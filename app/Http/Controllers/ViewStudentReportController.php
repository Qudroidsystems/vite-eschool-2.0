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
        Log::debug('Calculating junior grade', ['score' => $score]);
        
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
        Log::debug('Getting default grade', ['score' => $score]);
        return $this->calculateJuniorGrade($score);
    }

    protected function getRemark($grade)
    {
        Log::debug('Getting remark for grade', ['grade' => $grade]);
        
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
        
        $remark = $remarks[$grade] ?? 'Unknown';
        Log::debug('Remark retrieved', ['grade' => $grade, 'remark' => $remark]);
        
        return $remark;
    }

    // NEW: Get grade point for GPA calculation
    protected function getGradePoint($score, $isSenior = false)
    {
        Log::debug('Calculating grade point', ['score' => $score, 'isSenior' => $isSenior]);
        
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
        Log::info('Computing GPA/CGPA for student', [
            'student_id' => $studentId,
            'term_id' => $termId,
            'session_id' => $sessionId,
            'is_senior' => $isSenior,
            'class_name' => $schoolclass->schoolclass ?? 'Unknown'
        ]);

        // Current Term GPA and Grade (across all subjects)
        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);

        Log::debug('Current term broadsheets', [
            'student_id' => $studentId,
            'count' => $currentTermBroadsheets->count(),
            'totals' => $currentTermBroadsheets->pluck('total')->toArray()
        ]);

        // Compute average total score for GPA Grade using totals
        $averageTotal = $currentTermBroadsheets->avg('total') ?? 0.0;
        $category = $schoolclass->classcategories->first();
        $gpaGrade = $category ? $category->calculateGrade($averageTotal) : $this->getDefaultGrade($averageTotal);
        
        Log::debug('Average total and GPA grade', [
            'average_total' => $averageTotal,
            'gpa_grade' => $gpaGrade,
            'has_category' => !is_null($category)
        ]);
        
        $termGradePoints = $currentTermBroadsheets->map(function ($b) use ($isSenior) {
            return $this->getGradePoint($b->total, $isSenior);
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

        Log::debug('Student sessions in category', [
            'session_ids' => $studentSessionsInCategory->toArray(),
            'is_senior' => $isSenior
        ]);

        foreach ($studentSessionsInCategory as $targetSession) {
            $sessionAnnualGPAs = [];
            Log::debug('Processing session for CGPA', ['session_id' => $targetSession]);
            
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
                
                Log::debug('Term GPA calculation', [
                    'term' => $t,
                    'session_id' => $targetSession,
                    'term_gpa' => $termGPA,
                    'broad_sheet_count' => $termBroadsheets->count()
                ]);
            }
            
            $annualGPA = collect($sessionAnnualGPAs)->avg() ?? 0.0;
            $annualGPAs[] = $annualGPA;
            
            Log::debug('Annual GPA calculated', [
                'session_id' => $targetSession,
                'annual_gpa' => $annualGPA,
                'term_gpas' => $sessionAnnualGPAs
            ]);
        }

        $cgpa = collect($annualGPAs)->avg() ?? 0.0;

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

    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";

        Log::info('Starting class metrics calculation', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'cache_key' => $cacheKey
        ]);

        // Force cache invalidation to ensure fresh calculations
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
        
        // Get the first class category (many-to-many relationship)
        $isSenior = false;
        if ($schoolclass->classcategories->isNotEmpty()) {
            $classCategory = $schoolclass->classcategories->first();
            $isSenior = $classCategory ? $classCategory->is_senior : false;
            Log::debug('Class category info', [
                'category_id' => $classCategory->id ?? null,
                'category_name' => $classCategory->name ?? null,
                'is_senior' => $isSenior
            ]);
        }

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

            $subjectGroups = $broadsheets->groupBy('subject_id');
            Log::info('Grouped broadsheets by subject', ['subject_count' => $subjectGroups->count()]);

            foreach ($subjectGroups as $subjectId => $subjectRecords) {
                $subjectName = $subjectRecords->first()->subject_name;
                Log::debug('Processing subject for metrics', [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'record_count' => $subjectRecords->count()
                ]);
                
                $validRecords = $subjectRecords->filter(function ($record) {
                    return $record->cum != 0;
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
                        $updateResult = Broadsheets::where('id', $record->id)->update([
                            'avg' => $classAvg,
                            'subject_position_class' => $newPosition,
                            'grade' => $grade,
                            'remark' => $remark,
                        ]);
                        
                        if ($updateResult) {
                            $updatesCount++;
                        }

                        Log::debug('Broadsheet updated', [
                            'broadsheet_id' => $record->id,
                            'student_id' => $record->student_id,
                            'subject_name' => $subjectName,
                            'old_avg' => $record->avg,
                            'new_avg' => $classAvg,
                            'old_position' => $record->subject_position_class,
                            'new_position' => $newPosition,
                            'old_grade' => $record->grade,
                            'new_grade' => $grade
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
                'total_subjects' => $subjectGroups->count(),
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

    private function getStudentResultData($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            Log::channel('pdf')->info('========== START getStudentResultData ==========', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'timestamp' => now()->toDateTimeString(),
                'server_ip' => request()->server('SERVER_ADDR') ?? 'unknown',
                'client_ip' => request()->ip(),
            ]);
            
            if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
                Log::error('Invalid parameters in getStudentResultData', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'types' => [
                        'id' => gettype($id),
                        'schoolclassid' => gettype($schoolclassid),
                        'sessionid' => gettype($sessionid),
                        'termid' => gettype($termid)
                    ]
                ]);
                return [];
            }

            Log::debug('Fetching student basic info', [
                'student_id' => $id,
                'query_params' => compact('schoolclassid', 'sessionid', 'termid')
            ]);
            
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

            Log::debug('Student query executed', [
                'student_id' => $id,
                'query_success' => !is_null($students),
                'found_students' => $students->count(),
                'student_names' => $students->pluck('fname')->toArray(),
                'has_picture' => $students->isNotEmpty() && !empty($students->first()->picture)
            ]);

            if ($students->isEmpty()) {
                Log::error('No active student found for ID', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'query_time' => now()->toDateTimeString()
                ]);
                $students = collect([]);
            }

            // Get school class for assessments and GPA calculations
            $schoolclass = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);
            
            Log::debug('School class fetched', [
                'schoolclassid' => $schoolclassid,
                'class_found' => !is_null($schoolclass),
                'class_name' => $schoolclass->schoolclass ?? 'Not found',
                'arm' => $schoolclass->arms->arm ?? 'No arm',
                'category_count' => $schoolclass->classcategories->count() ?? 0
            ]);
            
            $assessments = collect();
            $isSenior = false;
            
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
                
                Log::debug('Class category info', [
                    'category_ids' => $categoryIds->toArray(),
                    'is_senior' => $isSenior,
                    'category_names' => $schoolclass->classcategories->pluck('name')->toArray()
                ]);
                
                // Try to load assessments if model exists
                try {
                    if (class_exists(\App\Models\Assessment::class)) {
                        $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                            ->with('subAssessments')
                            ->orderBy('id')
                            ->get();
                        
                        Log::debug('Assessments loaded', [
                            'assessment_count' => $assessments->count(),
                            'assessment_names' => $assessments->pluck('name')->toArray(),
                            'assessment_ids' => $assessments->pluck('id')->toArray()
                        ]);
                    } else {
                        Log::warning('Assessment model class does not exist');
                    }
                } catch (\Exception $e) {
                    Log::error('Error loading assessments', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'category_ids' => $categoryIds->toArray()
                    ]);
                }
            } else {
                Log::warning('No class categories found or schoolclass not found', [
                    'schoolclassid' => $schoolclassid,
                    'class_exists' => !is_null($schoolclass),
                    'has_categories' => $schoolclass ? $schoolclass->classcategories->isNotEmpty() : false
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
                'max_attempts' => $maxAttempts
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

                Log::debug('Broadsheet query attempt', [
                    'attempt' => $attempts + 1,
                    'scores_found' => $scores->count(),
                    'subject_names' => $scores->pluck('subject_name')->toArray(),
                    'query_time' => now()->format('H:i:s')
                ]);

                // For each score, fetch dynamic assessment scores if model exists
                foreach ($scores as $score) {
                    try {
                        if (class_exists(\App\Models\BroadsheetAssessmentScore::class)) {
                            $assessmentScores = \App\Models\BroadsheetAssessmentScore::where('broadsheet_id', $score->broadsheet_id)
                                ->with('assessment')
                                ->orderBy('assessment_id')
                                ->get();
                            
                            Log::debug('Assessment scores fetched for subject', [
                                'subject' => $score->subject_name,
                                'assessment_score_count' => $assessmentScores->count(),
                                'broadsheet_id' => $score->broadsheet_id
                            ]);
                            
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
                                'assessment_ids' => $assessmentScores->pluck('assessment_id')->toArray()
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error loading assessment scores', [
                            'error' => $e->getMessage(),
                            'broadsheet_id' => $score->broadsheet_id,
                            'subject_name' => $score->subject_name
                        ]);
                    }
                }

                // Verify if grades are populated
                $hasValidGrades = $scores->every(function ($score) {
                    return $score->grade !== '-' && $score->grade !== null;
                });

                if ($hasValidGrades || $scores->isEmpty()) {
                    Log::debug('Valid grades condition met', [
                        'has_valid_grades' => $hasValidGrades,
                        'is_empty' => $scores->isEmpty(),
                        'attempt' => $attempts + 1
                    ]);
                    break;
                }

                Log::warning('Retrying fetch of broadsheet data due to incomplete grades', [
                    'student_id' => $id,
                    'attempt' => $attempts + 1,
                    'scores_count' => $scores->count(),
                    'invalid_grades_count' => $scores->where('grade', '-')->count(),
                    'grades_found' => $scores->pluck('grade')->toArray()
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
                    'final_grades' => $scores ? $scores->pluck('grade')->toArray() : []
                ]);
            }

            Log::info('Fetched broadsheet data', [
                'student_id' => $id,
                'scores_count' => $scores ? $scores->count() : 0,
                'grades' => $scores ? $scores->pluck('grade')->toArray() : [],
                'subjects' => $scores ? $scores->pluck('subject_name')->toArray() : [],
                'total_attempts' => $attempts + 1
            ]);

            // Calculate GPA and CGPA for the student
            $gpaData = [];
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                try {
                    $gpaData = $this->computeOverallGPAAndCGPAForStudent(
                        $id,
                        $schoolclass,
                        $termid,
                        $sessionid,
                        $isSenior
                    );
                    
                    Log::info('GPA/CGPA calculation completed', array_merge(
                        ['student_id' => $id],
                        $gpaData
                    ));
                } catch (\Exception $e) {
                    Log::error('Error calculating GPA/CGPA', [
                        'student_id' => $id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $gpaData = [
                        'gpa' => 0.0,
                        'cgpa' => 0.0,
                        'gpa_grade' => 'F',
                        'num_subjects' => 0,
                        'total_grade_points' => 0,
                        'calculated_gpa' => 0.0,
                    ];
                }
            } else {
                Log::warning('Skipping GPA calculation - no class or categories', [
                    'student_id' => $id,
                    'has_schoolclass' => !is_null($schoolclass),
                    'has_categories' => $schoolclass ? $schoolclass->classcategories->isNotEmpty() : false
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
                
                Log::debug('Student personality profile fetched', [
                    'student_id' => $id,
                    'profile_count' => $studentpp->count(),
                    'has_data' => $studentpp->isNotEmpty()
                ]);
                
                if ($studentpp->isEmpty()) {
                    $studentpp = collect();
                    Log::info('No student personality profile found', ['student_id' => $id]);
                }
            } catch (\Exception $e) {
                Log::error('Error fetching student personality profile', [
                    'student_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $studentpp = collect();
            }

            // Fetch session and term details
            $schoolsession = Schoolsession::where('id', $sessionid)->first();
            $schoolterm = Schoolterm::where('id', $termid)->first();
            
            Log::debug('Session and term details', [
                'session_found' => !is_null($schoolsession),
                'session_name' => $schoolsession->session ?? 'Not found',
                'term_found' => !is_null($schoolterm),
                'term_name' => $schoolterm->term ?? 'Not found'
            ]);

            // Get number of students in class
            $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)
                ->where('sessionid', $sessionid)
                ->count();
            
            Log::debug('Class student count', [
                'class_id' => $schoolclassid,
                'session_id' => $sessionid,
                'student_count' => $numberOfStudents
            ]);

            // Get school information
            $schoolInfo = SchoolInformation::first();
            
            Log::debug('School info fetched', [
                'school_info_found' => !is_null($schoolInfo),
                'school_name' => $schoolInfo->school_name ?? 'Not found',
                'has_logo' => !empty($schoolInfo->logo)
            ]);

            // Get promotion status
            $promotionStatusValue = null;
            try {
                $promotionStatus = PromotionStatus::where('student_id', $id)
                    ->where('session_id', $sessionid)
                    ->where('term_id', $termid)
                    ->first();

                if ($promotionStatus) {
                    $promotionStatusValue = $promotionStatus->status;
                    Log::debug('Promotion status found', [
                        'student_id' => $id,
                        'status' => $promotionStatusValue
                    ]);
                } else {
                    Log::info('No promotion status found', ['student_id' => $id]);
                }
            } catch (\Exception $e) {
                Log::error('Error fetching promotion status', [
                    'student_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Fetch compulsory subjects for the class
            $compulsorySubjects = [];
            try {
                $compulsorySubjects = CompulsorySubjectClass::where('class_id', $schoolclassid)
                    ->pluck('subject_id')
                    ->toArray();
                
                Log::debug('Compulsory subjects fetched', [
                    'class_id' => $schoolclassid,
                    'subject_count' => count($compulsorySubjects),
                    'subject_ids' => $compulsorySubjects
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching compulsory subjects', [
                    'class_id' => $schoolclassid,
                    'error' => $e->getMessage()
                ]);
            }

            // Add compulsory flag to scores
            if ($scores) {
                $compulsoryCount = 0;
                foreach ($scores as $score) {
                    $score->is_compulsory = in_array($score->subject_id, $compulsorySubjects);
                    if ($score->is_compulsory) $compulsoryCount++;
                }
                Log::debug('Compulsory flags added to scores', [
                    'compulsory_count' => $compulsoryCount,
                    'total_scores' => $scores->count()
                ]);
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
                'is_senior' => $isSenior,
            ];

            Log::channel('pdf')->info('========== END getStudentResultData ==========', [
                'student_id' => $id,
                'result_keys' => array_keys($result),
                'has_students' => !empty($students) && $students->isNotEmpty(),
                'has_scores' => !empty($scores) && $scores->isNotEmpty(),
                'students_count' => $students->count() ?? 0,
                'scores_count' => $scores->count() ?? 0,
                'execution_time' => now()->toDateTimeString(),
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]);

            return $result;
        } catch (Exception $e) {
            Log::channel('pdf')->error('========== ERROR in getStudentResultData ==========', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return [];
        }
    }

    // NEW: Method to get column selection data
    public function getColumnOptions(Request $request)
    {
        Log::info('Getting column options', ['request' => $request->all()]);
        
        $schoolclassid = $request->input('schoolclassid');
        $sessionid = $request->input('sessionid');
        $termid = $request->input('termid');
        
        if (!$schoolclassid || !$sessionid || !$termid) {
            Log::error('Missing parameters for column options', $request->all());
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
                        'category_ids' => $categoryIds->toArray()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error loading assessments for column options', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
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

        Log::info('Column options prepared', [
            'total_columns' => count($columns['student_info']) + count($columns['assessments']) + 
                              count($columns['scores']) + count($columns['gpa_metrics']) + 
                              count($columns['other']),
            'assessment_count' => count($columns['assessments'])
        ]);

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
        Log::debug('Calculating grade preview', ['request' => $request->all()]);
        
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

        Log::debug('Grade preview result', [
            'schoolclass_id' => $request->schoolclass_id,
            'cum' => $request->cum,
            'grade' => $grade
        ]);

        return response()->json(['grade' => $grade]);
    }

    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        Log::info('Displaying student result view', [
            'student_id' => $id,
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid
        ]);
        
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
            Log::channel('pdf')->info('========== START SINGLE STUDENT PDF EXPORT ==========', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'timestamp' => now()->toDateTimeString(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]);

            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

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
                    'has_data' => !empty($data),
                    'has_students' => !empty($data['students']),
                    'students_count' => !empty($data['students']) ? $data['students']->count() : 0
                ]);
                return back()->with('error', 'No student data found for the provided parameters.');
            }

            $student = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession']->session . '_Term_' . $data['termid'] . '.pdf';

            Log::debug('Fixing image paths for single student PDF');
            $this->fixImagePaths([$data]);

            Log::info('Loading PDF view for single student', [
                'student_name' => $studentName,
                'filename' => $filename,
                'view' => 'studentreports.studentresult_pdf'
            ]);

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

            Log::info('PDF generated successfully for single student', [
                'student_id' => $id,
                'filename' => $filename,
                'file_size' => strlen($pdf->output()) . ' bytes'
            ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::channel('pdf')->error('========== ERROR SINGLE STUDENT PDF EXPORT ==========', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

   public function exportClassResultsPdf(Request $request)
{
    try {
        // Use local channel instead of pdf channel
        Log::info('========== START CLASS PDF EXPORT ==========', [
            'request_data' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
            'server_ip' => request()->server('SERVER_ADDR') ?? 'unknown',
            'client_ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ]);

        // Check server requirements first
        $this->checkServerRequirements();
        
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $schoolclassid = $request->input('schoolclassid');
        $sessionid = $request->input('sessionid');
        $termid = $request->input('termid', 3);
        $studentIds = $request->input('studentIds', []);
        $selectedColumns = $request->input('selectedColumns', []);

        Log::info('Starting class results PDF generation', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'studentIds_count' => count($studentIds),
            'selectedColumns_count' => count($selectedColumns),
            'selectedColumns_sample' => array_slice($selectedColumns, 0, 5)
        ]);

        // Validate inputs more strictly
        if (!$schoolclassid || !$sessionid || !$termid) {
            Log::error('Missing required parameters for PDF export', $request->all());
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters: schoolclassid, sessionid, termid'
            ], 400);
        }

        // Process each student
        $allStudentData = [];
        $processedCount = 0;
        $failedCount = 0;
        
        Log::info('Starting to process individual student data', [
            'total_students' => count($studentIds)
        ]);
        
        foreach ($studentIds as $index => $studentId) {
            Log::debug('Processing student', [
                'index' => $index + 1,
                'total' => count($studentIds),
                'student_id' => $studentId
            ]);
            
            $studentData = $this->getStudentResultData(
                $studentId, 
                $schoolclassid, 
                $sessionid, 
                $termid
            );
            
            // DEBUG: Log what we get from getStudentResultData
            Log::debug('Student data retrieved', [
                'student_id' => $studentId,
                'data_empty' => empty($studentData),
                'has_students' => !empty($studentData['students']),
                'students_count' => !empty($studentData['students']) ? $studentData['students']->count() : 0,
                'has_scores' => !empty($studentData['scores']),
                'scores_count' => !empty($studentData['scores']) ? $studentData['scores']->count() : 0,
                'data_keys' => !empty($studentData) ? array_keys($studentData) : []
            ]);
            
            if (!empty($studentData) && 
                !empty($studentData['students']) && 
                $studentData['students']->isNotEmpty()) {
                
                $studentData['selected_columns'] = $selectedColumns;
                $allStudentData[] = $studentData;
                $processedCount++;
                
                Log::debug('Student data added successfully', [
                    'student_id' => $studentId,
                    'student_name' => $studentData['students']->first()->fname . ' ' . $studentData['students']->first()->lastname
                ]);
            } else {
                $failedCount++;
                Log::warning('Skipped student due to empty data', [
                    'student_id' => $studentId,
                    'iteration' => $index + 1,
                    'data_empty' => empty($studentData),
                    'has_students' => !empty($studentData['students']),
                    'students_empty' => !empty($studentData['students']) && $studentData['students']->isEmpty()
                ]);
            }
        }

        Log::info('Student data processing completed', [
            'total_students' => count($studentIds),
            'processed_count' => $processedCount,
            'failed_count' => $failedCount,
            'final_data_count' => count($allStudentData)
        ]);

        if (empty($allStudentData)) {
            Log::error('All student data processing failed - no valid data collected', [
                'total_students' => count($studentIds),
                'processed_count' => $processedCount,
                'failed_count' => $failedCount
            ]);
            
            // Try a direct database query to debug
            $this->debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process student data. Check server logs for details.',
                'details' => 'No valid student data could be retrieved. Please verify student enrollment and scores.',
                'debug_info' => [
                    'student_ids' => $studentIds,
                    'class_id' => $schoolclassid,
                    'session_id' => $sessionid,
                    'term_id' => $termid
                ]
            ], 500);
        }

        // Fix image paths
        Log::info('Fixing image paths for all student data');
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
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
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

        // Try to generate PDF and return it inline
        Log::info('Generating PDF for inline display');
        
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
            ]);

        $pdfContent = $pdf->output();
        
        if (empty($pdfContent)) {
            Log::error('PDF content is empty');
            return response()->json([
                'success' => false,
                'message' => 'Generated PDF content is empty',
            ], 500);
        }

        Log::info('PDF generated successfully, returning inline response', [
            'size_bytes' => strlen($pdfContent),
            'filename' => $filename
        ]);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent));

    } catch (Exception $e) {
        Log::error('========== ERROR CLASS PDF EXPORT ==========', [
            'schoolclassid' => $schoolclassid ?? 'N/A',
            'sessionid' => $sessionid ?? 'N/A',
            'termid' => $termid ?? 'N/A',
            'studentIds_count' => count($studentIds ?? []),
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'error_trace' => $e->getTraceAsString(),
            'timestamp' => now()->toDateTimeString(),
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            'error_type' => get_class($e)
        ], 500);
    } finally {
        Log::info('========== END CLASS PDF EXPORT ==========', [
            'timestamp' => now()->toDateTimeString(),
            'execution_time' => round(microtime(true) - LARAVEL_START, 2) . ' seconds',
            'final_memory' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
        ]);
    }
}
    private function checkServerRequirements()
    {
        Log::info('Checking server requirements for PDF generation');
        
        $checks = [
            'storage_writable' => is_writable(storage_path()),
            'temp_dir_writable' => is_writable(storage_path('app/temp')),
            'dompdf_installed' => class_exists('Barryvdh\DomPDF\PDF'),
            'php_memory_limit' => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'public_storage_exists' => file_exists(public_path('storage')),
            'student_avatars_exists' => file_exists(public_path('storage/student_avatars')),
            'school_logos_exists' => file_exists(public_path('storage/school_logos')),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        ];

        Log::info('Server requirements check', $checks);

        // Create missing directories
        if (!$checks['temp_dir_writable']) {
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
                Log::info('Created temp directory', ['path' => $tempDir]);
            }
        }

        // Check for default images
        $defaultStudentImage = public_path('storage/student_avatars/unnamed.jpg');
        $defaultSchoolLogo = public_path('storage/school_logos/default.jpg');
        
        if (!file_exists($defaultStudentImage)) {
            Log::warning('Default student image not found', ['path' => $defaultStudentImage]);
            // Try to create directory
            $studentAvatarDir = dirname($defaultStudentImage);
            if (!file_exists($studentAvatarDir)) {
                mkdir($studentAvatarDir, 0755, true);
                Log::info('Created student avatars directory', ['path' => $studentAvatarDir]);
            }
        }
        
        if (!file_exists($defaultSchoolLogo)) {
            Log::warning('Default school logo not found', ['path' => $defaultSchoolLogo]);
            // Try to create directory
            $schoolLogoDir = dirname($defaultSchoolLogo);
            if (!file_exists($schoolLogoDir)) {
                mkdir($schoolLogoDir, 0755, true);
                Log::info('Created school logos directory', ['path' => $schoolLogoDir]);
            }
        }

        return $checks;
    }

    private function getAbsoluteImagePath($path)
    {
        Log::debug('Getting absolute image path', ['original_path' => $path]);

        if (empty($path)) {
            $defaultPath = public_path('storage/student_avatars/unnamed.jpg');
            Log::debug('Empty path provided, returning default', ['default_path' => $defaultPath]);
            return $defaultPath;
        }

        // If it's already a full path, use it
        if (str_starts_with($path, public_path())) {
            $exists = file_exists($path);
            Log::debug('Path is already absolute', [
                'path' => $path,
                'exists' => $exists
            ]);
            return $exists ? $path : public_path('storage/student_avatars/unnamed.jpg');
        }

        // Check if it's a base64 image
        if (str_starts_with($path, 'data:image')) {
            Log::debug('Path is base64 image data');
            return null; // Base64 image, handled separately
        }

        // Clean up the path
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = preg_replace('/^(http:\/\/|https:\/\/|\/\/)[^\/]+/', '', $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        
        // Check multiple possible locations
        $possiblePaths = [
            public_path($path),
            storage_path('app/public/' . basename($path)),
            public_path('storage/' . $path),
            public_path('school_logos/' . basename($path)),
            public_path('student_avatars/' . basename($path)),
            storage_path('app/public/school_logos/' . basename($path)),
            storage_path('app/public/student_avatars/' . basename($path)),
        ];

        Log::debug('Checking possible paths for image', [
            'original' => $path,
            'possible_paths' => $possiblePaths
        ]);

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath)) {
                Log::debug('Image found at path', [
                    'path' => $fullPath,
                    'file_size' => filesize($fullPath) . ' bytes'
                ]);
                return $fullPath;
            }
        }

        // Return default if not found
        if (str_contains(strtolower($path), 'student') || str_contains(strtolower($path), 'avatar')) {
            $defaultPath = public_path('storage/student_avatars/unnamed.jpg');
            Log::warning('Student image not found, using default', [
                'original' => $path,
                'default' => $defaultPath,
                'exists' => file_exists($defaultPath)
            ]);
            return $defaultPath;
        }
        
        $defaultLogoPath = public_path('storage/school_logos/default.jpg');
        Log::warning('School logo not found, using default', [
            'original' => $path,
            'default' => $defaultLogoPath,
            'exists' => file_exists($defaultLogoPath)
        ]);
        return $defaultLogoPath;
    }

    /**
     * Convert image to base64 data URI
     */
    private function imageToBase64($imagePath)
    {
        if (!$imagePath || !file_exists($imagePath)) {
            Log::warning('Image file does not exist for base64 conversion', ['path' => $imagePath]);
            
            // Try to return a default base64 placeholder
            $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">' .
                '<rect width="100" height="100" fill="#f0f0f0"/>' .
                '<text x="50" y="50" text-anchor="middle" dy=".3em" fill="#999" font-size="12">No Image</text>' .
                '</svg>'
            );
            return $placeholder;
        }
        
        try {
            $imageData = file_get_contents($imagePath);
            $mimeType = mime_content_type($imagePath);
            
            if (!$mimeType) {
                $mimeType = 'image/jpeg'; // Default to JPEG if mime type detection fails
            }
            
            $base64 = base64_encode($imageData);
            $result = "data:{$mimeType};base64,{$base64}";
            
            Log::debug('Image converted to base64', [
                'path' => $imagePath,
                'mime_type' => $mimeType,
                'data_length' => strlen($result),
                'file_size' => filesize($imagePath) . ' bytes'
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to convert image to base64', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a simple placeholder
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">' .
                '<rect width="100" height="100" fill="#f0f0f0"/>' .
                '<text x="50" y="50" text-anchor="middle" dy=".3em" fill="#999" font-size="12">Error</text>' .
                '</svg>'
            );
        }
    }

    private function fixImagePaths(&$studentData)
    {
        Log::info('Fixing image paths for student data', ['student_count' => count($studentData)]);
        
        $defaultStudentImage = public_path('storage/student_avatars/unnamed.jpg');
        $defaultSchoolLogo = public_path('storage/school_logos/default.jpg');
        
        // Ensure defaults exist
        if (!file_exists($defaultStudentImage)) {
            Log::warning('Default student image not found', ['path' => $defaultStudentImage]);
            // Create directory if it doesn't exist
            $studentDir = dirname($defaultStudentImage);
            if (!file_exists($studentDir)) {
                mkdir($studentDir, 0755, true);
                Log::info('Created student avatars directory', ['path' => $studentDir]);
            }
        }
        
        if (!file_exists($defaultSchoolLogo)) {
            Log::warning('Default school logo not found', ['path' => $defaultSchoolLogo]);
            // Create directory if it doesn't exist
            $logoDir = dirname($defaultSchoolLogo);
            if (!file_exists($logoDir)) {
                mkdir($logoDir, 0755, true);
                Log::info('Created school logos directory', ['path' => $logoDir]);
            }
        }
        
        foreach ($studentData as $index => &$student) {
            Log::debug("Processing student {$index} image paths");
            
            // Student image handling
            if (isset($student['students']) && $student['students']->isNotEmpty() && $student['students']->first()->picture) {
                $imagePath = $student['students']->first()->picture;
                $absolutePath = $this->getAbsoluteImagePath($imagePath);
                
                if ($absolutePath && file_exists($absolutePath)) {
                    $student['student_image_base64'] = $this->imageToBase64($absolutePath);
                    Log::debug('Student image found and converted', [
                        'student_index' => $index,
                        'original_path' => $imagePath,
                        'absolute_path' => $absolutePath,
                        'exists' => file_exists($absolutePath),
                        'has_base64' => !empty($student['student_image_base64'])
                    ]);
                } else {
                    $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
                    Log::warning('Using default student image', [
                        'student_index' => $index,
                        'original_path' => $imagePath,
                        'absolute_path' => $absolutePath,
                        'default_path' => $defaultStudentImage
                    ]);
                }
            } else {
                $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
                Log::info('No student picture, using default', [
                    'student_index' => $index,
                    'has_students' => isset($student['students']),
                    'students_not_empty' => isset($student['students']) && $student['students']->isNotEmpty(),
                    'has_picture' => isset($student['students']) && $student['students']->isNotEmpty() && !empty($student['students']->first()->picture)
                ]);
            }
            
            // School logo handling
            if (isset($student['schoolInfo']) && $student['schoolInfo']->logo) {
                $logoPath = $student['schoolInfo']->logo;
                $absolutePath = $this->getAbsoluteImagePath($logoPath);
                
                if ($absolutePath && file_exists($absolutePath)) {
                    $student['school_logo_base64'] = $this->imageToBase64($absolutePath);
                    Log::debug('School logo found and converted', [
                        'student_index' => $index,
                        'original_path' => $logoPath,
                        'absolute_path' => $absolutePath,
                        'exists' => file_exists($absolutePath)
                    ]);
                } else {
                    $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
                    Log::warning('Using default school logo', [
                        'student_index' => $index,
                        'original_path' => $logoPath,
                        'absolute_path' => $absolutePath,
                        'default_path' => $defaultSchoolLogo
                    ]);
                }
            } else {
                $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
                Log::info('No school logo, using default', [
                    'student_index' => $index,
                    'has_school_info' => isset($student['schoolInfo']),
                    'has_logo' => isset($student['schoolInfo']) && !empty($student['schoolInfo']->logo)
                ]);
            }
        }
        
        Log::info('Image path fixing completed', ['students_processed' => count($studentData)]);
    }

    private function ensureDirectoriesExist()
    {
        Log::info('Ensuring required directories exist');
        
        $directories = [
            storage_path('app/temp'),
            storage_path('fonts'),
            storage_path('logs'),
            public_path('temp_pdfs'),
            storage_path('logs/pdf'), // Separate log directory for PDF logs
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                $result = mkdir($dir, 0755, true);
                Log::info('Created directory', [
                    'path' => $dir,
                    'success' => $result,
                    'permissions' => substr(sprintf('%o', fileperms($dir)), -4)
                ]);
            } else {
                Log::debug('Directory already exists', [
                    'path' => $dir,
                    'writable' => is_writable($dir),
                    'permissions' => substr(sprintf('%o', fileperms($dir)), -4)
                ]);
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
        
        $termName = $terms[$termid] ?? 'Unknown Term';
        Log::debug('Getting term name', ['term_id' => $termid, 'term_name' => $termName]);
        
        return $termName;
    }

    private function validateStudentData($studentData): bool
    {
        Log::debug('Validating student data', [
            'has_data' => !empty($studentData),
            'data_type' => gettype($studentData),
            'data_keys' => !empty($studentData) ? array_keys($studentData) : []
        ]);

        if (empty($studentData)) {
            Log::error('Student data is completely empty');
            return false;
        }

        $requiredKeys = ['students', 'scores', 'schoolclass', 'schoolsession', 'schoolterm'];
        
        foreach ($requiredKeys as $key) {
            if (!isset($studentData[$key])) {
                Log::error("Missing required key in student data: $key", [
                    'available_keys' => array_keys($studentData),
                    'student_id' => $studentData['studentid'] ?? 'unknown',
                    'missing_key' => $key
                ]);
                return false;
            }
        }

        if (!$studentData['students'] || $studentData['students']->isEmpty()) {
            Log::error('Students collection is empty', [
                'student_id' => $studentData['studentid'] ?? 'unknown',
                'students_type' => gettype($studentData['students']),
                'students_is_collection' => $studentData['students'] instanceof \Illuminate\Support\Collection,
                'students_count' => $studentData['students'] ? $studentData['students']->count() : 0,
            ]);
            return false;
        }

        if (!$studentData['scores'] || $studentData['scores']->isEmpty()) {
            Log::warning('Scores collection is empty or invalid', [
                'student_id' => $studentData['studentid'] ?? 'unknown',
                'schoolclassid' => $studentData['schoolclassid'] ?? 'unknown',
                'sessionid' => $studentData['sessionid'] ?? 'unknown',
                'termid' => $studentData['termid'] ?? 'unknown',
                'scores_type' => gettype($studentData['scores']),
                'scores_is_collection' => $studentData['scores'] instanceof \Illuminate\Support\Collection,
                'scores_count' => $studentData['scores'] ? $studentData['scores']->count() : 0
            ]);
            // Don't return false - some students might legitimately have no scores
        }

        $student = $studentData['students']->first();
        
        Log::info('Student data validation passed', [
            'student_id' => $student->id ?? 'unknown',
            'student_name' => ($student->fname ?? '') . ' ' . ($student->lastname ?? ''),
            'scores_count' => $studentData['scores'] ? $studentData['scores']->count() : 0,
            'has_gpa_data' => isset($studentData['gpa_data']),
            'gpa' => $studentData['gpa_data']['gpa'] ?? 'N/A',
            'cgpa' => $studentData['gpa_data']['cgpa'] ?? 'N/A',
            'has_school_info' => isset($studentData['schoolInfo']),
            'has_assessments' => isset($studentData['assessments']),
            'assessment_count' => $studentData['assessments'] ? $studentData['assessments']->count() : 0,
            'has_compulsory_subjects' => isset($studentData['compulsorySubjects']),
            'compulsory_count' => isset($studentData['compulsorySubjects']) ? count($studentData['compulsorySubjects']) : 0,
        ]);
        
        return true;
    }

    // ============= RESPONSE METHODS =============
    
    private function base64Response($pdfContent, $filename)
    {
        Log::info('Returning base64 response', [
            'filename' => $filename,
            'pdf_size_bytes' => strlen($pdfContent),
            'base64_size_bytes' => strlen(base64_encode($pdfContent))
        ]);
        
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
        Log::info('Saving PDF and returning redirect response', ['filename' => $filename]);
        
        $tempPath = storage_path('app/temp/' . $filename);
        file_put_contents($tempPath, $pdfContent);
        
        $publicPath = 'temp_pdfs/' . $filename;
        $publicFullPath = public_path($publicPath);
        
        if (!file_exists(public_path('temp_pdfs'))) {
            mkdir(public_path('temp_pdfs'), 0755, true);
            Log::info('Created public temp PDFs directory');
        }
        
        file_put_contents($publicFullPath, $pdfContent);
        
        Log::info('PDF saved to public directory', [
            'public_path' => $publicPath,
            'full_path' => $publicFullPath,
            'file_size' => filesize($publicFullPath) . ' bytes'
        ]);
        
        return response()->json([
            'success' => true,
            'url' => url($publicPath),
            'filename' => $filename,
            'message' => 'PDF saved successfully',
        ]);
    }

    private function downloadResponse($pdfContent, $filename)
    {
        Log::info('Returning download response', [
            'filename' => $filename,
            'content_length' => strlen($pdfContent),
            'content_type' => 'application/pdf'
        ]);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent));
    }

    private function inlineResponse($pdfContent, $filename)
    {
        Log::info('Returning inline response', [
            'filename' => $filename,
            'content_length' => strlen($pdfContent)
        ]);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent));
    }

    private function chunkedResponse($pdfContent, $filename)
    {
        Log::info('Returning chunked response', [
            'filename' => $filename,
            'content_length' => strlen($pdfContent)
        ]);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent))
            ->header('Transfer-Encoding', 'chunked');
    }
    // ============= END RESPONSE METHODS =============

    public function index(Request $request): View|JsonResponse 
    {
        Log::info('ViewStudentReportController index method called', [
            'request_params' => $request->all(),
            'ajax' => $request->ajax(),
            'method' => $request->method()
        ]);
        
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
                'sessionid' => $request->input('sessionid'),
                'student_count' => $allstudents->total(),
                'page' => $allstudents->currentPage(),
                'per_page' => $allstudents->perPage()
            ]);
        } else {
            Log::info('No class/session selected or ALL selected', [
                'schoolclassid' => $request->input('schoolclassid'),
                'sessionid' => $request->input('sessionid')
            ]);
        }

        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        Log::debug('Session and class data loaded', [
            'sessions_count' => $schoolsessions->count(),
            'classes_count' => $schoolclasses->count(),
            'sample_classes' => $schoolclasses->take(3)->map(function($c) {
                return $c->schoolclass . ' ' . $c->arm;
            })->toArray()
        ]);

        if ($request->ajax()) {
            Log::info('AJAX request for student data', [
                'student_count' => $allstudents->total(),
                'page' => $allstudents->currentPage()
            ]);
            return response()->json([
                'tableBody' => view('studentreports.partials.student_rows', compact('allstudents'))->render(),
                'pagination' => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        Log::info('Returning full view for student reports');
        return view('studentreports.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }

    public function registeredClasses(Request $request)
    {
        Log::info('Getting registered classes', ['request' => $request->all()]);
        
        $classId = $request->query('class_id');
        $sessionId = $request->query('session_id');

        if (!$classId || !$sessionId || $classId === 'ALL' || $sessionId === 'ALL') {
            Log::warning('Invalid parameters for registered classes', [
                'class_id' => $classId,
                'session_id' => $sessionId
            ]);
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

        Log::info('Registered classes retrieved', [
            'class_id' => $classId,
            'session_id' => $sessionId,
            'classes_count' => $classes->count(),
            'student_counts' => $classes->pluck('student_count')->toArray()
        ]);

        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }

    public function classBroadsheet($schoolclassid, $sessionid, $termid): View
    {
        Log::info('Displaying class broadsheet', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid
        ]);
        
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
        Log::info('Displaying student mock result', [
            'student_id' => $id,
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid
        ]);
        
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

    // Test endpoint for debugging
    public function testPdfGeneration(Request $request)
    {
        Log::info('Test PDF generation endpoint called');
        
        try {
            // Test with a known student ID
            $testStudentId = Student::first()->id ?? null;
            $testClassId = Schoolclass::first()->id ?? null;
            $testSessionId = Schoolsession::first()->id ?? null;
            
            Log::debug('Test parameters', [
                'test_student_id' => $testStudentId,
                'test_class_id' => $testClassId,
                'test_session_id' => $testSessionId
            ]);
            
            if (!$testStudentId || !$testClassId || !$testSessionId) {
                Log::error('Test data not available');
                return response()->json([
                    'success' => false,
                    'message' => 'Test data not available in database'
                ]);
            }
            
            // Test getStudentResultData
            $studentData = $this->getStudentResultData(
                $testStudentId, 
                $testClassId, 
                $testSessionId, 
                3 // Third term
            );
            
            $result = [
                'success' => !empty($studentData),
                'student_data_keys' => array_keys($studentData),
                'has_students' => isset($studentData['students']) && !$studentData['students']->isEmpty(),
                'has_scores' => isset($studentData['scores']) && !$studentData['scores']->isEmpty(),
                'student_count' => $studentData['students']->count() ?? 0,
                'scores_count' => $studentData['scores']->count() ?? 0,
                'server_info' => [
                    'storage_writable' => is_writable(storage_path()),
                    'php_version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'laravel_version' => app()->version(),
                ],
                'paths' => [
                    'public_path' => public_path(),
                    'storage_path' => storage_path(),
                    'default_student_image' => public_path('storage/student_avatars/unnamed.jpg'),
                    'default_school_logo' => public_path('storage/school_logos/default.jpg'),
                ],
                'file_exists' => [
                    'default_student_image' => file_exists(public_path('storage/student_avatars/unnamed.jpg')),
                    'default_school_logo' => file_exists(public_path('storage/school_logos/default.jpg')),
                ]
            ];
            
            Log::info('Test completed', $result);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Test PDF generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    private function debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid)
{
    Log::info('DEBUG: Running direct database queries to check data');
    
    // Check if students exist
    $studentsExist = Student::whereIn('id', $studentIds)->count();
    Log::info('DEBUG: Students exist check', [
        'student_ids' => $studentIds,
        'students_found' => $studentsExist,
        'expected' => count($studentIds)
    ]);
    
    // Check if students are in the class
    $studentsInClass = Studentclass::whereIn('studentId', $studentIds)
        ->where('schoolclassid', $schoolclassid)
        ->where('sessionid', $sessionid)
        ->count();
    
    Log::info('DEBUG: Students in class check', [
        'students_in_class' => $studentsInClass,
        'expected' => count($studentIds)
    ]);
    
    // Check if broadsheet records exist
    $broadsheetRecords = DB::table('broadsheet_records')
        ->whereIn('student_id', $studentIds)
        ->where('schoolclass_id', $schoolclassid)
        ->where('session_id', $sessionid)
        ->count();
    
    Log::info('DEBUG: Broadsheet records check', [
        'broadsheet_records' => $broadsheetRecords
    ]);
    
    // Check if broadsheets exist for the term
    $broadsheets = DB::table('broadsheets')
        ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
        ->whereIn('broadsheet_records.student_id', $studentIds)
        ->where('broadsheet_records.schoolclass_id', $schoolclassid)
        ->where('broadsheet_records.session_id', $sessionid)
        ->where('broadsheets.term_id', $termid)
        ->count();
    
    Log::info('DEBUG: Broadsheets check', [
        'broadsheets_found' => $broadsheets
    ]);
    
    // Sample some data
    if ($broadsheets > 0) {
        $sampleData = DB::table('broadsheets')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
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
        
        Log::info('DEBUG: Sample broadsheet data', [
            'sample_data' => $sampleData->toArray()
        ]);
    }
}
}