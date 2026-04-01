<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Schoolarm;
use Illuminate\View\View;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use App\Models\Schoolsession;
use Illuminate\Http\Response;
use App\Models\PromotionStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\CompulsorySubjectClass;
use App\Models\BroadsheetAssessmentScore;
use App\Models\Studentpersonalityprofile;
use Illuminate\Pagination\LengthAwarePaginator;

class ViewStudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-report', ['only' => [
            'index', 'show', 'registeredClasses', 'classBroadsheet',
            'studentresult', 'studentmockresult', 'exportStudentResultPdf', 'exportClassResultsPdf'
        ]]);
        $this->middleware('permission:Create student-report', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update student-report', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete student-report', ['only' => ['destroy']]);

        Log::channel('pdf')->info('ViewStudentReportController initialized', ['timestamp' => now()]);
    }

    /**
     * Format number with ordinal suffix (st, nd, rd, th)
     */
    protected function formatOrdinal($number)
    {
        if (!is_numeric($number) || $number <= 0) {
            return '-';
        }

        $lastDigit     = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return $number . 'th';
        }

        return $number . match ($lastDigit) {
            1       => 'st',
            2       => 'nd',
            3       => 'rd',
            default => 'th',
        };
    }

    /**
     * Calculate grade based on TOTAL score using WAEC/NECO standard for SENIOR classes.
     *
     * FIX: Removed upper-bound checks (e.g. $score <= 74) that left decimal scores
     * like 74.5 falling through every condition and landing on F9.
     * Now uses cascading >= only, which correctly handles any decimal value.
     */
    protected function calculateGrade($score)
    {
        Log::debug('Calculating grade', ['score' => $score]);

        if ($score === null || $score <= 0) {
            return 'F9';
        }

        if ($score >= 75) {
            return 'A1';
        } elseif ($score >= 70) {
            return 'B2';
        } elseif ($score >= 65) {
            return 'B3';
        } elseif ($score >= 60) {
            return 'C4';
        } elseif ($score >= 55) {
            return 'C5';
        } elseif ($score >= 50) {
            return 'C6';
        } elseif ($score >= 45) {
            return 'D7';
        } elseif ($score >= 40) {
            return 'E8';
        } else {
            return 'F9';
        }
    }

    /**
     * Get grade point based on score using WAEC/NECO standard.
     *
     * FIX: Same gap fix as calculateGrade() — cascading >= only.
     */
    protected function getGradePoint($score)
    {
        Log::debug('Calculating grade point', ['score' => $score]);

        if ($score === null || $score <= 0) {
            return 0.0;
        }

        if ($score >= 75) {
            return 5.0; // A1
        } elseif ($score >= 70) {
            return 4.5; // B2
        } elseif ($score >= 65) {
            return 4.0; // B3
        } elseif ($score >= 60) {
            return 3.5; // C4
        } elseif ($score >= 55) {
            return 3.0; // C5
        } elseif ($score >= 50) {
            return 2.5; // C6
        } elseif ($score >= 45) {
            return 2.0; // D7
        } elseif ($score >= 40) {
            return 1.0; // E8
        } else {
            return 0.0; // F9
        }
    }

    /**
     * Get remark based on grade
     */
    protected function getRemark($grade)
    {
        Log::debug('Getting remark for grade', ['grade' => $grade]);

        $remarks = [
            'A1' => 'Excellent',
            'B2' => 'Very Good',
            'B3' => 'Good',
            'C4' => 'Credit',
            'C5' => 'Credit',
            'C6' => 'Credit',
            'D7' => 'Pass',
            'E8' => 'Pass',
            'F9' => 'Fail',
        ];

        return $remarks[$grade] ?? 'Unknown';
    }

    /**
     * Get GPA letter grade based on GPA value.
     *
     * FIX: Same gap fix — cascading >= only.
     */
    protected function getGpaGrade($gpa)
    {
        if ($gpa >= 4.5) {
            return 'A1';
        } elseif ($gpa >= 4.0) {
            return 'B2';
        } elseif ($gpa >= 3.5) {
            return 'B3';
        } elseif ($gpa >= 3.0) {
            return 'C4';
        } elseif ($gpa >= 2.5) {
            return 'C5';
        } elseif ($gpa >= 2.0) {
            return 'C6';
        } elseif ($gpa >= 1.5) {
            return 'D7';
        } elseif ($gpa >= 1.0) {
            return 'E8';
        } else {
            return 'F9';
        }
    }

    /**
     * Compute overall GPA and CGPA for a student.
     * GPA  = average of grade points for current term using TOTAL score.
     * CGPA = average of GPAs for all completed terms in current session.
     */
    protected function computeOverallGPAAndCGPAForStudent($studentId, $schoolclass, $termId, $sessionId)
    {
        Log::info('Computing GPA/CGPA for student', [
            'student_id' => $studentId,
            'term_id'    => $termId,
            'session_id' => $sessionId,
        ]);

        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);

        Log::debug('Current term broadsheets', [
            'student_id'   => $studentId,
            'count'        => $currentTermBroadsheets->count(),
            'total_scores' => $currentTermBroadsheets->pluck('total')->toArray(),
        ]);

        $termGradePoints = $currentTermBroadsheets->map(function ($b) {
            return $this->getGradePoint($b->total);
        });

        $gpa               = $termGradePoints->avg() ?? 0.0;
        $num_subjects      = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();

        // CGPA — average of all completed terms in the current session
        $termGPAs = [];

        for ($t = 1; $t <= $termId; $t++) {
            $termBroadsheets = Broadsheets::where('term_id', $t)
                ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                    $q->where('student_id', $studentId)->where('session_id', $sessionId);
                })
                ->get(['total']);

            if ($termBroadsheets->isNotEmpty()) {
                $termGradePointsPast = $termBroadsheets->map(function ($b) {
                    return $this->getGradePoint($b->total);
                });

                $termGPA = $termGradePointsPast->avg() ?? 0.0;
                if ($termGPA > 0) {
                    $termGPAs[] = $termGPA;
                }
            }
        }

        $cgpa     = !empty($termGPAs) ? collect($termGPAs)->avg() : 0.0;
        $gpaGrade = $this->getGpaGrade($gpa);

        return [
            'gpa'               => round($gpa, 2),
            'cgpa'              => round($cgpa, 2),
            'gpa_grade'         => $gpaGrade,
            'num_subjects'      => $num_subjects,
            'total_grade_points' => round($total_grade_points, 1),
            'calculated_gpa'    => $num_subjects > 0 ? round($total_grade_points / $num_subjects, 2) : 0.0,
        ];
    }

    /**
     * Calculate class positions, averages, and grades for all subjects.
     *
     * FIX 1: Class average now uses TOTAL (was previously inconsistent between
     *         this method and updateClassMetrics in MyScoreSheetController).
     * FIX 2: Positions ranked by TOTAL consistently.
     * FIX 3: Grade boundaries use cascading >= to handle decimal scores.
     */
    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";

        Log::info('Starting class metrics calculation', [
            'schoolclassid' => $schoolclassid,
            'sessionid'     => $sessionid,
            'termid'        => $termid,
        ]);

        Cache::forget($cacheKey);

        $schoolclass = Schoolclass::with('classcategories')->where('id', $schoolclassid)->first(['id', 'schoolclass']);
        if (!$schoolclass) {
            Log::error('Schoolclass not found');
            return false;
        }

        $className = $schoolclass->schoolclass;

        $classIds = Schoolclass::where('schoolclass', $className)->pluck('id')->toArray();
        if (empty($classIds)) {
            Log::error('No schoolclass IDs found');
            return false;
        }

        $students = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($students)) {
            Log::error('No students found for class');
            return false;
        }

        $success = DB::transaction(function () use ($schoolclassid, $sessionid, $termid, $classIds, $students) {

            $broadsheets = Broadsheets::whereIn('broadsheet_records.student_id', $students)
                ->where('broadsheets.term_id', $termid)
                ->where('broadsheet_records.session_id', $sessionid)
                ->whereIn('broadsheet_records.schoolclass_id', $classIds)
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->select([
                    'broadsheets.id',
                    'broadsheet_records.student_id',
                    'broadsheet_records.subject_id',
                    'subject.subject as subject_name',
                    'broadsheets.total',
                    'broadsheets.bf',
                    'broadsheets.cum',
                    'broadsheets.subject_position_class',
                    'broadsheets.avg',
                    'broadsheets.grade',
                    'broadsheets.remark',
                ])
                ->get();

            if ($broadsheets->isEmpty()) {
                Log::error('No broadsheet records found');
                return false;
            }

            $subjectGroups = $broadsheets->groupBy('subject_id');

            foreach ($subjectGroups as $subjectId => $subjectRecords) {
                $subjectName = $subjectRecords->first()->subject_name;

                // FIX: Average calculated from TOTAL scores (was using cum in updateClassMetrics)
                $validRecords = $subjectRecords->filter(function ($record) {
                    return $record->total != 0 && $record->total !== null;
                });

                $totalScores  = $validRecords->sum('total');
                $studentCount = $validRecords->count();
                // FIX: Use round() not floor/truncate
                $classAvg = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

                // FIX: Positions based on TOTAL scores (consistent with report display)
                $sortedRecords = $validRecords->sortByDesc('total')->values();

                $rank         = 0;
                $lastTotal    = null;
                $lastPosition = 0;
                $positionMap  = [];

                foreach ($sortedRecords as $record) {
                    $rank++;
                    if ($lastTotal !== null && $record->total == $lastTotal) {
                        $positionMap[$record->id] = $lastPosition;
                    } else {
                        $lastPosition             = $rank;
                        $lastTotal                = $record->total;
                        $positionMap[$record->id] = $lastPosition;
                    }
                }

                foreach ($subjectRecords as $record) {
                    // Use TOTAL for grade — fixed boundary gaps
                    $grade  = $this->calculateGrade($record->total);
                    $remark = $this->getRemark($grade);

                    $newPosition = $record->total == 0 ? '-' : (
                        isset($positionMap[$record->id]) ? $this->formatOrdinal($positionMap[$record->id]) : '-'
                    );

                    Log::debug('Grade calculation', [
                        'subject'          => $subjectName,
                        'total_score'      => $record->total,
                        'calculated_grade' => $grade,
                        'old_grade'        => $record->grade,
                        'position'         => $newPosition,
                    ]);

                    if (
                        $record->avg != $classAvg ||
                        $record->subject_position_class != $newPosition ||
                        $record->grade != $grade ||
                        $record->remark != $remark
                    ) {
                        Broadsheets::where('id', $record->id)->update([
                            'avg'                   => $classAvg,
                            'subject_position_class' => $newPosition,
                            'grade'                 => $grade,
                            'remark'                => $remark,
                        ]);

                        Log::info('Broadsheet updated', [
                            'subject'      => $subjectName,
                            'total_score'  => $record->total,
                            'new_grade'    => $grade,
                            'new_position' => $newPosition,
                        ]);
                    }
                }
            }

            return true;
        });

        if ($success) {
            Cache::put($cacheKey, true, now()->addHours(1));
            Log::info('Class metrics calculation completed successfully');
        }

        return $success;
    }

    /**
     * Get complete student result data
     */
    private function getStudentResultData($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            Log::info('getStudentResultData called', [
                'student_id'   => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid'    => $sessionid,
                'termid'       => $termid,
            ]);

            $students = Student::where('studentRegistration.id', $id)
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->select([
                    'studentRegistration.id as id',
                    'studentRegistration.admissionNo as admissionNo',
                    'studentRegistration.firstname as fname',
                    'studentRegistration.lastname as lastname',
                    'studentRegistration.othername as othername',
                    'studentRegistration.dateofbirth as dateofbirth',
                    'studentRegistration.gender as gender',
                    'studentpicture.picture as picture',
                ])
                ->get();

            $schoolclass = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);

            $scores = Broadsheets::where('broadsheet_records.student_id', $id)
                ->where('broadsheets.term_id', $termid)
                ->where('broadsheet_records.session_id', $sessionid)
                ->where('broadsheet_records.schoolclass_id', $schoolclassid)
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->orderBy('subject.subject')
                ->select([
                    'subject.id as subject_id',
                    'subject.subject as subject_name',
                    'subject.subject_code',
                    'broadsheets.total',
                    'broadsheets.bf',
                    'broadsheets.cum',
                    'broadsheets.grade',
                    'broadsheets.remark',
                    'broadsheets.subject_position_class as position',
                    'broadsheets.avg as class_average',
                    'broadsheets.id as broadsheet_id',
                ])->get();

            Log::info('Scores fetched', [
                'student_id'   => $id,
                'scores_count' => $scores->count(),
                'scores_data'  => $scores->map(function ($s) {
                    return [
                        'subject'       => $s->subject_name,
                        'total'         => $s->total,
                        'current_grade' => $s->grade,
                    ];
                })->toArray(),
            ]);

            // Force-recalculate grades from TOTAL using fixed boundary logic
            foreach ($scores as $score) {
                $correctGrade = $this->calculateGrade($score->total);

                if ($score->grade !== $correctGrade) {
                    Log::warning('Grade mismatch — fixing', [
                        'subject'     => $score->subject_name,
                        'total_score' => $score->total,
                        'old_grade'   => $score->grade,
                        'new_grade'   => $correctGrade,
                    ]);

                    $score->grade  = $correctGrade;
                    $score->remark = $this->getRemark($correctGrade);

                    Broadsheets::where('id', $score->broadsheet_id)->update([
                        'grade'  => $correctGrade,
                        'remark' => $this->getRemark($correctGrade),
                    ]);
                }
            }

            $gpaData = $this->computeOverallGPAAndCGPAForStudent(
                $id,
                $schoolclass,
                $termid,
                $sessionid
            );

            $schoolsession = Schoolsession::where('id', $sessionid)->first();
            $schoolterm    = Schoolterm::where('id', $termid)->first();

            $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->count();

            $schoolInfo = SchoolInformation::first();
            if (!$schoolInfo) {
                $schoolInfo             = new \stdClass();
                $schoolInfo->school_name = 'School Name Not Found';
            }

            return [
                'students'             => $students,
                'scores'               => $scores,
                'studentid'            => $id,
                'schoolclassid'        => $schoolclassid,
                'sessionid'            => $sessionid,
                'termid'               => $termid,
                'schoolclass'          => $schoolclass,
                'schoolterm'           => $schoolterm,
                'schoolsession'        => $schoolsession,
                'numberOfStudents'     => $numberOfStudents,
                'schoolInfo'           => $schoolInfo,
                'gpa_data'             => $gpaData,
                'assessments'          => collect(),
                'studentpp'            => collect(),
                'compulsorySubjects'   => [],
                'promotionStatusValue' => null,
            ];
        } catch (Exception $e) {
            Log::error('Error in getStudentResultData', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Export class results as PDF
     */
    public function exportClassResultsPdf(Request $request)
    {
        try {
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            $schoolclassid   = $request->input('schoolclassid');
            $sessionid       = $request->input('sessionid');
            $termid          = $request->input('termid', 3);
            $studentIds      = $request->input('studentIds', []);
            $selectedColumns = $request->input('selectedColumns', []);

            if (!$schoolclassid || !$sessionid || !$termid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters',
                ], 400);
            }

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to calculate class metrics',
                ], 500);
            }

            $allStudentData = [];

            foreach ($studentIds as $studentId) {
                $studentData = $this->getStudentResultData(
                    $studentId,
                    $schoolclassid,
                    $sessionid,
                    $termid
                );

                if (!empty($studentData) && !empty($studentData['scores'])) {
                    $studentData['selected_columns'] = $selectedColumns;
                    $allStudentData[]                = $studentData;
                }
            }

            if (empty($allStudentData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid student data found',
                ], 500);
            }

            $schoolclass   = Schoolclass::find($schoolclassid);
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $term          = $this->getTermName($termid);
            $className     = $schoolclass
                ? ($schoolclass->schoolclass . ($schoolclass->arm ? $schoolclass->arm : ''))
                : 'Class';

            $filename = 'Class_Results_'
                . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_'
                . preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_'
                . $term . '.pdf';

            $viewData = [
                'allStudentData' => $allStudentData,
                'metadata'       => [
                    'class_name'      => $className,
                    'session'         => $schoolsession,
                    'term'            => $term,
                    'generation_date' => now()->format('Y-m-d H:i:s'),
                    'student_count'   => count($allStudentData),
                    'selected_columns' => $selectedColumns,
                ],
            ];

            $pdf = Pdf::loadView('studentreports.class_results_pdf', $viewData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi'                    => 96,
                    'defaultFont'            => 'DejaVu Sans',
                    'isRemoteEnabled'        => true,
                    'isHtml5ParserEnabled'   => true,
                    'isFontSubsettingEnabled' => true,
                ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Error in exportClassResultsPdf', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export single student result as PDF
     */
    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return back()->with('error', 'Failed to calculate class metrics');
            }

            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

            if (empty($data) || empty($data['students']) || $data['students']->isEmpty()) {
                return back()->with('error', 'No student data found');
            }

            $student     = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename    = 'Terminal_Report_' . $studentName
                . '_' . $data['schoolsession']->session
                . '_Term_' . $data['termid'] . '.pdf';

            $pdf = Pdf::loadView('studentreports.studentresult_pdf', ['data' => $data])
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi'                    => 150,
                    'defaultFont'            => 'DejaVu Sans',
                    'isRemoteEnabled'        => true,
                    'isHtml5ParserEnabled'   => true,
                    'isFontSubsettingEnabled' => true,
                ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Error in exportStudentResultPdf', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get term name from term ID
     */
    private function getTermName($termid)
    {
        $terms = [
            1 => 'First Term',
            2 => 'Second Term',
            3 => 'Third Term',
        ];

        return $terms[$termid] ?? 'Unknown Term';
    }

    /**
     * Get column options for PDF generation
     */
    public function getColumnOptions(Request $request)
    {
        return response()->json([
            'success' => true,
            'columns' => [
                'student_info' => [
                    'sn'          => ['label' => 'SN', 'default' => true],
                    'admission_no' => ['label' => 'Admission No', 'default' => true],
                    'name'        => ['label' => 'Name', 'default' => true],
                    'picture'     => ['label' => 'Picture', 'default' => true],
                ],
                'scores' => [
                    'total'         => ['label' => 'Total', 'default' => true],
                    'grade'         => ['label' => 'Grade', 'default' => true],
                    'position'      => ['label' => 'Position', 'default' => true],
                    'class_average' => ['label' => 'Class Avg', 'default' => true],
                ],
                'gpa_metrics' => [
                    'gpa'       => ['label' => 'GPA', 'default' => true],
                    'cgpa'      => ['label' => 'CGPA', 'default' => true],
                    'gpa_grade' => ['label' => 'GPA Grade', 'default' => true],
                ],
            ],
        ]);
    }

    /**
     * Display student result view
     */
    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Personality Profile";

        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
        if (!$metricsCalculated) {
            return back()->with('error', 'Failed to calculate class metrics');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

        return view('studentreports.studentresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Display student mock result
     */
    public function studentmockresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Mock Result";

        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
        if (!$metricsCalculated) {
            return back()->with('error', 'Failed to calculate class metrics');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

        return view('studentreports.studentmockresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Display class broadsheet
     */
    public function classBroadsheet($schoolclassid, $sessionid, $termid): View
    {
        $class     = Schoolclass::findOrFail($schoolclassid);
        $session   = Schoolsession::findOrFail($sessionid);
        $pagetitle = "Broadsheet for {$class->schoolclass} - {$session->session} - Term {$termid}";

        return view('studentreports.broadsheet', compact('class', 'session', 'termid', 'pagetitle'));
    }

    /**
     * Get registered classes
     */
    public function registeredClasses(Request $request)
    {
        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id');

        if (!$classId || !$sessionId) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $classes = Studentclass::query()
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolclass.id', $classId)
            ->where('schoolsession.id', $sessionId)
            ->groupBy('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm')
            ->selectRaw('
                schoolclass.schoolclass as class_name,
                schoolarm.arm as name_arm,
                COUNT(DISTINCT studentclass.studentId) as student_count
            ')
            ->get();

        return response()->json(['success' => true, 'data' => $classes]);
    }

    /**
     * Display student reports index
     */
    public function index(Request $request): View|JsonResponse
    {
        $pagetitle  = "Student Terminal Report Management";
        $current    = "Current";
        $allstudents = new LengthAwarePaginator([], 0, 10);

        if (
            $request->filled('schoolclassid') && $request->filled('sessionid') &&
            $request->input('schoolclassid') !== 'ALL' && $request->input('sessionid') !== 'ALL'
        ) {
            $query = Studentclass::query()
                ->where('schoolclassid', $request->input('schoolclassid'))
                ->where('sessionid', $request->input('sessionid'))
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->where('schoolsession.status', '=', $current);

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionNo', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.lastname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.othername', 'like', "%{$search}%");
                });
            }

            $allstudents = $query->select([
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentRegistration.id as stid',
                'studentpicture.picture as picture',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session',
            ])->latest('studentclass.created_at')->paginate(100);
        }

        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses  = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        if ($request->ajax()) {
            return response()->json([
                'tableBody'    => view('studentreports.partials.student_rows', compact('allstudents'))->render(),
                'pagination'   => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('studentreports.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }
}
