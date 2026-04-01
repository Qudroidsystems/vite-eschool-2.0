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
     * Calculate grade based on TOTAL score using WAEC/NECO standard
     * This is the master grading function
     */
    protected function calculateGrade($score)
    {
        Log::debug('Calculating grade', ['score' => $score]);

        // Handle null, zero, or negative scores
        if ($score === null || $score <= 0) {
            return 'F9';
        }

        // WAEC/NECO Grading Scheme - Based on TOTAL score
        if ($score >= 75 && $score <= 100) {
            return 'A1';
        } elseif ($score >= 70 && $score <= 74) {
            return 'B2';
        } elseif ($score >= 65 && $score <= 69) {
            return 'B3';
        } elseif ($score >= 60 && $score <= 64) {
            return 'C4';
        } elseif ($score >= 55 && $score <= 59) {
            return 'C5';
        } elseif ($score >= 50 && $score <= 54) {
            return 'C6';
        } elseif ($score >= 45 && $score <= 49) {
            return 'D7';
        } elseif ($score >= 40 && $score <= 44) {
            return 'E8';
        } else {
            return 'F9';
        }
    }

    /**
     * Get grade point based on score using WAEC/NECO standard
     */
    protected function getGradePoint($score)
    {
        Log::debug('Calculating grade point', ['score' => $score]);

        if ($score === null || $score <= 0) {
            return 0.0;
        }

        // WAEC/NECO Grade Point System
        if ($score >= 75 && $score <= 100) {
            return 5.0; // A1
        } elseif ($score >= 70 && $score <= 74) {
            return 4.5; // B2
        } elseif ($score >= 65 && $score <= 69) {
            return 4.0; // B3
        } elseif ($score >= 60 && $score <= 64) {
            return 3.5; // C4
        } elseif ($score >= 55 && $score <= 59) {
            return 3.0; // C5
        } elseif ($score >= 50 && $score <= 54) {
            return 2.5; // C6
        } elseif ($score >= 45 && $score <= 49) {
            return 2.0; // D7
        } elseif ($score >= 40 && $score <= 44) {
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
     * Get GPA letter grade based on GPA value
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
     * Compute overall GPA and CGPA for a student
     * GPA = Average of grade points for current term using TOTAL score
     * CGPA = Average of GPAs for all completed terms in current session
     */
    protected function computeOverallGPAAndCGPAForStudent($studentId, $schoolclass, $termId, $sessionId)
    {
        Log::info('Computing GPA/CGPA for student', [
            'student_id' => $studentId,
            'term_id' => $termId,
            'session_id' => $sessionId,
            'class_name' => $schoolclass->schoolclass ?? 'Unknown'
        ]);

        // Get current term scores - use TOTAL for GPA calculation
        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);

        Log::debug('Current term broadsheets', [
            'student_id' => $studentId,
            'count' => $currentTermBroadsheets->count(),
            'total_scores' => $currentTermBroadsheets->pluck('total')->toArray()
        ]);

        // Calculate grade points based on TOTAL scores
        $termGradePoints = $currentTermBroadsheets->map(function ($b) {
            return $this->getGradePoint($b->total);
        });

        $gpa = $termGradePoints->avg() ?? 0.0;
        $num_subjects = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();

        Log::debug('GPA calculations', [
            'gpa' => $gpa,
            'num_subjects' => $num_subjects,
            'total_grade_points' => $total_grade_points,
            'grade_points' => $termGradePoints->toArray()
        ]);

        // Calculate CGPA - Average of all completed terms in the current session
        $termGPAs = [];

        // Loop through all terms (1, 2, 3) up to the current term
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

                Log::debug('Term GPA calculation for CGPA', [
                    'term' => $t,
                    'term_gpa' => $termGPA,
                    'broad_sheet_count' => $termBroadsheets->count()
                ]);
            }
        }

        // CGPA is the average of all completed term GPAs
        $cgpa = !empty($termGPAs) ? collect($termGPAs)->avg() : 0.0;

        // Get GPA grade based on GPA value
        $gpaGrade = $this->getGpaGrade($gpa);

        $result = [
            'gpa' => round($gpa, 2),
            'cgpa' => round($cgpa, 2),
            'gpa_grade' => $gpaGrade,
            'num_subjects' => $num_subjects,
            'total_grade_points' => round($total_grade_points, 1),
            'calculated_gpa' => $num_subjects > 0 ? round($total_grade_points / $num_subjects, 2) : 0.0,
        ];

        Log::info('GPA/CGPA computation completed', $result);

        return $result;
    }

    /**
     * Calculate class positions, averages, and grades for all subjects
     * CRITICAL: Uses TOTAL score for grading and position calculation
     */
    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";

        Log::info('Starting class metrics calculation', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'cache_key' => $cacheKey
        ]);

        Cache::forget($cacheKey);

        $schoolclass = Schoolclass::with('classcategories')->where('id', $schoolclassid)->first(['id', 'schoolclass']);
        if (!$schoolclass) {
            Log::error('Schoolclass not found', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return false;
        }

        $className = $schoolclass->schoolclass;
        Log::debug('School class found', ['class_name' => $className, 'class_id' => $schoolclassid]);

        $classIds = Schoolclass::where('schoolclass', $className)->pluck('id')->toArray();
        if (empty($classIds)) {
            Log::error('No schoolclass IDs found for class name', [
                'class_name' => $className,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return false;
        }

        Log::debug('Class IDs for calculation', [
            'primary_class_id' => $schoolclassid,
            'all_class_ids' => $classIds,
            'class_name' => $className
        ]);

        $students = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($students)) {
            Log::error('No students found for class', [
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return false;
        }

        Log::debug('Students found for metrics calculation', [
            'student_count' => count($students),
            'student_ids_sample' => array_slice($students, 0, 5)
        ]);

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

            Log::debug('Broadsheets fetched for metrics', [
                'broad_sheet_count' => $broadsheets->count(),
                'unique_subjects' => $broadsheets->groupBy('subject_id')->count(),
                'unique_students' => $broadsheets->groupBy('student_id')->count()
            ]);

            if ($broadsheets->isEmpty()) {
                Log::error('No broadsheet records found for class', [
                    'class_name' => $className,
                    'schoolclassids' => $classIds,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'student_ids' => $students
                ]);
                return false;
            }

            // Get class category for grade calculation
            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
            $classCategory = $schoolclass->classcategories->first();

            $subjectGroups = $broadsheets->groupBy('subject_id');
            Log::info('Grouped broadsheets by subject', ['subject_count' => $subjectGroups->count()]);

            foreach ($subjectGroups as $subjectId => $subjectRecords) {
                $subjectName = $subjectRecords->first()->subject_name;
                Log::debug('Processing subject for metrics', [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'record_count' => $subjectRecords->count()
                ]);

                // Calculate class average using TOTAL scores
                $validRecords = $subjectRecords->filter(function ($record) {
                    return $record->total != 0 && $record->total !== null;
                });

                $totalScores = $validRecords->sum('total');
                $studentCount = $validRecords->count();
                $classAvg = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

                Log::debug('Subject metrics calculated', [
                    'subject_name' => $subjectName,
                    'valid_records' => $validRecords->count(),
                    'total_scores' => $totalScores,
                    'class_average' => $classAvg,
                    'student_count' => $studentCount
                ]);

                // Calculate positions based on TOTAL scores with ordinal formatting
                $sortedRecords = $validRecords->sortByDesc('total')->values();

                $rank = 0;
                $lastTotal = null;
                $lastPosition = 0;
                $positionMap = [];

                foreach ($sortedRecords as $record) {
                    $rank++;
                    if ($lastTotal !== null && $record->total == $lastTotal) {
                        $positionMap[$record->id] = $lastPosition;
                    } else {
                        $lastPosition = $rank;
                        $lastTotal = $record->total;
                        $positionMap[$record->id] = $lastPosition;
                    }
                }

                $updatesCount = 0;
                foreach ($subjectRecords as $record) {
                    // CRITICAL: Use TOTAL score for grade calculation
                    $scoreForGrading = $record->total;

                    // Calculate grade based on TOTAL score
                    if ($classCategory && $scoreForGrading > 0) {
                        $grade = $classCategory->calculateGrade($scoreForGrading);
                    } else {
                        // Fallback to direct calculation if no category
                        $grade = $this->calculateGrade($scoreForGrading);
                    }

                    $remark = $this->getRemark($grade);

                    // Format position with ordinal suffix (based on TOTAL score)
                    $newPosition = $record->total == 0 ? '-' : (
                        isset($positionMap[$record->id]) ? $this->formatOrdinal($positionMap[$record->id]) : '-'
                    );

                    Log::debug('Grade calculation check', [
                        'subject_name' => $subjectName,
                        'student_id' => $record->student_id,
                        'total_score' => $record->total,
                        'calculated_grade' => $grade,
                        'current_grade' => $record->grade,
                        'position' => $newPosition,
                        'has_category' => !is_null($classCategory)
                    ]);

                    if (
                        $record->avg != $classAvg ||
                        $record->subject_position_class != $newPosition ||
                        $record->grade != $grade ||
                        $record->remark != $remark
                    ) {
                        $updateResult = Broadsheets::where('id', $record->id)->update([
                            'avg' => $classAvg,
                            'subject_position_class' => $newPosition,
                            'grade' => $grade,
                            'remark' => $remark,
                        ]);

                        if ($updateResult) {
                            $updatesCount++;
                        }

                        Log::info('Broadsheet updated', [
                            'broadsheet_id' => $record->id,
                            'student_id' => $record->student_id,
                            'subject_name' => $subjectName,
                            'total_score' => $record->total,
                            'old_grade' => $record->grade,
                            'new_grade' => $grade,
                            'old_position' => $record->subject_position_class,
                            'new_position' => $newPosition
                        ]);
                    }
                }

                Log::info('Subject processing completed', [
                    'subject_name' => $subjectName,
                    'updates_applied' => $updatesCount,
                    'total_records' => $subjectRecords->count()
                ]);
            }

            return true;
        });

        if ($success) {
            Cache::put($cacheKey, true, now()->addHours(1));
            Log::info('Class metrics calculation completed successfully', [
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'total_subjects' => $subjectGroups ? $subjectGroups->count() : 0,
                'total_students' => count($students),
            ]);
        } else {
            Log::error('Failed to calculate class metrics in transaction', [
                'class_name' => $className,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
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
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'timestamp' => now()->toDateTimeString(),
            ]);

            if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
                Log::error('Invalid parameters in getStudentResultData', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return [];
            }

            // Fetch student basic info
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
                    'studentpicture.picture as picture'
                ])
                ->get();

            if ($students->isEmpty()) {
                Log::error('No active student found for ID', ['student_id' => $id]);
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
                    }
                } catch (\Exception $e) {
                    Log::error('Error loading assessments', ['error' => $e->getMessage()]);
                }
            }

            // Fetch scores - IMPORTANT: Use TOTAL for display and grading
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

            // Get class category for grade calculation
            $classCategory = $schoolclass->classcategories->first();

            // Ensure grades are correct based on TOTAL score
            foreach ($scores as $score) {
                // Recalculate grade based on TOTAL score
                $correctGrade = $classCategory ?
                    $classCategory->calculateGrade($score->total) :
                    $this->calculateGrade($score->total);

                if ($score->grade != $correctGrade) {
                    Log::warning('Grade mismatch detected', [
                        'subject' => $score->subject_name,
                        'total_score' => $score->total,
                        'current_grade' => $score->grade,
                        'correct_grade' => $correctGrade
                    ]);
                    $score->grade = $correctGrade;
                    $score->remark = $this->getRemark($correctGrade);

                    // Update database
                    Broadsheets::where('id', $score->broadsheet_id)->update([
                        'grade' => $correctGrade,
                        'remark' => $this->getRemark($correctGrade)
                    ]);
                }
            }

            // Load assessment scores
            foreach ($scores as $score) {
                try {
                    if (class_exists(\App\Models\BroadsheetAssessmentScore::class)) {
                        $assessmentScores = \App\Models\BroadsheetAssessmentScore::where('broadsheet_id', $score->broadsheet_id)
                            ->with('assessment')
                            ->orderBy('assessment_id')
                            ->get();

                        $assessmentArray = $assessmentScores->values();

                        $score->ca1 = $assessmentArray->get(0)->score ?? 0;
                        $score->ca2 = $assessmentArray->get(1)->score ?? 0;
                        $score->ca3 = $assessmentArray->get(2)->score ?? 0;
                        $score->exam = $assessmentArray->get(3)->score ?? 0;

                        $score->assessment_scores = $assessmentScores;
                        $score->assessments = $assessments;
                    }
                } catch (\Exception $e) {
                    Log::error('Error loading assessment scores', ['error' => $e->getMessage()]);
                }
            }

            // Calculate GPA/CGPA
            $gpaData = [];
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                try {
                    $gpaData = $this->computeOverallGPAAndCGPAForStudent(
                        $id,
                        $schoolclass,
                        $termid,
                        $sessionid
                    );
                } catch (\Exception $e) {
                    Log::error('Error calculating GPA/CGPA', ['error' => $e->getMessage()]);
                    $gpaData = [
                        'gpa' => 0.0,
                        'cgpa' => 0.0,
                        'gpa_grade' => 'F9',
                        'num_subjects' => 0,
                        'total_grade_points' => 0,
                        'calculated_gpa' => 0.0,
                    ];
                }
            }

            // Get student personality profile
            $studentpp = Studentpersonalityprofile::where('studentpersonalityprofiles.studentid', $id)
                ->where('studentpersonalityprofiles.termid', $termid)
                ->where('studentpersonalityprofiles.sessionid', $sessionid)
                ->where('studentpersonalityprofiles.schoolclassid', $schoolclassid)
                ->get();

            $schoolsession = Schoolsession::where('id', $sessionid)->first();
            $schoolterm = Schoolterm::where('id', $termid)->first();

            $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->count();

            $schoolInfo = SchoolInformation::first();
            if (!$schoolInfo) {
                $schoolInfo = new \stdClass();
                $schoolInfo->id = 0;
                $schoolInfo->school_name = 'School Name Not Found';
                $schoolInfo->school_logo = null;
                $schoolInfo->school_motto = 'Motto Not Found';
                $schoolInfo->school_address = 'Address Not Found';
                $schoolInfo->school_phone = 'Phone Not Found';
                $schoolInfo->date_school_opened = null;
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
                Log::error('Error fetching promotion status', ['error' => $e->getMessage()]);
            }

            $compulsorySubjects = [];
            try {
                $compulsorySubjects = CompulsorySubjectClass::where('class_id', $schoolclassid)
                    ->pluck('subject_id')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Error fetching compulsory subjects', ['error' => $e->getMessage()]);
            }

            if ($scores) {
                foreach ($scores as $score) {
                    $score->is_compulsory = in_array($score->subject_id, $compulsorySubjects);
                }
            }

            $result = [
                'students' => $students,
                'studentpp' => $studentpp,
                'scores' => $scores,
                'studentid' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'schoolclass' => $schoolclass,
                'schoolterm' => $schoolterm,
                'schoolsession' => $schoolsession,
                'numberOfStudents' => $numberOfStudents,
                'schoolInfo' => $schoolInfo,
                'promotionStatusValue' => $promotionStatusValue,
                'assessments' => $assessments,
                'compulsorySubjects' => $compulsorySubjects,
                'gpa_data' => $gpaData,
            ];

            Log::channel('pdf')->info('========== END getStudentResultData ==========', [
                'student_id' => $id,
                'scores_count' => $scores->count(),
                'has_gpa_data' => !empty($gpaData),
            ]);

            return $result;
        } catch (Exception $e) {
            Log::channel('pdf')->error('========== ERROR in getStudentResultData ==========', [
                'student_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
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
     * Calculate grade preview for AJAX requests
     */
    public function calculateGradePreview(Request $request)
    {
        $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'total' => 'required|numeric|min:0|max:100',
        ]);

        $schoolclass = Schoolclass::with('classcategories')->findOrFail($request->schoolclass_id);
        $classCategory = $schoolclass->classcategories->first();

        if ($classCategory) {
            $grade = $classCategory->calculateGrade($request->total);
        } else {
            $grade = $this->calculateGrade($request->total);
        }

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
        $pagetitle = "Student Mock Result";

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
        $class = Schoolclass::findOrFail($schoolclassid);
        $session = Schoolsession::findOrFail($sessionid);
        $term = $termid;
        $pagetitle = "Broadsheet for {$class->schoolclass} - {$session->session} - Term {$term}";

        $data = [
            'class' => $class,
            'session' => $session,
            'term' => $term,
            'pagetitle' => $pagetitle
        ];

        return view('studentreports.broadsheet', $data);
    }

    /**
     * Get registered classes
     */
    public function registeredClasses(Request $request)
    {
        $classId = $request->query('class_id');
        $sessionId = $request->query('session_id');

        if (!$classId || !$sessionId || $classId === 'ALL' || $sessionId === 'ALL') {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid class and session.'
            ], 400);
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

        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
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
                return back()->with('error', 'Failed to calculate class metrics. Please try again.');
            }

            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

            if (empty($data) || empty($data['students']) || $data['students']->isEmpty()) {
                return back()->with('error', 'No student data found for the provided parameters.');
            }

            $student = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession']->session . '_Term_' . $data['termid'] . '.pdf';

            $pdf = Pdf::loadView('studentreports.studentresult_pdf', ['data' => $data])
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Error in exportStudentResultPdf', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
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

            $schoolclassid = $request->input('schoolclassid');
            $sessionid = $request->input('sessionid');
            $termid = $request->input('termid', 3);
            $studentIds = $request->input('studentIds', []);
            $selectedColumns = $request->input('selectedColumns', []);

            if (!$schoolclassid || !$sessionid || !$termid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters'
                ], 400);
            }

            // First, calculate positions and averages for all subjects
            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to calculate class metrics. Please try again.'
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

                if (!empty($studentData) &&
                    !empty($studentData['students']) &&
                    $studentData['students']->isNotEmpty()) {

                    $studentData['selected_columns'] = $selectedColumns;
                    $allStudentData[] = $studentData;
                }
            }

            if (empty($allStudentData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid student data found'
                ], 500);
            }

            $schoolclass = Schoolclass::where('id', $schoolclassid)->with(['arms'])->first();
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $term = $this->getTermName($termid);
            $className = $schoolclass ? ($schoolclass->schoolclass . ($schoolclass->arms ? $schoolclass->arms->arm : '')) : 'Class';
            $filename = 'Class_Results_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_' .
                        preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_' . $term . '.pdf';

            $viewData = [
                'allStudentData' => $allStudentData,
                'metadata' => [
                    'class_name' => $className,
                    'session' => $schoolsession,
                    'term' => $term,
                    'generation_date' => now()->format('Y-m-d H:i:s'),
                    'student_count' => count($allStudentData),
                    'selected_columns' => $selectedColumns,
                ],
            ];

            $pdf = Pdf::loadView('studentreports.class_results_pdf', $viewData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 96,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                ]);

            $pdfContent = $pdf->output();

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));

        } catch (Exception $e) {
            Log::error('Error in exportClassResultsPdf', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
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
            3 => 'Third Term'
        ];

        return $terms[$termid] ?? 'Unknown Term';
    }

    /**
     * Display student reports index
     */
    public function index(Request $request): View|JsonResponse
    {
        $pagetitle = "Student Terminal Report Management";
        $current = "Current";

        $allstudents = new LengthAwarePaginator([], 0, 10);

        if ($request->filled('schoolclassid') && $request->filled('sessionid') && $request->input('schoolclassid') !== 'ALL' && $request->input('sessionid') !== 'ALL') {
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
                'studentclass.schoolclassid as schoolclassID',
                'studentclass.sessionid as sessionid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session',
            ])->latest('studentclass.created_at')->paginate(100);
        }

        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        if ($request->ajax()) {
            return response()->json([
                'tableBody' => view('studentreports.partials.student_rows', compact('allstudents'))->render(),
                'pagination' => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('studentreports.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }

    // Helper methods for image handling (keep these from your original code)
    private function fixImagePaths(&$studentData) {}
    private function getAbsoluteImagePath($path, $isStudent = false) { return null; }
    private function imageToBase64($imagePath) { return ''; }
    private function createPlaceholderImage($path, $text) {}
    private function checkServerRequirements() {}
    private function debugStorageStructure() {}
    private function debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid) {}
    public function testPdfGeneration(Request $request) {}
}
