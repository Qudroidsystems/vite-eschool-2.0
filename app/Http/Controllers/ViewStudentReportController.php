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
     * Calculate grade based on total score using WAEC/NECO standard.
     *
     * FIX: Removed upper-bound checks (e.g. $score <= 74) that caused decimal
     * scores like 74.5 to fall through every condition and return F9.
     * Now uses cascading >= only, which correctly handles any decimal value.
     */
    protected function calculateGrade($score)
    {
        Log::debug('Calculating grade', ['score' => $score]);

        if ($score === null || $score == 0) {
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

        if ($score === null || $score == 0) {
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

        $remark = $remarks[$grade] ?? 'Unknown';
        Log::debug('Remark retrieved', ['grade' => $grade, 'remark' => $remark]);

        return $remark;
    }

    /**
     * Get GPA letter grade based on GPA value.
     *
     * FIX: Cascading >= only — no upper-bound gaps.
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
     * GPA  = Average of grade points for current term using TOTAL score.
     * CGPA = Average of GPAs for all completed terms in current session.
     */
    protected function computeOverallGPAAndCGPAForStudent($studentId, $schoolclass, $termId, $sessionId)
    {
        Log::info('Computing GPA/CGPA for student', [
            'student_id' => $studentId,
            'term_id'    => $termId,
            'session_id' => $sessionId,
            'class_name' => $schoolclass->schoolclass ?? 'Unknown',
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

        $termGradePoints    = $currentTermBroadsheets->map(fn ($b) => $this->getGradePoint($b->total));
        $gpa                = $termGradePoints->avg() ?? 0.0;
        $num_subjects       = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();

        Log::debug('GPA calculations', [
            'gpa'                => $gpa,
            'num_subjects'       => $num_subjects,
            'total_grade_points' => $total_grade_points,
            'grade_points'       => $termGradePoints->toArray(),
        ]);

        // CGPA — average of all completed term GPAs in the current session
        $termGPAs = [];

        for ($t = 1; $t <= $termId; $t++) {
            $termBroadsheets = Broadsheets::where('term_id', $t)
                ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                    $q->where('student_id', $studentId)->where('session_id', $sessionId);
                })
                ->get(['total']);

            if ($termBroadsheets->isNotEmpty()) {
                $termGradePointsPast = $termBroadsheets->map(fn ($b) => $this->getGradePoint($b->total));
                $termGPA             = $termGradePointsPast->avg() ?? 0.0;

                if ($termGPA > 0) {
                    $termGPAs[] = $termGPA;
                }

                Log::debug('Term GPA calculation for CGPA', [
                    'term'             => $t,
                    'term_gpa'         => $termGPA,
                    'broadsheet_count' => $termBroadsheets->count(),
                ]);
            }
        }

        $cgpa     = !empty($termGPAs) ? collect($termGPAs)->avg() : 0.0;
        $gpaGrade = $this->getGpaGrade($gpa);

        $result = [
            'gpa'                => round($gpa, 2),
            'cgpa'               => round($cgpa, 2),
            'gpa_grade'          => $gpaGrade,
            'num_subjects'       => $num_subjects,
            'total_grade_points' => round($total_grade_points, 1),
            'calculated_gpa'     => $num_subjects > 0 ? round($total_grade_points / $num_subjects, 2) : 0.0,
        ];

        Log::info('GPA/CGPA computation completed', $result);

        return $result;
    }

    /**
     * Calculate class positions, averages, and grades for all subjects.
     *
     * FIX 1: Class average now uses TOTAL (consistent with report display).
     * FIX 2: Positions ranked and tied by TOTAL.
     * FIX 3: Grade boundaries use cascading >= to handle decimal scores.
     */
    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";

        Log::info('Starting class metrics calculation', [
            'schoolclassid' => $schoolclassid,
            'sessionid'     => $sessionid,
            'termid'        => $termid,
            'cache_key'     => $cacheKey,
        ]);

        Cache::forget($cacheKey);

        $schoolclass = Schoolclass::with('classcategories')->where('id', $schoolclassid)->first(['id', 'schoolclass']);
        if (!$schoolclass) {
            Log::error('Schoolclass not found', compact('schoolclassid', 'sessionid', 'termid'));
            return false;
        }

        $className = $schoolclass->schoolclass;
        Log::debug('School class found', ['class_name' => $className, 'class_id' => $schoolclassid]);

        $classIds = Schoolclass::where('schoolclass', $className)->pluck('id')->toArray();
        if (empty($classIds)) {
            Log::error('No schoolclass IDs found for class name', compact('className', 'schoolclassid', 'sessionid', 'termid'));
            return false;
        }

        $students = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($students)) {
            Log::error('No students found for class', compact('className', 'classIds', 'sessionid', 'termid'));
            return false;
        }

        $subjectGroups = null;

        $success = DB::transaction(function () use ($schoolclassid, $sessionid, $termid, $className, $classIds, $students, &$subjectGroups) {

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
                    'studentRegistration.admissionNo as admission_no',
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
                Log::error('No broadsheet records found for class', compact('className', 'classIds', 'sessionid', 'termid', 'students'));
                return false;
            }

            $subjectGroups = $broadsheets->groupBy('subject_id');
            Log::info('Grouped broadsheets by subject', ['subject_count' => $subjectGroups->count()]);

            foreach ($subjectGroups as $subjectId => $subjectRecords) {
                $subjectName = $subjectRecords->first()->subject_name;

                // FIX: Average from TOTAL (not cum)
                $validRecords = $subjectRecords->filter(fn ($r) => $r->total != 0 && $r->total !== null);
                $totalScores  = $validRecords->sum('total');
                $studentCount = $validRecords->count();
                $classAvg     = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

                // FIX: Positions based on TOTAL
                $sortedRecords = $validRecords->sortByDesc('total')->values();
                $rank          = 0;
                $lastTotal     = null;
                $lastPosition  = 0;
                $positionMap   = [];

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

                $updatesCount = 0;

                foreach ($subjectRecords as $record) {
                    // FIX: Grade uses cascading >= (no upper-bound gaps)
                    $grade  = $record->total == 0 ? '-' : $this->calculateGrade($record->total);
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
                        $updateResult = Broadsheets::where('id', $record->id)->update([
                            'avg'                    => $classAvg,
                            'subject_position_class' => $newPosition,
                            'grade'                  => $grade,
                            'remark'                 => $remark,
                        ]);

                        if ($updateResult) {
                            $updatesCount++;
                        }

                        Log::debug('Broadsheet updated', [
                            'broadsheet_id' => $record->id,
                            'subject_name'  => $subjectName,
                            'old_grade'     => $record->grade,
                            'new_grade'     => $grade,
                            'old_position'  => $record->subject_position_class,
                            'new_position'  => $newPosition,
                            'new_avg'       => $classAvg,
                        ]);
                    }
                }

                Log::info('Subject processing completed', [
                    'subject_name'    => $subjectName,
                    'updates_applied' => $updatesCount,
                    'total_records'   => $subjectRecords->count(),
                ]);
            }

            return true;
        });

        if ($success) {
            Cache::put($cacheKey, true, now()->addHours(1));
            Log::info('Class metrics calculation completed successfully', [
                'class_name'     => $className,
                'schoolclassids' => $classIds,
                'sessionid'      => $sessionid,
                'termid'         => $termid,
                'total_subjects' => $subjectGroups ? $subjectGroups->count() : 0,
                'total_students' => count($students),
            ]);
        } else {
            Log::error('Failed to calculate class metrics in transaction', compact('className', 'schoolclassid', 'sessionid', 'termid'));
        }

        return $success;
    }

    /**
     * Get complete student result data
     */
    private function getStudentResultData($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            Log::channel('pdf')->info('========== START getStudentResultData ==========', [
                'student_id'    => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid'     => $sessionid,
                'termid'        => $termid,
                'timestamp'     => now()->toDateTimeString(),
                'server_ip'     => request()->server('SERVER_ADDR') ?? 'unknown',
                'client_ip'     => request()->ip(),
            ]);

            if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
                Log::error('Invalid parameters in getStudentResultData', compact('id', 'schoolclassid', 'sessionid', 'termid'));
                return [];
            }

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
                    'studentRegistration.home_address2 as present_address',
                    'studentRegistration.home_address2 as permanent_address',
                    'studentRegistration.updated_at as updated_at',
                    'studentpicture.picture as picture',
                ])
                ->orderBy('studentRegistration.lastname', 'asc')
                ->get();

            if ($students->isEmpty()) {
                Log::error('No active student found for ID', compact('id', 'schoolclassid', 'sessionid', 'termid'));
                $students = collect([]);
            }

            $schoolclass = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);
            $assessments = collect();

            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');

                try {
                    if (class_exists(\App\Models\Assessment::class)) {
                        $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                            ->with('subAssessments')
                            ->orderBy('id')
                            ->get();

                        Log::debug('Assessments loaded', [
                            'assessment_count' => $assessments->count(),
                            'assessment_names' => $assessments->pluck('name')->toArray(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error loading assessments', ['error' => $e->getMessage(), 'category_ids' => $categoryIds->toArray()]);
                }
            } else {
                Log::warning('No class categories found or schoolclass not found', [
                    'schoolclassid'  => $schoolclassid,
                    'class_exists'   => !is_null($schoolclass),
                    'has_categories' => $schoolclass ? $schoolclass->classcategories->isNotEmpty() : false,
                ]);
            }

            $scores      = null;
            $attempts    = 0;
            $maxAttempts = 3;
            $retryDelay  = 500;

            while ($attempts < $maxAttempts) {
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
                        'broadsheets.vettedstatus',
                    ])->get();

                Log::debug('Broadsheet query attempt', [
                    'attempt'       => $attempts + 1,
                    'scores_found'  => $scores->count(),
                    'subject_names' => $scores->pluck('subject_name')->toArray(),
                ]);

                foreach ($scores as $score) {
                    try {
                        if (class_exists(\App\Models\BroadsheetAssessmentScore::class)) {
                            $assessmentScores = \App\Models\BroadsheetAssessmentScore::where('broadsheet_id', $score->broadsheet_id)
                                ->with('assessment')
                                ->orderBy('assessment_id')
                                ->get();

                            $assessmentArray = $assessmentScores->values();

                            $score->ca1  = 0;
                            $score->ca2  = 0;
                            $score->ca3  = 0;
                            $score->exam = 0;

                            if ($assessmentArray->count() > 0) $score->ca1  = $assessmentArray->get(0)->score ?? 0;
                            if ($assessmentArray->count() > 1) $score->ca2  = $assessmentArray->get(1)->score ?? 0;
                            if ($assessmentArray->count() > 2) $score->ca3  = $assessmentArray->get(2)->score ?? 0;
                            if ($assessmentArray->count() > 3) $score->exam = $assessmentArray->get(3)->score ?? 0;

                            $score->assessment_scores = $assessmentScores;
                            $score->assessments       = $assessments;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error loading assessment scores', [
                            'error'         => $e->getMessage(),
                            'broadsheet_id' => $score->broadsheet_id,
                            'subject_name'  => $score->subject_name,
                        ]);
                    }
                }

                $hasValidGrades = $scores->every(fn ($s) => $s->grade !== '-' && $s->grade !== null);

                if ($hasValidGrades || $scores->isEmpty()) {
                    break;
                }

                Log::warning('Retrying fetch due to incomplete grades', [
                    'student_id' => $id,
                    'attempt'    => $attempts + 1,
                ]);

                usleep($retryDelay * 1000);
                $attempts++;
            }

            Log::info('Fetched broadsheet data', [
                'student_id'     => $id,
                'scores_count'   => $scores ? $scores->count() : 0,
                'total_attempts' => $attempts + 1,
            ]);

            $gpaData = [];
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                try {
                    $gpaData = $this->computeOverallGPAAndCGPAForStudent($id, $schoolclass, $termid, $sessionid);
                    Log::info('GPA/CGPA calculation completed', array_merge(['student_id' => $id], $gpaData));
                } catch (\Exception $e) {
                    Log::error('Error calculating GPA/CGPA', ['student_id' => $id, 'error' => $e->getMessage()]);
                    $gpaData = [
                        'gpa'                => 0.0,
                        'cgpa'               => 0.0,
                        'gpa_grade'          => 'F9',
                        'num_subjects'       => 0,
                        'total_grade_points' => 0,
                        'calculated_gpa'     => 0.0,
                    ];
                }
            }

            try {
                $studentpp = Studentpersonalityprofile::where('studentpersonalityprofiles.studentid', $id)
                    ->where('studentpersonalityprofiles.termid', $termid)
                    ->where('studentpersonalityprofiles.sessionid', $sessionid)
                    ->where('studentpersonalityprofiles.schoolclassid', $schoolclassid)
                    ->join('schoolsession', 'schoolsession.id', '=', 'studentpersonalityprofiles.sessionid')
                    ->join('schoolterm', 'schoolterm.id', '=', 'studentpersonalityprofiles.termid')
                    ->join('schoolclass', 'schoolclass.id', '=', 'studentpersonalityprofiles.schoolclassid')
                    ->select(
                        'studentpersonalityprofiles.*',
                        'schoolsession.session as session',
                        'schoolterm.term as term',
                        'schoolclass.schoolclass as schoolclass'
                    )
                    ->get();

                if ($studentpp->isEmpty()) {
                    $studentpp = collect();
                }
            } catch (\Exception $e) {
                Log::error('Error fetching student personality profile', ['student_id' => $id, 'error' => $e->getMessage()]);
                $studentpp = collect();
            }

            $schoolsession    = Schoolsession::where('id', $sessionid)->first();
            $schoolterm       = Schoolterm::where('id', $termid)->first();
            $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)->where('sessionid', $sessionid)->count();

            $schoolInfo = SchoolInformation::first();
            if (!$schoolInfo) {
                $schoolInfo                        = new \stdClass();
                $schoolInfo->id                    = 0;
                $schoolInfo->school_name           = 'School Name Not Found';
                $schoolInfo->school_logo           = null;
                $schoolInfo->school_motto          = 'Motto Not Found';
                $schoolInfo->school_address        = 'Address Not Found';
                $schoolInfo->school_phone          = 'Phone Not Found';
                $schoolInfo->date_school_opened    = null;
                $schoolInfo->date_next_term_begins = null;
            }

            $promotionStatusValue = null;
            try {
                $promotionStatus = PromotionStatus::where('student_id', $id)
                    ->where('session_id', $sessionid)
                    ->where('term_id', $termid)
                    ->first();

                if ($promotionStatus) {
                    $promotionStatusValue = $promotionStatus->status;
                }
            } catch (\Exception $e) {
                Log::error('Error fetching promotion status', ['student_id' => $id, 'error' => $e->getMessage()]);
            }

            $compulsorySubjects = [];
            try {
                $compulsorySubjects = CompulsorySubjectClass::where('class_id', $schoolclassid)
                    ->pluck('subject_id')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Error fetching compulsory subjects', ['class_id' => $schoolclassid, 'error' => $e->getMessage()]);
            }

            if ($scores) {
                foreach ($scores as $score) {
                    $score->is_compulsory = in_array($score->subject_id, $compulsorySubjects);
                }
            }

            $result = [
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

            Log::channel('pdf')->info('========== END getStudentResultData ==========', [
                'student_id'     => $id,
                'students_count' => $students->count() ?? 0,
                'scores_count'   => $scores ? $scores->count() : 0,
                'memory_usage'   => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            ]);

            return $result;

        } catch (Exception $e) {
            Log::channel('pdf')->error('========== ERROR in getStudentResultData ==========', [
                'student_id'    => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid'     => $sessionid,
                'termid'        => $termid,
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile(),
                'error_line'    => $e->getLine(),
                'error_trace'   => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Get column options for PDF generation
     */
    public function getColumnOptions(Request $request)
    {
        Log::info('Getting column options', ['request' => $request->all()]);

        $schoolclassid = $request->input('schoolclassid');
        $sessionid     = $request->input('sessionid');
        $termid        = $request->input('termid');

        if (!$schoolclassid || !$sessionid || !$termid) {
            Log::error('Missing parameters for column options', $request->all());
            return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
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
                        'assessment_count' => $assessments->count(),
                        'category_ids'     => $categoryIds->toArray(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error loading assessments for column options', ['error' => $e->getMessage()]);
            }
        }

        $columns = [
            'student_info' => [
                'sn'           => ['label' => 'SN', 'default' => true],
                'admission_no' => ['label' => 'Admission No', 'default' => true],
                'name'         => ['label' => 'Name', 'default' => true],
                'picture'      => ['label' => 'Picture', 'default' => true],
                'gender'       => ['label' => 'Gender', 'default' => false],
                'dob'          => ['label' => 'Date of Birth', 'default' => false],
            ],
            'assessments' => [],
            'scores' => [
                'total'         => ['label' => 'Total', 'default' => true],
                'bf'            => ['label' => 'BF', 'default' => true],
                'cum'           => ['label' => 'Cum', 'default' => true],
                'grade'         => ['label' => 'Grade', 'default' => true],
                'position'      => ['label' => 'Position', 'default' => true],
                'class_average' => ['label' => 'Class Avg', 'default' => true],
            ],
            'gpa_metrics' => [
                'num_subjects'       => ['label' => 'Num Subjects', 'default' => true],
                'total_grade_points' => ['label' => 'Total GP', 'default' => true],
                'gpa'                => ['label' => 'GPA', 'default' => true],
                'calculated_gpa'     => ['label' => 'Calc GPA', 'default' => true],
                'gpa_grade'          => ['label' => 'GPA Grade', 'default' => true],
                'cgpa'               => ['label' => 'CGPA', 'default' => true],
            ],
            'other' => [
                'compulsory_flag' => ['label' => 'Compulsory', 'default' => false],
                'vetted_status'   => ['label' => 'Vetted Status', 'default' => true],
            ],
        ];

        foreach ($assessments as $assessment) {
            $columns['assessments'][$assessment->id] = [
                'label'               => $assessment->name . ' (' . $assessment->max_score . ')',
                'default'             => true,
                'is_assessment'       => true,
                'max_score'           => $assessment->max_score,
                'has_sub_assessments' => $assessment->subAssessments->isNotEmpty(),
            ];
        }

        Log::info('Column options prepared', [
            'total_columns'    => count($columns['student_info']) + count($columns['assessments']) +
                                  count($columns['scores']) + count($columns['gpa_metrics']) +
                                  count($columns['other']),
            'assessment_count' => count($columns['assessments']),
        ]);

        return response()->json([
            'success'           => true,
            'columns'           => $columns,
            'assessments_count' => $assessments->count(),
            'is_senior'         => $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? ($schoolclass->classcategories->first()->is_senior ?? false)
                : false,
        ]);
    }

    /**
     * Calculate grade preview for AJAX requests
     */
    public function calculateGradePreview(Request $request)
    {
        Log::debug('Calculating grade preview', ['request' => $request->all()]);

        $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'total'          => 'required|numeric|min:0|max:100',
        ]);

        $grade = $this->calculateGrade($request->total);

        return response()->json(['grade' => $grade]);
    }

    /**
     * Display student result view
     */
    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        Log::info('Displaying student result view', compact('id', 'schoolclassid', 'sessionid', 'termid'));

        $pagetitle         = "Student Personality Profile";
        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);

        if (!$metricsCalculated) {
            return back()->with('error', 'Failed to calculate class metrics. Please try again.');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

        return view('studentreports.studentresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Display student mock result
     */
    public function studentmockresult($id, $schoolclassid, $sessionid, $termid)
    {
        Log::info('Displaying student mock result', compact('id', 'schoolclassid', 'sessionid', 'termid'));

        $pagetitle         = "Student Mock Result";
        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);

        if (!$metricsCalculated) {
            return back()->with('error', 'Failed to calculate class metrics. Please try again.');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

        return view('studentreports.studentmockresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Display class broadsheet
     */
    public function classBroadsheet($schoolclassid, $sessionid, $termid): View
    {
        Log::info('Displaying class broadsheet', compact('schoolclassid', 'sessionid', 'termid'));

        $class     = Schoolclass::findOrFail($schoolclassid);
        $session   = Schoolsession::findOrFail($sessionid);
        $term      = $termid;
        $pagetitle = "Broadsheet for {$class->schoolclass} - {$session->session} - Term {$term}";

        return view('studentreports.broadsheet', [
            'class'     => $class,
            'session'   => $session,
            'term'      => $term,
            'pagetitle' => $pagetitle,
        ]);
    }

    /**
     * Get registered classes
     */
    public function registeredClasses(Request $request)
    {
        Log::info('Getting registered classes', ['request' => $request->all()]);

        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id');

        if (!$classId || !$sessionId || $classId === 'ALL' || $sessionId === 'ALL') {
            return response()->json(['success' => false, 'message' => 'Please select a valid class and session.'], 400);
        }

        $classes = Studentclass::query()
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolclass.id', $classId)
            ->where('schoolsession.id', $sessionId)
            ->where('schoolsession.status', 'Current')
            ->groupBy('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm', 'schoolsession.session')
            ->selectRaw('
                schoolclass.schoolclass as class_name,
                schoolarm.arm as name_arm,
                schoolsession.session as session_name,
                COUNT(DISTINCT studentclass.studentId) as student_count
            ')
            ->get();

        return response()->json(['success' => true, 'data' => $classes]);
    }

    /**
     * Export single student result as PDF
     */
    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            Log::channel('pdf')->info('========== START SINGLE STUDENT PDF EXPORT ==========', [
                'student_id'    => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid'     => $sessionid,
                'termid'        => $termid,
                'timestamp'     => now()->toDateTimeString(),
                'memory_limit'  => ini_get('memory_limit'),
            ]);

            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return back()->with('error', 'Failed to calculate class metrics. Please try again.');
            }

            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

            if (empty($data) || empty($data['students']) || $data['students']->isEmpty()) {
                return back()->with('error', 'No student data found for the provided parameters.');
            }

            Log::debug('Fixing image paths for single student PDF');
            $this->fixImagePaths([$data]);

            $student     = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename    = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession']->session . '_Term_' . $data['termid'] . '.pdf';

            $pdf = Pdf::loadView('studentreports.studentresult_pdf', ['data' => $data])
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi'                    => 150,
                    'defaultFont'            => 'DejaVu Sans',
                    'isRemoteEnabled'        => true,
                    'isHtml5ParserEnabled'   => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled'           => false,
                    'chroot'                 => [public_path(), storage_path()],
                    'fontCache'              => storage_path('fonts/'),
                    'logOutputFile'          => storage_path('logs/dompdf.log'),
                    'debugCss'               => config('app.debug', false),
                    'debugLayout'            => config('app.debug', false),
                ]);

            Log::info('PDF generated successfully for single student', [
                'student_id' => $id,
                'filename'   => $filename,
                'file_size'  => strlen($pdf->output()) . ' bytes',
            ]);

            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::channel('pdf')->error('========== ERROR SINGLE STUDENT PDF EXPORT ==========', [
                'student_id'    => $id,
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile(),
                'error_line'    => $e->getLine(),
                'error_trace'   => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export class results as PDF
     */
    public function exportClassResultsPdf(Request $request)
    {
        try {
            Log::info('========== START CLASS PDF EXPORT ==========', [
                'request_data' => $request->all(),
                'timestamp'    => now()->toDateTimeString(),
                'server_ip'    => request()->server('SERVER_ADDR') ?? 'unknown',
                'client_ip'    => request()->ip(),
                'user_agent'   => request()->header('User-Agent'),
                'memory_limit' => ini_get('memory_limit'),
            ]);

            $this->checkServerRequirements();
            $this->debugStorageStructure();

            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            $schoolclassid   = $request->input('schoolclassid');
            $sessionid       = $request->input('sessionid');
            $termid          = $request->input('termid', 3);
            $studentIds      = $request->input('studentIds', []);
            $selectedColumns = $request->input('selectedColumns', []);

            if (!$schoolclassid || !$sessionid || !$termid) {
                return response()->json(['success' => false, 'message' => 'Missing required parameters: schoolclassid, sessionid, termid'], 400);
            }

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return response()->json(['success' => false, 'message' => 'Failed to calculate class metrics. Please try again.'], 500);
            }

            $allStudentData = [];
            $processedCount = 0;
            $failedCount    = 0;

            foreach ($studentIds as $index => $studentId) {
                $studentData = $this->getStudentResultData($studentId, $schoolclassid, $sessionid, $termid);

                if (
                    !empty($studentData) &&
                    !empty($studentData['students']) &&
                    $studentData['students']->isNotEmpty()
                ) {
                    $studentData['selected_columns'] = $selectedColumns;
                    $allStudentData[]                = $studentData;
                    $processedCount++;
                } else {
                    $failedCount++;
                    Log::warning('Skipped student due to empty data', [
                        'student_id' => $studentId,
                        'iteration'  => $index + 1,
                    ]);
                }
            }

            Log::info('Student data processing completed', [
                'total_students' => count($studentIds),
                'processed'      => $processedCount,
                'failed'         => $failedCount,
            ]);

            if (empty($allStudentData)) {
                $this->debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process student data. Check server logs for details.',
                    'details' => 'No valid student data could be retrieved. Please verify student enrollment and scores.',
                ], 500);
            }

            Log::info('Fixing image paths for all student data');
            $this->fixImagePaths($allStudentData);

            $schoolclass   = Schoolclass::where('id', $schoolclassid)->with(['arms', 'classcategories'])->first(['id', 'schoolclass', 'arm']);
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $term          = $this->getTermName($termid);
            $className     = $schoolclass
                ? ($schoolclass->schoolclass . ($schoolclass->arms ? $schoolclass->arms->arm : ''))
                : 'Class';

            $filename = 'Class_Results_'
                . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_'
                . preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_'
                . $term . '.pdf';

            $viewName = 'studentreports.class_results_pdf';
            if (!view()->exists($viewName)) {
                return response()->json(['success' => false, 'message' => 'PDF template view not found: ' . $viewName], 500);
            }

            $viewData = [
                'allStudentData' => $allStudentData,
                'metadata'       => [
                    'class_name'       => $className,
                    'session'          => $schoolsession,
                    'term'             => $term,
                    'generation_date'  => now()->format('Y-m-d H:i:s'),
                    'student_count'    => count($allStudentData),
                    'selected_columns' => $selectedColumns,
                ],
            ];

            $pdf = Pdf::loadView($viewName, $viewData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi'                    => 96,
                    'defaultFont'            => 'DejaVu Sans',
                    'isRemoteEnabled'        => true,
                    'isHtml5ParserEnabled'   => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled'           => false,
                    'chroot'                 => [public_path(), storage_path()],
                    'tempDir'                => storage_path('app/temp/'),
                    'fontCache'              => storage_path('fonts/'),
                    'logOutputFile'          => storage_path('logs/dompdf.log'),
                    'isJavascriptEnabled'    => false,
                    'enable_css_float'       => true,
                    'debugLayout'            => false,
                    'debugCss'               => false,
                    'debugKeepTemp'          => false,
                ]);

            $pdfContent = $pdf->output();

            if (empty($pdfContent)) {
                return response()->json(['success' => false, 'message' => 'Generated PDF content is empty'], 500);
            }

            Log::info('PDF generated successfully, returning inline response', [
                'size_bytes' => strlen($pdfContent),
                'filename'   => $filename,
            ]);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));

        } catch (Exception $e) {
            Log::error('========== ERROR CLASS PDF EXPORT ==========', [
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile(),
                'error_line'    => $e->getLine(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now()->toDateTimeString(),
                'memory_usage'  => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            ]);

            return response()->json([
                'success'    => false,
                'message'    => 'Failed to generate PDF: ' . $e->getMessage(),
                'error_type' => get_class($e),
            ], 500);

        } finally {
            Log::info('========== END CLASS PDF EXPORT ==========', [
                'timestamp'      => now()->toDateTimeString(),
                'execution_time' => round(microtime(true) - LARAVEL_START, 2) . ' seconds',
                'final_memory'   => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            ]);
        }
    }

    /**
     * Check server requirements for PDF generation
     */
    private function checkServerRequirements()
    {
        $checks = [
            'storage_writable'       => is_writable(storage_path()),
            'temp_dir_writable'      => is_writable(storage_path('app/temp')),
            'dompdf_installed'       => class_exists('Barryvdh\DomPDF\PDF'),
            'php_memory_limit'       => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'public_storage_exists'  => file_exists(public_path('storage')),
            'student_avatars_exists' => file_exists(public_path('storage/student_avatars')),
            'school_logos_exists'    => file_exists(public_path('storage/school_logos')),
            'php_version'            => PHP_VERSION,
            'laravel_version'        => app()->version(),
            'server_software'        => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        ];

        Log::info('Server requirements check', $checks);

        if (!$checks['temp_dir_writable']) {
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
                Log::info('Created temp directory', ['path' => $tempDir]);
            }
        }

        $defaultStudentImage = public_path('storage/student_avatars/unnamed.jpg');
        $defaultSchoolLogo   = public_path('storage/school_logos/default.jpg');

        if (!file_exists($defaultStudentImage)) {
            Log::warning('Default student image not found', ['path' => $defaultStudentImage]);
            $studentDir = dirname($defaultStudentImage);
            if (!file_exists($studentDir)) mkdir($studentDir, 0755, true);
        }

        if (!file_exists($defaultSchoolLogo)) {
            Log::warning('Default school logo not found', ['path' => $defaultSchoolLogo]);
            $logoDir = dirname($defaultSchoolLogo);
            if (!file_exists($logoDir)) mkdir($logoDir, 0755, true);
        }

        return $checks;
    }

    /**
     * Get absolute image path for a given relative path
     */
    private function getAbsoluteImagePath($path, $isStudent = false)
    {
        Log::debug('Getting absolute image path', [
            'original_path' => $path,
            'is_student'    => $isStudent,
        ]);

        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, public_path()) || str_starts_with($path, storage_path())) {
            $exists = file_exists($path);
            return $exists ? $path : null;
        }

        if (str_starts_with($path, 'data:image')) {
            return null;
        }

        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = preg_replace('/^(http:\/\/|https:\/\/|\/\/)[^\/]+/', '', $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        $possiblePaths = [];

        if ($isStudent) {
            $possiblePaths = [
                public_path('storage/student_avatars/' . $path),
                storage_path('app/public/student_avatars/' . $path),
                public_path('storage/' . $path),
                storage_path('app/public/' . $path),
                public_path('uploads/students/' . $path),
                public_path('images/students/' . $path),
                storage_path('app/uploads/students/' . $path),
                public_path($path),
                storage_path($path),
            ];
        } else {
            $possiblePaths = [
                storage_path('app/public/' . $path),
                public_path('storage/' . $path),
                storage_path('app/public/school_logos/' . basename($path)),
                public_path('storage/school_logos/' . basename($path)),
                public_path($path),
                public_path(basename($path)),
                public_path('school_logos/' . basename($path)),
                storage_path($path),
                storage_path(basename($path)),
                base_path('public/' . $path),
                base_path('public/storage/' . $path),
                storage_path('app/' . $path),
                public_path('uploads/' . $path),
                public_path('uploads/school_logos/' . basename($path)),
            ];
        }

        $possiblePaths = array_unique($possiblePaths);

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath)) {
                Log::debug('Image FOUND at path', [
                    'path'        => $fullPath,
                    'file_size'   => filesize($fullPath) . ' bytes',
                    'is_readable' => is_readable($fullPath),
                    'is_student'  => $isStudent,
                ]);
                return $fullPath;
            }
        }

        Log::warning('Image NOT found in any possible paths', [
            'original_path'         => $path,
            'is_student'            => $isStudent,
            'checked_paths_count'   => count($possiblePaths),
        ]);
        return null;
    }

    /**
     * Convert image to base64 for PDF embedding
     */
    private function imageToBase64($imagePath)
    {
        if (str_starts_with((string) $imagePath, 'data:image')) {
            return $imagePath;
        }

        if (!$imagePath || !file_exists($imagePath)) {
            Log::warning('Image file does not exist for base64 conversion', ['path' => $imagePath]);

            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                    <rect width="100" height="100" fill="#f0f0f0" stroke="#ddd" stroke-width="2"/>
                    <circle cx="50" cy="40" r="15" fill="#ddd"/>
                    <rect x="35" y="60" width="30" height="25" fill="#ddd" rx="2"/>
                    <text x="50" y="95" text-anchor="middle" fill="#999" font-family="Arial" font-size="8">No Image</text>
                </svg>';

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }

        try {
            $imageData = file_get_contents($imagePath);
            if (empty($imageData)) {
                throw new \Exception('Image file is empty');
            }

            $mimeType = mime_content_type($imagePath);
            if (!$mimeType) {
                $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'gif'  => 'image/gif',
                    'svg'  => 'image/svg+xml',
                    'webp' => 'image/webp',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
            }

            $base64 = base64_encode($imageData);
            $result = "data:{$mimeType};base64,{$base64}";

            Log::debug('Image converted to base64', [
                'path'        => $imagePath,
                'mime_type'   => $mimeType,
                'file_size'   => filesize($imagePath) . ' bytes',
                'data_length' => strlen($base64) . ' bytes',
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to convert image to base64', [
                'path'  => $imagePath,
                'error' => $e->getMessage(),
            ]);

            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                    <rect width="100" height="100" fill="#f8f9fa"/>
                    <text x="50" y="50" text-anchor="middle" dy=".3em" fill="#6c757d" font-family="Arial" font-size="10">Error</text>
                </svg>'
            );
        }
    }

    /**
     * Fix image paths for PDF embedding
     */
    private function fixImagePaths(&$studentData)
    {
        Log::info('Fixing image paths for student data', ['student_count' => count($studentData)]);

        $defaultStudentImage = public_path('storage/student_avatars/unnamed.jpg');
        $defaultSchoolLogo   = public_path('storage/school_logos/default.jpg');

        if (!file_exists($defaultStudentImage)) {
            Log::warning('Default student image not found', ['path' => $defaultStudentImage]);
            $studentDir = dirname($defaultStudentImage);
            if (!file_exists($studentDir)) mkdir($studentDir, 0755, true);
            $this->createPlaceholderImage($defaultStudentImage, 'Student');
        }

        if (!file_exists($defaultSchoolLogo)) {
            Log::warning('Default school logo not found', ['path' => $defaultSchoolLogo]);
            $logoDir = dirname($defaultSchoolLogo);
            if (!file_exists($logoDir)) mkdir($logoDir, 0755, true);
            $this->createPlaceholderImage($defaultSchoolLogo, 'School');
        }

        foreach ($studentData as $index => &$student) {
            Log::debug("Processing student {$index} image paths");

            // Student image
            if (isset($student['students']) && $student['students']->isNotEmpty() && $student['students']->first()->picture) {
                $imagePath    = $student['students']->first()->picture;
                $absolutePath = $this->getAbsoluteImagePath($imagePath, true);

                if ($absolutePath && file_exists($absolutePath)) {
                    $student['student_image_base64'] = $this->imageToBase64($absolutePath);
                    Log::debug('Student image found and converted', [
                        'student_index' => $index,
                        'original_path' => $imagePath,
                        'absolute_path' => $absolutePath,
                    ]);
                } else {
                    $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
                    Log::warning('Using default student image', [
                        'student_index' => $index,
                        'original_path' => $imagePath,
                    ]);
                }
            } else {
                $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
                Log::debug('No student picture, using default', ['student_index' => $index]);
            }

            // School logo
            if (isset($student['schoolInfo'])) {
                $hasLogoInDatabase = !empty($student['schoolInfo']->school_logo);
                $logoPath          = $student['schoolInfo']->school_logo;

                if ($hasLogoInDatabase && $logoPath) {
                    $absolutePath = $this->getAbsoluteImagePath($logoPath, false);

                    if ($absolutePath && file_exists($absolutePath)) {
                        $fileSize = filesize($absolutePath);
                        if ($fileSize > 100) {
                            $student['school_logo_base64'] = $this->imageToBase64($absolutePath);
                            Log::info('Actual school logo found and used', [
                                'student_index' => $index,
                                'file_size'     => $fileSize,
                                'path'          => $absolutePath,
                            ]);
                        } else {
                            Log::warning('Logo file exists but is too small', [
                                'student_index' => $index,
                                'file_size'     => $fileSize,
                            ]);
                            $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
                        }
                    } else {
                        Log::warning('Logo in database but file not found', [
                            'student_index' => $index,
                            'logo_path'     => $logoPath,
                        ]);
                        $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
                    }
                } else {
                    Log::info('No logo in database, using default', ['student_index' => $index]);
                    $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
                }
            } else {
                Log::warning('No schoolInfo found, using default logo', ['student_index' => $index]);
                $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
            }
        }

        Log::info('Image path fixing completed', ['students_processed' => count($studentData)]);
    }

    /**
     * Create a placeholder image
     */
    private function createPlaceholderImage($path, $text)
    {
        try {
            $width           = 300;
            $height          = 200;
            $image           = imagecreatetruecolor($width, $height);
            $backgroundColor = imagecolorallocate($image, 240, 240, 240);
            $textColor       = imagecolorallocate($image, 153, 153, 153);

            imagefill($image, 0, 0, $backgroundColor);

            $font       = 5;
            $textWidth  = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            $x          = ($width - $textWidth) / 2;
            $y          = ($height - $textHeight) / 2;

            imagestring($image, $font, $x, $y, $text, $textColor);
            imagejpeg($image, $path, 80);
            imagedestroy($image);

            Log::info('Created placeholder image', ['path' => $path, 'text' => $text]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create placeholder image', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Debug storage structure
     */
    private function debugStorageStructure()
    {
        Log::info('Debugging storage structure');

        $pathsToCheck = [
            'storage/app/public'              => storage_path('app/public'),
            'storage/app/public/school_logos' => storage_path('app/public/school_logos'),
            'public/storage'                  => public_path('storage'),
            'public/storage/school_logos'     => public_path('storage/school_logos'),
            'public/school_logos'             => public_path('school_logos'),
            'storage'                         => storage_path(),
            'public'                          => public_path(),
        ];

        foreach ($pathsToCheck as $name => $path) {
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $files     = scandir($path);
                    $fileCount = count($files) - 2;
                    Log::info("Directory: {$name}", [
                        'path'         => $path,
                        'exists'       => true,
                        'is_dir'       => true,
                        'file_count'   => $fileCount,
                        'files_sample' => array_slice(
                            array_filter($files, fn ($f) => !in_array($f, ['.', '..'])),
                            0, 10
                        ),
                    ]);
                } else {
                    Log::info("File: {$name}", [
                        'path'    => $path,
                        'exists'  => true,
                        'is_dir'  => false,
                        'size'    => filesize($path) . ' bytes',
                    ]);
                }
            } else {
                Log::warning("Not found: {$name}", ['path' => $path]);
            }
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

        $termName = $terms[$termid] ?? 'Unknown Term';
        Log::debug('Getting term name', ['term_id' => $termid, 'term_name' => $termName]);

        return $termName;
    }

    /**
     * Debug student query for troubleshooting
     */
    private function debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid)
    {
        Log::info('DEBUG: Running direct database queries to check data');

        $studentsExist     = Student::whereIn('id', $studentIds)->count();
        $studentsInClass   = Studentclass::whereIn('studentId', $studentIds)
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->count();
        $broadsheetRecords = DB::table('broadsheet_records')
            ->whereIn('student_id', $studentIds)
            ->where('schoolclass_id', $schoolclassid)
            ->where('session_id', $sessionid)
            ->count();
        $broadsheets       = DB::table('broadsheets')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->whereIn('broadsheet_records.student_id', $studentIds)
            ->where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheets.term_id', $termid)
            ->count();

        Log::info('DEBUG: Student data checks', [
            'student_ids'         => $studentIds,
            'students_found'      => $studentsExist,
            'students_in_class'   => $studentsInClass,
            'broadsheet_records'  => $broadsheetRecords,
            'broadsheets_found'   => $broadsheets,
        ]);

        if ($broadsheets > 0) {
            $sampleData = DB::table('broadsheets')
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->whereIn('broadsheet_records.student_id', $studentIds)
                ->where('broadsheet_records.schoolclass_id', $schoolclassid)
                ->where('broadsheet_records.session_id', $sessionid)
                ->where('broadsheets.term_id', $termid)
                ->select(
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'subject.subject',
                    'broadsheets.total',
                    'broadsheets.grade'
                )
                ->limit(5)
                ->get();

            Log::info('DEBUG: Sample broadsheet data', ['sample_data' => $sampleData->toArray()]);
        }
    }

    /**
     * Test PDF generation endpoint
     */
    public function testPdfGeneration(Request $request)
    {
        Log::info('Test PDF generation endpoint called');

        try {
            $testStudentId = Student::first()->id ?? null;
            $testClassId   = Schoolclass::first()->id ?? null;
            $testSessionId = Schoolsession::first()->id ?? null;

            if (!$testStudentId || !$testClassId || !$testSessionId) {
                return response()->json(['success' => false, 'message' => 'Test data not available in database']);
            }

            $studentData = $this->getStudentResultData($testStudentId, $testClassId, $testSessionId, 3);

            $result = [
                'success'           => !empty($studentData),
                'student_data_keys' => array_keys($studentData),
                'has_students'      => isset($studentData['students']) && !$studentData['students']->isEmpty(),
                'has_scores'        => isset($studentData['scores']) && !$studentData['scores']->isEmpty(),
                'student_count'     => $studentData['students']->count() ?? 0,
                'scores_count'      => $studentData['scores']->count() ?? 0,
                'server_info'       => [
                    'storage_writable'   => is_writable(storage_path()),
                    'php_version'        => PHP_VERSION,
                    'memory_limit'       => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'laravel_version'    => app()->version(),
                ],
                'paths' => [
                    'public_path'           => public_path(),
                    'storage_path'          => storage_path(),
                    'default_student_image' => public_path('storage/student_avatars/unnamed.jpg'),
                    'default_school_logo'   => public_path('storage/school_logos/default.jpg'),
                ],
                'file_exists' => [
                    'default_student_image' => file_exists(public_path('storage/student_avatars/unnamed.jpg')),
                    'default_school_logo'   => file_exists(public_path('storage/school_logos/default.jpg')),
                ],
            ];

            Log::info('Test completed', $result);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Test PDF generation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Display student reports index
     */
    public function index(Request $request): View|JsonResponse
    {
        Log::info('ViewStudentReportController index method called', [
            'request_params' => $request->all(),
            'ajax'           => $request->ajax(),
            'method'         => $request->method(),
        ]);

        $pagetitle   = "Student Terminal Report Management";
        $current     = "Current";
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

                Log::debug('Search filter applied', ['search_term' => $search]);
            }

            $allstudents = $query->select([
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentRegistration.id as stid',
                'studentpicture.picture as picture',
                'studentclass.schoolclassid as schoolclassID',
                'studentclass.sessionid as sessionid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session',
            ])->latest('studentclass.created_at')->paginate(100);

            Log::info('Student query executed', [
                'schoolclassid' => $request->input('schoolclassid'),
                'sessionid'     => $request->input('sessionid'),
                'student_count' => $allstudents->total(),
            ]);
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
