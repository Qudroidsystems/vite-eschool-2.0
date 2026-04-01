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

        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return $number . 'th';
        }

        return $number . match ($lastDigit) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    /**
     * Calculate grade based on TOTAL score using WAEC/NECO standard for SENIOR classes.
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

                $validRecords = $subjectRecords->filter(function ($record) {
                    return $record->total != 0 && $record->total !== null;
                });

                $totalScores  = $validRecords->sum('total');
                $studentCount = $validRecords->count();
                $classAvg = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

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
                    $grade  = $this->calculateGrade($record->total);
                    $remark = $this->getRemark($grade);

                    $newPosition = $record->total == 0 ? '-' : (
                        isset($positionMap[$record->id]) ? $this->formatOrdinal($positionMap[$record->id]) : '-'
                    );

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
                    }
                }
            }

            return true;
        });

        if ($success) {
            Cache::put($cacheKey, true, now()->addHours(1));
        }

        return $success;
    }

    /**
     * Get complete student result data with images
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

            // Get assessments for the class category
            $assessments = collect();
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                if (class_exists(\App\Models\Assessment::class)) {
                    $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                        ->with('subAssessments')
                        ->orderBy('id')
                        ->get();
                }
            }

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

            // Load assessment scores for each subject
            foreach ($scores as $score) {
                $assessmentScores = BroadsheetAssessmentScore::where('broadsheet_id', $score->broadsheet_id)
                    ->with('assessment')
                    ->orderBy('assessment_id')
                    ->get();

                $score->assessment_scores = $assessmentScores;
                $score->assessments = $assessments;

                // Map to individual fields for display
                $assessmentArray = $assessmentScores->values();
                $score->ca1 = $assessmentArray[0]->score ?? 0;
                $score->ca2 = $assessmentArray[1]->score ?? 0;
                $score->ca3 = $assessmentArray[2]->score ?? 0;
                $score->exam = $assessmentArray[3]->score ?? 0;
            }

            // Force-recalculate grades from TOTAL
            foreach ($scores as $score) {
                $correctGrade = $this->calculateGrade($score->total);
                if ($score->grade !== $correctGrade) {
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

            $studentpp = Studentpersonalityprofile::where('studentpersonalityprofiles.studentid', $id)
                ->where('studentpersonalityprofiles.termid', $termid)
                ->where('studentpersonalityprofiles.sessionid', $sessionid)
                ->where('studentpersonalityprofiles.schoolclassid', $schoolclassid)
                ->get();

            $schoolsession = Schoolsession::where('id', $sessionid)->first();
            $schoolterm    = Schoolterm::where('id', $termid)->first();

            $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->count();

            $schoolInfo = SchoolInformation::first();
            if (!$schoolInfo) {
                $schoolInfo = new \stdClass();
                $schoolInfo->school_name = 'School Name Not Found';
                $schoolInfo->school_logo = null;
                $schoolInfo->school_motto = 'Motto Not Found';
                $schoolInfo->school_address = 'Address Not Found';
                $schoolInfo->school_phone = 'Phone Not Found';
                $schoolInfo->date_school_opened = null;
                $schoolInfo->date_next_term_begins = null;
            }

            $promotionStatusValue = null;
            $promotionStatus = PromotionStatus::where('student_id', $id)
                ->where('session_id', $sessionid)
                ->where('term_id', $termid)
                ->first();
            if ($promotionStatus) {
                $promotionStatusValue = $promotionStatus->status;
            }

            $compulsorySubjects = CompulsorySubjectClass::where('class_id', $schoolclassid)
                ->pluck('subject_id')
                ->toArray();

            if ($scores) {
                foreach ($scores as $score) {
                    $score->is_compulsory = in_array($score->subject_id, $compulsorySubjects);
                }
            }

            return [
                'students'             => $students,
                'studentpp'            => $studentpp,
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
                'promotionStatusValue' => $promotionStatusValue,
                'assessments'          => $assessments,
                'compulsorySubjects'   => $compulsorySubjects,
                'gpa_data'             => $gpaData,
            ];
        } catch (Exception $e) {
            Log::error('Error in getStudentResultData', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get column options for PDF generation - COMPLETE VERSION
     */
    public function getColumnOptions(Request $request)
    {
        Log::info('Getting column options', ['request' => $request->all()]);

        $schoolclassid = $request->input('schoolclassid');
        $sessionid = $request->input('sessionid');
        $termid = $request->input('termid');

        if (!$schoolclassid || !$sessionid || !$termid) {
            return response()->json([
                'success' => false,
                'message' => 'Missing parameters'
            ], 400);
        }

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $assessments = collect();

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            try {
                if (class_exists(\App\Models\Assessment::class)) {
                    $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                        ->with('subAssessments')
                        ->orderBy('id')
                        ->get();

                    Log::debug('Assessments for column options', [
                        'schoolclassid' => $schoolclassid,
                        'assessment_count' => $assessments->count(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error loading assessments for column options', ['error' => $e->getMessage()]);
            }
        }

        $columns = [
            'student_info' => [
                'sn' => ['label' => 'SN', 'default' => true],
                'admission_no' => ['label' => 'Admission No', 'default' => true],
                'name' => ['label' => 'Name', 'default' => true],
                'picture' => ['label' => 'Picture', 'default' => true],
                'gender' => ['label' => 'Gender', 'default' => false],
                'dob' => ['label' => 'Date of Birth', 'default' => false],
            ],
            'assessments' => [],
            'scores' => [
                'total' => ['label' => 'Total', 'default' => true],
                'bf' => ['label' => 'BF', 'default' => true],
                'cum' => ['label' => 'Cum', 'default' => true],
                'grade' => ['label' => 'Grade', 'default' => true],
                'position' => ['label' => 'Position', 'default' => true],
                'class_average' => ['label' => 'Class Avg', 'default' => true],
            ],
            'gpa_metrics' => [
                'num_subjects' => ['label' => 'Num Subjects', 'default' => true],
                'total_grade_points' => ['label' => 'Total GP', 'default' => true],
                'gpa' => ['label' => 'GPA', 'default' => true],
                'calculated_gpa' => ['label' => 'Calc GPA', 'default' => true],
                'gpa_grade' => ['label' => 'GPA Grade', 'default' => true],
                'cgpa' => ['label' => 'CGPA', 'default' => true],
            ],
            'other' => [
                'compulsory_flag' => ['label' => 'Compulsory', 'default' => false],
                'vetted_status' => ['label' => 'Vetted Status', 'default' => true],
            ]
        ];

        foreach ($assessments as $assessment) {
            $columns['assessments'][$assessment->id] = [
                'label' => $assessment->name . ' (' . $assessment->max_score . ')',
                'default' => true,
                'is_assessment' => true,
                'max_score' => $assessment->max_score,
                'has_sub_assessments' => $assessment->subAssessments->isNotEmpty()
            ];
        }

        return response()->json([
            'success' => true,
            'columns' => $columns,
            'assessments_count' => $assessments->count(),
            'is_senior' => $schoolclass && $schoolclass->classcategories->isNotEmpty() ?
                ($schoolclass->classcategories->first()->is_senior ?? false) : false,
        ]);
    }

    /**
     * Export class results as PDF - DISPLAY IN BROWSER
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

            // Fix image paths for all students
            $this->fixImagePathsForStudents($studentIds, $schoolclassid, $sessionid, $termid);

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

            $pdfContent = $pdf->output();

            // Return inline response to display in browser
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));

        } catch (Exception $e) {
            Log::error('Error in exportClassResultsPdf', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fix image paths for students - convert to base64 for PDF
     */
    private function fixImagePathsForStudents($studentIds, $schoolclassid, $sessionid, $termid)
    {
        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);
            if ($student && $student->picture) {
                $imagePath = public_path('storage/' . $student->picture);
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                    $base64 = base64_encode($imageData);
                    $mime = mime_content_type($imagePath);
                    $student->picture_base64 = "data:{$mime};base64,{$base64}";
                }
            }
        }
    }

    /**
     * Export single student result as PDF - DISPLAY IN BROWSER
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

            // Fix student image
            $student = $data['students']->first();
            if ($student && $student->picture) {
                $imagePath = public_path('storage/' . $student->picture);
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                    $base64 = base64_encode($imageData);
                    $mime = mime_content_type($imagePath);
                    $data['student_image_base64'] = "data:{$mime};base64,{$base64}";
                }
            }

            // Fix school logo
            if ($data['schoolInfo'] && !empty($data['schoolInfo']->school_logo)) {
                $logoPath = public_path('storage/' . $data['schoolInfo']->school_logo);
                if (file_exists($logoPath)) {
                    $imageData = file_get_contents($logoPath);
                    $base64 = base64_encode($imageData);
                    $mime = mime_content_type($logoPath);
                    $data['school_logo_base64'] = "data:{$mime};base64,{$base64}";
                }
            }

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

            $pdfContent = $pdf->output();

            // Return inline response to display in browser
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));

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
     * Calculate grade preview for AJAX requests
     */
    public function calculateGradePreview(Request $request)
    {
        $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'total' => 'required|numeric|min:0|max:100',
        ]);

        $grade = $this->calculateGrade($request->total);

        return response()->json(['grade' => $grade]);
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
