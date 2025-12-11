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
    }

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

    protected function calculateJuniorGrade($score)
    {
        if ($score >= 70 && $score <= 100) {
            return 'A';
        } elseif ($score >= 60) {
            return 'B';
        } elseif ($score >= 50) {
            return 'C';
        } elseif ($score >= 40) {
            return 'D';
        }
        return 'F';
    }

    protected function getDefaultGrade($score)
    {
        return $this->calculateJuniorGrade($score);
    }

    protected function getRemark($grade)
    {
        $remarks = [
            'A' => 'Excellent',
            'B' => 'Very Good',
            'C' => 'Good',
            'D' => 'Pass',
            'F' => 'Fail',
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

    // NEW: Get grade point for GPA calculation
    protected function getGradePoint($score, $isSenior = false)
    {
        if (!$isSenior) {
            // Junior scale
            if ($score >= 70) {
                return 5.0;
            } elseif ($score >= 60) {
                return 4.0;
            } elseif ($score >= 50) {
                return 3.0;
            } elseif ($score >= 40) {
                return 2.0;
            }
            return 0.0;
        } else {
            // Senior scale
            if ($score >= 75) {
                return 5.0;
            } elseif ($score >= 65) {
                return 4.0;
            } elseif ($score >= 50) {
                return 3.0;
            } elseif ($score >= 45) {
                return 2.0;
            } elseif ($score >= 40) {
                return 1.0;
            }
            return 0.0;
        }
    }

    // NEW: Compute overall GPA and CGPA for a student
    protected function computeOverallGPAAndCGPAForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior)
    {
        // Current Term GPA and Grade (across all subjects)
        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);

        // Compute average total score for GPA Grade using totals
        $averageTotal = $currentTermBroadsheets->avg('total') ?? 0.0;
        $category = $schoolclass->classcategories->first();
        $gpaGrade = $category ? $category->calculateGrade($averageTotal) : $this->getDefaultGrade($averageTotal);
        
        $termGradePoints = $currentTermBroadsheets->map(function ($b) use ($isSenior) {
            return $this->getGradePoint($b->total, $isSenior);
        });
        
        $gpa = $termGradePoints->avg() ?? 0.0;
        $num_subjects = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();

        // CGPA: Average of up to 3 most recent sessions' annual GPAs
        $annualGPAs = [];
        $studentSessionsInCategory = DB::table('broadsheet_records')
            ->join('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->where('broadsheet_records.student_id', $studentId)
            ->where('classcategories.is_senior', $isSenior)
            ->select('broadsheet_records.session_id')
            ->distinct()
            ->orderByDesc('broadsheet_records.session_id')
            ->limit(3)
            ->pluck('session_id');

        foreach ($studentSessionsInCategory as $targetSession) {
            $sessionAnnualGPAs = [];
            for ($t = 1; $t <= 3; $t++) {
                $termBroadsheets = Broadsheets::where('term_id', $t)
                    ->whereHas('broadsheetRecord', function ($q) use ($studentId, $targetSession) {
                        $q->where('student_id', $studentId)->where('session_id', $targetSession);
                    })
                    ->get(['total']);
                
                $termGradePointsPast = $termBroadsheets->map(function ($b) use ($isSenior) {
                    return $this->getGradePoint($b->total, $isSenior);
                });
                
                $termGPA = $termGradePointsPast->avg() ?? 0.0;
                $sessionAnnualGPAs[] = $termGPA;
            }
            $annualGPA = collect($sessionAnnualGPAs)->avg() ?? 0.0;
            $annualGPAs[] = $annualGPA;
        }

        $cgpa = collect($annualGPAs)->avg() ?? 0.0;

        return [
            'gpa' => $gpa,
            'cgpa' => $cgpa,
            'gpa_grade' => $gpaGrade,
            'num_subjects' => $num_subjects,
            'total_grade_points' => $total_grade_points,
            'calculated_gpa' => $num_subjects > 0 ? round($total_grade_points / $num_subjects, 2) : 0.0,
        ];
    }

    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";

        // Force cache invalidation to ensure fresh calculations
        Cache::forget($cacheKey);

        $schoolclass = Schoolclass::with('classcategories')->where('id', $schoolclassid)->first(['id', 'schoolclass']);
        if (!$schoolclass) {
            Log::warning('Schoolclass not found', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return false;
        }
        $className = $schoolclass->schoolclass;
        
        // Get the first class category (many-to-many relationship)
        $isSenior = false;
        if ($schoolclass->classcategories->isNotEmpty()) {
            $classCategory = $schoolclass->classcategories->first();
            $isSenior = $classCategory ? $classCategory->is_senior : false;
        }

        $classIds = Schoolclass::where('schoolclass', $className)->pluck('id')->toArray();
        if (empty($classIds)) {
            Log::warning('No schoolclass IDs found for class name', [
                'class_name' => $className,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return false;
        }

        $students = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($students)) {
            Log::warning('No students found for class', [
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return false;
        }

        $success = DB::transaction(function () use ($schoolclassid, $sessionid, $termid, $className, $classIds, $students, $isSenior, $classCategory, &$subjectGroups) {
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
                    'broadsheets.cum',
                    'broadsheets.subject_position_class',
                    'broadsheets.avg',
                    'broadsheets.grade',
                    'broadsheets.remark',
                ])
                ->get();

            if ($broadsheets->isEmpty()) {
                Log::warning('No broadsheet records found for class', [
                    'class_name' => $className,
                    'schoolclassids' => $classIds,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return false;
            }

            $subjectGroups = $broadsheets->groupBy('subject_id');

            foreach ($subjectGroups as $subjectId => $subjectRecords) {
                $subjectName = $subjectRecords->first()->subject_name;
                $validRecords = $subjectRecords->filter(function ($record) {
                    return $record->cum != 0;
                });
                $totalScores = $validRecords->sum('total');
                $studentCount = $validRecords->count();
                $classAvg = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

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

                foreach ($subjectRecords as $record) {
                    $newPosition = $record->cum == 0 ? '-' : ($positionMap[$record->id] ?? null);
                    if ($newPosition !== '-') {
                        $newPosition = $this->formatOrdinal($newPosition);
                    }

                    $grade = $record->cum == 0 ? '-' : (
                        $isSenior && $classCategory !== null
                            ? $classCategory->calculateGrade($record->cum)
                            : $this->calculateJuniorGrade($record->cum)
                    );
                    $remark = $this->getRemark($grade);

                    if (
                        $record->avg != $classAvg ||
                        $record->subject_position_class != $newPosition ||
                        $record->grade != $grade ||
                        $record->remark != $remark
                    ) {
                        Broadsheets::where('id', $record->id)->update([
                            'avg' => $classAvg,
                            'subject_position_class' => $newPosition,
                            'grade' => $grade,
                            'remark' => $remark,
                        ]);

                        Log::info('Updated broadsheet metrics', [
                            'broadsheet_id' => $record->id,
                            'student_id' => $record->student_id,
                            'admission_no' => $record->admission_no,
                            'subject_id' => $subjectId,
                            'subject_name' => $subjectName,
                            'class_avg' => $classAvg,
                            'subject_position_class' => $newPosition,
                            'grade' => $grade,
                            'remark' => $remark,
                            'class_name' => $className,
                            'cum' => $record->cum,
                        ]);
                    }
                }

                Log::info('Calculated metrics for subject', [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'class_name' => $className,
                    'schoolclassids' => $classIds,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'class_avg' => $classAvg,
                    'student_count' => $studentCount,
                    'total_scores' => $totalScores,
                ]);
            }

            return true;
        });

        if ($success) {
            Cache::put($cacheKey, true, now()->addHours(1));
            Log::info('Completed class metrics calculation', [
                'class_name' => $className,
                'schoolclassids' => $classIds,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'total_subjects' => $subjectGroups->count(),
                'total_students' => count($students),
            ]);
        } else {
            Log::error('Failed to calculate class metrics', [
                'class_name' => $className,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
        }

        return $success;
    }

    private function getStudentResultData($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            Log::info('Starting getStudentResultData', [
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

            Log::info('Fetching student basic info', ['student_id' => $id]);
            
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
                    'studentRegistration.present_address as present_address',
                    'studentRegistration.permanent_address as permanent_address',
                    'studentRegistration.updated_at as updated_at',
                    'studentpicture.picture as picture'
                ])
                ->orderBy('studentRegistration.lastname', 'asc')
                ->get();

            Log::info('Student query results', [
                'student_id' => $id,
                'found_students' => $students->count(),
                'student_names' => $students->pluck('fname')->toArray(),
            ]);

            if ($students->isEmpty()) {
                Log::warning('No active student found for ID', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                $students = collect([]);
            }

            // Get school class for assessments and GPA calculations
            $schoolclass = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);
            $assessments = collect();
            $isSenior = false;
            
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
                
                // Try to load assessments if model exists
                try {
                    if (class_exists(\App\Models\Assessment::class)) {
                        $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                            ->with('subAssessments')
                            ->orderBy('id')
                            ->get();
                    }
                } catch (\Exception $e) {
                    Log::warning('Assessment model not found or error loading assessments', [
                        'error' => $e->getMessage(),
                    ]);
                }
                
                Log::info('Found assessments for class', [
                    'class_id' => $schoolclassid,
                    'category_ids' => $categoryIds->toArray(),
                    'assessment_count' => $assessments->count(),
                    'assessment_names' => $assessments->pluck('name')->toArray(),
                    'is_senior' => $isSenior,
                ]);
            }

            // Fetch scores with retry mechanism
            $scores = null;
            $attempts = 0;
            $maxAttempts = 3;
            $retryDelay = 500; // milliseconds

            Log::info('Fetching broadsheet scores', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);

            while ($attempts < $maxAttempts) {
                $scores = Broadsheets::where('broadsheet_records.student_id', $id)
                    ->where('broadsheets.term_id', $termid)
                    ->where('broadsheet_records.session_id', $sessionid)
                    ->where('broadsheet_records.schoolclass_id', $schoolclassid)
                    ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
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

                // For each score, fetch dynamic assessment scores if model exists
                foreach ($scores as $score) {
                    try {
                        if (class_exists(\App\Models\BroadsheetAssessmentScore::class)) {
                            $assessmentScores = \App\Models\BroadsheetAssessmentScore::where('broadsheet_id', $score->broadsheet_id)
                                ->with('assessment')
                                ->orderBy('assessment_id')
                                ->get();
                            
                            // Map first 4 assessments to ca1, ca2, ca3, exam for backward compatibility
                            $assessmentArray = $assessmentScores->values();
                            
                            // Reset all CA fields to 0 first
                            $score->ca1 = 0;
                            $score->ca2 = 0;
                            $score->ca3 = 0;
                            $score->exam = 0;
                            
                            // Map available assessments
                            if ($assessmentArray->count() > 0) $score->ca1 = $assessmentArray->get(0)->score ?? 0;
                            if ($assessmentArray->count() > 1) $score->ca2 = $assessmentArray->get(1)->score ?? 0;
                            if ($assessmentArray->count() > 2) $score->ca3 = $assessmentArray->get(2)->score ?? 0;
                            if ($assessmentArray->count() > 3) $score->exam = $assessmentArray->get(3)->score ?? 0;
                            
                            // Store full assessment data
                            $score->assessment_scores = $assessmentScores;
                            $score->assessments = $assessments;
                            
                            Log::debug('Mapped assessment scores', [
                                'subject' => $score->subject_name,
                                'ca1' => $score->ca1,
                                'ca2' => $score->ca2,
                                'ca3' => $score->ca3,
                                'exam' => $score->exam,
                                'total_assessments' => $assessmentArray->count(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error loading assessment scores', [
                            'error' => $e->getMessage(),
                            'broadsheet_id' => $score->broadsheet_id,
                        ]);
                    }
                }

                Log::info('Broadsheet query attempt', [
                    'attempt' => $attempts + 1,
                    'scores_found' => $scores->count(),
                    'subject_names' => $scores->pluck('subject_name')->toArray(),
                ]);

                // Verify if grades are populated
                $hasValidGrades = $scores->every(function ($score) {
                    return $score->grade !== '-' && $score->grade !== null;
                });

                if ($hasValidGrades || $scores->isEmpty()) {
                    break;
                }

                Log::warning('Retrying fetch of broadsheet data due to incomplete grades', [
                    'student_id' => $id,
                    'attempt' => $attempts + 1,
                    'scores_count' => $scores->count(),
                    'invalid_grades_count' => $scores->where('grade', '-')->count(),
                ]);

                usleep($retryDelay * 1000);
                $attempts++;
            }

            if ($attempts >= $maxAttempts) {
                Log::error('Failed to fetch valid broadsheet data after retries', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'scores_count' => $scores ? $scores->count() : 0,
                ]);
            }

            Log::info('Fetched broadsheet data', [
                'student_id' => $id,
                'scores_count' => $scores ? $scores->count() : 0,
                'grades' => $scores ? $scores->pluck('grade')->toArray() : [],
            ]);

            // Calculate GPA and CGPA for the student
            $gpaData = [];
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $gpaData = $this->computeOverallGPAAndCGPAForStudent(
                    $id,
                    $schoolclass,
                    $termid,
                    $sessionid,
                    $isSenior
                );
                
                Log::info('Calculated GPA/CGPA for student', [
                    'student_id' => $id,
                    'gpa' => $gpaData['gpa'] ?? 0,
                    'cgpa' => $gpaData['cgpa'] ?? 0,
                    'gpa_grade' => $gpaData['gpa_grade'] ?? 'F',
                    'num_subjects' => $gpaData['num_subjects'] ?? 0,
                    'total_grade_points' => $gpaData['total_grade_points'] ?? 0,
                ]);
            }

            // Fetch student personality profile
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
                Log::warning('Error fetching student personality profile', [
                    'student_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $studentpp = collect();
            }

            // Fetch session and term details
            $schoolsession = Schoolsession::where('id', $sessionid)->first();
            $schoolterm = Schoolterm::where('id', $termid)->first();

            // Get number of students in class
            $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->count();

            // Get school information
            $schoolInfo = SchoolInformation::first();

            // Get promotion status
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
                Log::warning('Error fetching promotion status', [
                    'student_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Fetch compulsory subjects for the class
            $compulsorySubjects = [];
            try {
                $compulsorySubjects = CompulsorySubjectClass::where('class_id', $schoolclassid)
                    ->pluck('subject_id')
                    ->toArray();
            } catch (\Exception $e) {
                Log::warning('Error fetching compulsory subjects', [
                    'class_id' => $schoolclassid,
                    'error' => $e->getMessage(),
                ]);
            }

            // Add compulsory flag to scores
            if ($scores) {
                foreach ($scores as $score) {
                    $score->is_compulsory = in_array($score->subject_id, $compulsorySubjects);
                }
            }

            return [
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
                'gpa_data' => $gpaData, // NEW: Add GPA/CGPA data
                'is_senior' => $isSenior, // NEW: Add senior flag
            ];
        } catch (Exception $e) {
            Log::error('Error fetching student result data', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    // NEW: Method to get column selection data
    public function getColumnOptions(Request $request)
    {
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
                Log::warning('Error loading assessments for column options', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Define column groups
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

        // Add dynamic assessments
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

    public function calculateGradePreview(Request $request)
    {
        $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'cum' => 'required|numeric|min:0|max:100',
        ]);

        $schoolclass = Schoolclass::with('classcategories')->findOrFail($request->schoolclass_id);
        $grade = $this->getDefaultGrade($request->cum);
        
        if ($schoolclass->classcategories->isNotEmpty()) {
            $classCategory = $schoolclass->classcategories->first();
            $grade = $classCategory->calculateGrade($request->cum);
        }

        return response()->json(['grade' => $grade]);
    }

    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Personality Profile";
        
        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
        if (!$metricsCalculated) {
            Log::error('Failed to calculate class metrics for student result', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return back()->with('error', 'Failed to calculate class metrics. Please try again.');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);
        
        return view('studentreports.studentresult')->with($data)->with('pagetitle', $pagetitle);
    }

    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

            Log::info('Generating single student PDF', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                Log::error('Failed to calculate class metrics for PDF generation', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return back()->with('error', 'Failed to calculate class metrics. Please try again.');
            }

            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

            if (empty($data) || empty($data['students']) || $data['students']->isEmpty()) {
                Log::error('No valid student data for PDF generation', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return back()->with('error', 'No student data found for the provided parameters.');
            }

            $student = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession']->session . '_Term_' . $data['termid'] . '.pdf';

            $this->fixImagePaths([$data]);

            $pdf = Pdf::loadView('studentreports.studentresult_pdf', ['data' => $data])
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled' => false,
                    'chroot' => [public_path(), storage_path()],
                    'fontCache' => storage_path('fonts/'),
                    'logOutputFile' => storage_path('logs/dompdf.log'),
                    'debugCss' => config('app.debug', false),
                    'debugLayout' => config('app.debug', false),
                ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Single Student PDF Export Error', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function exportClassResultsPdf(Request $request)
    {
        try {
            ini_set('max_execution_time', 1200);
            ini_set('memory_limit', '2048M');

            $schoolclassid = $request->input('schoolclassid');
            $sessionid = $request->input('sessionid');
            $termid = $request->input('termid', 3);
            $studentIds = $request->input('studentIds', []);
            $selectedColumns = $request->input('selectedColumns', []);

            Log::info('Starting class results PDF generation', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'studentIds' => $studentIds,
                'selectedColumns' => $selectedColumns,
            ]);

            if (!is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parameters provided. All IDs must be numeric.'
                ], 400);
            }

            if (!Schoolclass::find($schoolclassid) || !Schoolsession::find($sessionid) || !Schoolterm::find($termid)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid class, session, or term ID.'
                ], 400);
            }

            // Calculate class metrics
            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                Log::error('Failed to calculate class metrics before generating PDF', [
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to calculate class metrics. Please try again.'
                ], 500);
            }

            $query = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->where('schoolsession.status', '=', 'Current')
                ->select('studentRegistration.id', 'studentRegistration.firstname', 'studentRegistration.lastname')
                ->orderBy('studentRegistration.lastname', 'asc');

            if (!empty($studentIds)) {
                $query->whereIn('studentRegistration.id', $studentIds);
            }

            $students = $query->get();

            if ($students->isEmpty()) {
                Log::warning('No students found for class or selected students', [
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'studentIds' => $studentIds,
                    'query_sql' => $query->toSql(),
                    'query_bindings' => $query->getBindings(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No students found for the selected class, session, or selected students.'
                ], 404);
            }

            Log::info('Processing students for PDF', [
                'student_count' => $students->count(),
                'student_ids' => $students->pluck('id')->toArray(),
            ]);

            $allStudentData = [];
            $processedStudents = 0;
            $skippedStudents = 0;

            foreach ($students as $student) {
                try {
                    Log::info('Processing student for PDF', [
                        'student_id' => $student->id,
                        'student_name' => $student->firstname . ' ' . $student->lastname,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                    ]);

                    $studentData = $this->getStudentResultData($student->id, $schoolclassid, $sessionid, $termid);
                    
                    if ($this->validateStudentData($studentData)) {
                        // Add column visibility data
                        $studentData['selected_columns'] = $selectedColumns;
                        $allStudentData[] = $studentData;
                        $processedStudents++;
                        
                        Log::info('Processed student data successfully', [
                            'student_id' => $student->id,
                            'student_name' => $student->firstname . ' ' . $student->lastname,
                            'promotion_status' => $studentData['promotionStatusValue'] ?? 'N/A',
                            'gpa' => $studentData['gpa_data']['gpa'] ?? 'N/A',
                            'cgpa' => $studentData['gpa_data']['cgpa'] ?? 'N/A',
                            'scores_count' => $studentData['scores']->count(),
                        ]);
                    } else {
                        $skippedStudents++;
                        Log::warning('Skipping student due to invalid/missing data', [
                            'student_id' => $student->id,
                            'student_name' => $student->firstname . ' ' . $student->lastname,
                            'schoolclassid' => $schoolclassid,
                            'sessionid' => $sessionid,
                            'termid' => $termid,
                        ]);
                    }
                } catch (Exception $e) {
                    $skippedStudents++;
                    Log::error('Error processing student data', [
                        'student_id' => $student->id,
                        'student_name' => $student->firstname . ' ' . $student->lastname,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            Log::info('Student data collection completed', [
                'processed' => $processedStudents,
                'skipped' => $skippedStudents,
                'total' => $students->count(),
                'total_student_data' => count($allStudentData),
            ]);

            if (empty($allStudentData)) {
                Log::error('No valid student data found for PDF generation after processing all students', [
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'total_students' => $students->count(),
                    'processed' => $processedStudents,
                    'skipped' => $skippedStudents,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No valid student data found for PDF generation.'
                ], 404);
            }

            $this->fixImagePaths($allStudentData);

            $schoolclass = Schoolclass::where('id', $schoolclassid)->with(['arms', 'classcategories'])->first(['id', 'schoolclass', 'arm']);
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $term = $this->getTermName($termid);
            $className = $schoolclass ? ($schoolclass->schoolclass . ($schoolclass->arms ? $schoolclass->arms->arm : '')) : 'Class';
            $filename = 'Class_Results_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_' . 
                        preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_' . $term . '.pdf';

            Log::info('Preparing PDF data', [
                'filename' => $filename,
                'class_name' => $className,
                'session' => $schoolsession,
                'term' => $term,
                'student_count_in_pdf' => count($allStudentData),
                'selected_columns_count' => count($selectedColumns),
            ]);

            $viewName = 'studentreports.class_results_pdf';
            if (!view()->exists($viewName)) {
                Log::error('PDF view not found', ['view' => $viewName]);
                return response()->json([
                    'success' => false,
                    'message' => 'PDF template view not found: ' . $viewName,
                ], 500);
            }

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

            try {
                $viewContent = view($viewName, $viewData)->render();
                Log::info('View rendered successfully', ['content_length' => strlen($viewContent)]);
            } catch (Exception $e) {
                Log::error('View rendering failed', [
                    'view' => $viewName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to render PDF template: ' . $e->getMessage(),
                ], 500);
            }

            $this->ensureDirectoriesExist();

            Log::info('Starting PDF generation with DomPDF');

            $pdf = Pdf::loadView($viewName, $viewData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi' => 96,
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled' => false,
                    'chroot' => [public_path(), storage_path()],
                    'tempDir' => storage_path('app/temp/'),
                    'fontCache' => storage_path('fonts/'),
                    'logOutputFile' => storage_path('logs/dompdf.log'),
                    'isJavascriptEnabled' => false,
                    'enable_css_float' => true,
                    'debugLayout' => false,
                    'debugCss' => false,
                    'debugKeepTemp' => false,
                ])
                ->setWarnings(true);

            Log::info('PDF object created successfully');

            $pdfContent = $pdf->output();
            Log::info('PDF content generated', ['size' => strlen($pdfContent)]);

            if (empty($pdfContent)) {
                Log::error('PDF content is empty');
                return response()->json([
                    'success' => false,
                    'message' => 'Generated PDF content is empty',
                    'error_code' => 'EMPTY_PDF_CONTENT',
                ], 500);
            }

            if (!str_starts_with($pdfContent, '%PDF')) {
                Log::error('Invalid PDF content generated', [
                    'content_start' => substr($pdfContent, 0, 100),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid PDF content generated',
                    'error_code' => 'INVALID_PDF_CONTENT',
                ], 500);
            }

            Log::info('PDF validation successful');

            // Default to download response
            $responseMethod = $request->input('response_method', 'download');

            Log::info('Using response method', ['method' => $responseMethod]);

            switch ($responseMethod) {
                case 'save_and_redirect':
                    Log::info('Returning save_and_redirect response');
                    return $this->saveAndRedirectResponse($pdfContent, $filename);
                case 'base64':
                    Log::info('Returning base64 response', [
                        'pdf_size' => strlen($pdfContent),
                        'base64_size' => strlen(base64_encode($pdfContent))
                    ]);
                    return $this->base64Response($pdfContent, $filename);
                case 'chunked':
                    Log::info('Returning chunked response');
                    return $this->chunkedResponse($pdfContent, $filename);
                case 'download':
                    Log::info('Returning download response');
                    return $this->downloadResponse($pdfContent, $filename);
                case 'inline':
                    Log::info('Returning inline response');
                    return $this->inlineResponse($pdfContent, $filename);
                default:
                    Log::warning('Unknown response method, defaulting to download', [
                        'method' => $responseMethod
                    ]);
                    return $this->downloadResponse($pdfContent, $filename);
            }
        } catch (Exception $e) {
            Log::error('Class PDF Export Error', [
                'schoolclassid' => $schoolclassid ?? 'N/A',
                'sessionid' => $sessionid ?? 'N/A',
                'termid' => $termid ?? 'N/A',
                'studentIds' => $studentIds ?? [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
                'error_code' => 'PDF_EXPORT_FAILED',
            ], 500);
        }
    }

    private function fixImagePaths(&$studentData)
{
    foreach ($studentData as &$student) {
        // Student image handling
        $defaultStudentImage = public_path('storage/student_avatars/unnamed.jpg');
        
        if (isset($student['students']) && $student['students']->isNotEmpty() && $student['students']->first()->picture) {
            $imagePath = $student['students']->first()->picture;
            $absolutePath = $this->getAbsoluteImagePath($imagePath);
            
            if (file_exists($absolutePath)) {
                $student['student_image_base64'] = $this->imageToBase64($absolutePath);
                $student['student_image_path'] = $absolutePath;
            } else {
                $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
                $student['student_image_path'] = $defaultStudentImage;
            }
            
            Log::info('Student image path set', [
                'student_id' => $student['students']->first()->id,
                'path' => $student['student_image_path'],
                'exists' => file_exists($student['student_image_path'])
            ]);
        } else {
            $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
            $student['student_image_path'] = $defaultStudentImage;
            Log::info('Using default student image', ['path' => $student['student_image_path']]);
        }
        
        // School logo handling
        $defaultSchoolLogo = public_path('storage/school_logos/default.jpg');
        
        if (isset($student['schoolInfo'])) {
            $logoPath = $student['schoolInfo']->getLogoUrlAttribute();
            $absolutePath = $this->getAbsoluteImagePath($logoPath);
            
            if (file_exists($absolutePath)) {
                $student['school_logo_base64'] = $this->imageToBase64($absolutePath);
                $student['school_logo_path'] = $absolutePath;
            } else {
                $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
                $student['school_logo_path'] = $defaultSchoolLogo;
            }
            
            Log::info('School logo path set', [
                'path' => $student['school_logo_path'],
                'exists' => file_exists($student['school_logo_path'])
            ]);
        } else {
            $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
            $student['school_logo_path'] = $defaultSchoolLogo;
            Log::info('Using default school logo', ['path' => $student['school_logo_path']]);
        }
    }
}

/**
 * Convert image to base64 data URI
 */
private function imageToBase64($imagePath)
{
    if (!file_exists($imagePath)) {
        Log::warning('Image file does not exist for base64 conversion', ['path' => $imagePath]);
        return '';
    }
    
    try {
        $imageData = file_get_contents($imagePath);
        $mimeType = mime_content_type($imagePath);
        $base64 = base64_encode($imageData);
        return "data:{$mimeType};base64,{$base64}";
    } catch (\Exception $e) {
        Log::error('Failed to convert image to base64', [
            'path' => $imagePath,
            'error' => $e->getMessage()
        ]);
        return '';
    }
}
    // ============= RESPONSE METHODS =============
    
    private function base64Response($pdfContent, $filename)
    {
        return response()->json([
            'success' => true,
            'data' => base64_encode($pdfContent),
            'filename' => $filename,
            'message' => 'PDF generated successfully',
            'size' => strlen($pdfContent),
        ]);
    }

    private function saveAndRedirectResponse($pdfContent, $filename)
    {
        $tempPath = storage_path('app/temp/' . $filename);
        file_put_contents($tempPath, $pdfContent);
        
        $publicPath = 'temp_pdfs/' . $filename;
        $publicFullPath = public_path($publicPath);
        
        if (!file_exists(public_path('temp_pdfs'))) {
            mkdir(public_path('temp_pdfs'), 0755, true);
        }
        
        file_put_contents($publicFullPath, $pdfContent);
        
        return response()->json([
            'success' => true,
            'url' => url($publicPath),
            'filename' => $filename,
            'message' => 'PDF saved successfully',
        ]);
    }

    private function downloadResponse($pdfContent, $filename)
    {
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent));
    }

    private function inlineResponse($pdfContent, $filename)
    {
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent));
    }

    private function chunkedResponse($pdfContent, $filename)
    {
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent))
            ->header('Transfer-Encoding', 'chunked');
    }
    // ============= END RESPONSE METHODS =============


    private function getAbsoluteImagePath($path)
    {
        if (empty($path)) {
            return public_path('storage/student_avatars/unnamed.jpg');
        }

        // Remove any URL protocols
        $path = preg_replace('/^(http:\/\/|https:\/\/|\/\/)[^\/]+/', '', $path);
        
        // Normalize path separators
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        
        // Ensure it's in the storage directory
        if (!preg_match('/^(storage|school_logos|student_avatars)/', $path)) {
            $path = 'storage/' . $path;
        }
        
        // Get absolute path
        $fullPath = public_path($path);
        $fullPath = realpath($fullPath) ?: $fullPath;
        
        if (file_exists($fullPath)) {
            return $fullPath;
        }
        
        // Return default if file doesn't exist
        if (str_contains($path, 'student_avatars')) {
            return public_path('storage/student_avatars/unnamed.jpg');
        }
        
        return public_path('storage/school_logos/default.jpg');
    }

    private function sanitizeImagePath($path)
    {
        if (empty($path)) {
            Log::warning('Empty image path provided');
            return null;
        }

        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = preg_replace('/^(http:\/\/|https:\/\/|\/\/)[^\/]+/', '', $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        if (!preg_match('/^(storage|school_logos|student_avatars)/', $path)) {
            $path = 'storage/' . $path;
        }
        
        $fullPath = public_path($path);
        $fullPath = realpath($fullPath) ?: $fullPath;
        
        if (file_exists($fullPath)) {
            Log::info('Sanitized image path', ['original' => $path, 'sanitized' => $fullPath]);
            return $fullPath;
        }
        
        Log::warning('Image file does not exist', ['path' => $fullPath]);
        return null;
    }

    private function ensureDirectoriesExist()
    {
        $directories = [
            storage_path('app/temp'),
            storage_path('fonts'),
            storage_path('logs'),
            public_path('temp_pdfs')
        ];

        foreach ($directories as $all) {
            if (!file_exists($all)) {
                mkdir($all, 0755, true);
                Log::info('Created directory', ['path' => $all]);
            }
        }
    }

    private function getTermName($termid)
    {
        $terms = [
            1 => 'First Term',
            2 => 'Second Term',
            3 => 'Third Term'
        ];
        
        return $terms[$termid] ?? 'Unknown Term';
    }

    private function validateStudentData($studentData): bool
    {
        if (empty($studentData)) {
            Log::warning('Student data is completely empty');
            return false;
        }

        if (!isset($studentData['students'])) {
            Log::warning('Students key not found in student data', [
                'available_keys' => array_keys($studentData),
            ]);
            return false;
        }

        if (!$studentData['students'] || $studentData['students']->isEmpty()) {
            Log::warning('Students collection is empty', [
                'students_type' => gettype($studentData['students']),
                'students_is_collection' => $studentData['students'] instanceof \Illuminate\Support\Collection,
                'students_count' => $studentData['students'] ? $studentData['students']->count() : 0,
            ]);
            return false;
        }

        if (!isset($studentData['scores'])) {
            Log::warning('Scores key not found in student data', [
                'available_keys' => array_keys($studentData),
            ]);
            return false;
        }

        $student = $studentData['students']->first();
        if (!$student) {
            Log::warning('First student object is null or empty');
            return false;
        }

        Log::info('Student data validation passed', [
            'student_id' => $student->id ?? 'unknown',
            'student_name' => ($student->fname ?? '') . ' ' . ($student->lastname ?? ''),
            'scores_count' => $studentData['scores'] ? $studentData['scores']->count() : 0,
            'has_gpa_data' => isset($studentData['gpa_data']),
            'gpa' => $studentData['gpa_data']['gpa'] ?? 'N/A',
            'cgpa' => $studentData['gpa_data']['cgpa'] ?? 'N/A',
        ]);
        
        return true;
    }

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

        if (config('app.debug')) {
            Log::info('Sessions for select:', $schoolsessions->toArray());
            Log::info('Students fetched:', $allstudents->toArray());
        }

        if ($request->ajax()) {
            return response()->json([
                'tableBody' => view('studentreports.partials.student_rows', compact('allstudents'))->render(),
                'pagination' => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('studentreports.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }

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

    public function studentmockresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Student Mock Result";
        
        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
        if (!$metricsCalculated) {
            Log::error('Failed to calculate class metrics for mock result', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return back()->with('error', 'Failed to calculate class metrics. Please try again.');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);
        
        return view('studentreports.studentmockresult')->with($data)->with('pagetitle', $pagetitle);
    }
}