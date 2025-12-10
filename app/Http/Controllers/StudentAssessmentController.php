<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Assessment;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SubAssessment;
use App\Models\BroadsheetRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class StudentAssessmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student assessments', ['only' => ['index']]);
    }

    public function index(Request $request)
    {
        $pagetitle = 'My Assessments';
        $studentId = auth()->user()->student_id;

        if (!$studentId) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        // Check if student is allowed to view assessments
        $student = Student::where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'admissionNo', 'can_view_assessments')
            ->first();

        if (!$student || !$student->can_view_assessments) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view assessments.');
        }

        // Get available terms and sessions
        $terms = Schoolterm::orderBy('id', 'desc')->get(['id', 'term']);
        $sessions = Schoolsession::where('status', 'Current')->orWhere('status', 'Previous')->orderBy('id', 'desc')->get(['id', 'session']);

        $userSelectedTermId = $request->get('term_id');
        $selectedSessionId = $request->get('session_id', $sessions->first()?->id ?? null);

        // Determine selectedTermId: use user selection, or default to latest for session if "All Terms"
        $selectedTermId = $userSelectedTermId ?: null;
        $isAllTerms = empty($userSelectedTermId);

        if ($isAllTerms && $selectedSessionId) {
            // For "All Terms", use the latest term for class and broadsheet queries
            $latestTermId = DB::table('studentclass')
                ->where('studentId', $studentId)
                ->where('sessionid', $selectedSessionId)
                ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->orderBy('schoolterm.id', 'desc')
                ->value('schoolterm.id');

            if ($latestTermId) {
                $selectedTermId = $latestTermId;
            }
        }

        // Initialize these as null to avoid undefined variable errors
        $class = null;
        $term = null;
        $session = null;

        // Get student details (search by session only, ignore term for class display)
        $studentClassData = DB::table('studentclass')
            ->where('studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->when($selectedSessionId, function ($query) use ($selectedSessionId) {
                return $query->where('schoolsession.id', $selectedSessionId);
            })
            ->select(
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            )
            ->first();

        if (!$studentClassData) {
            return view('student.assessments.index', compact('pagetitle', 'student', 'terms', 'sessions', 'userSelectedTermId', 'selectedSessionId'))
                ->with('error', 'No class registration found for the selected term and session.');
        }

        // Define class, term, session only if data exists (as before)
        $class = (object) ['id' => $studentClassData->class_id, 'schoolclass' => $studentClassData->class_name];
        $term = (object) ['id' => $studentClassData->term_id, 'term' => $studentClassData->term_name];
        $session = (object) ['id' => $studentClassData->session_id, 'session' => $studentClassData->session_name];

        // Get schoolclass for category IDs and senior status
        $schoolclass = Schoolclass::with('classcategories')->find($studentClassData->class_id);
        if (!$schoolclass || $schoolclass->classcategories->isEmpty()) {
            return view('student.assessments.index', compact('pagetitle', 'student', 'class', 'term', 'session', 'terms', 'sessions', 'userSelectedTermId', 'selectedSessionId'))
                ->with('error', 'Class category not found.');
        }
        $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
        $categoryIds = $schoolclass->classcategories->pluck('id');

        // Get registered subjects for selected term/session
        // Filter by session always; filter by term only if specific term selected (not "All Terms")
        $registeredSubjects = DB::table('student_subject_register_record as ssrr')
            ->where('ssrr.studentId', $studentId)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'ssrr.subjectclassid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'ssrr.session')
            ->when($selectedSessionId, function ($query) use ($selectedSessionId) {
                return $query->where('schoolsession.id', $selectedSessionId);
            })
            ->when(!$isAllTerms && $selectedTermId, function ($query) use ($selectedTermId) {
                // Only filter by term if specific term is selected (not "All Terms")
                return $query->where('subjectteacher.termid', $selectedTermId);
            })
            ->where('schoolsession.status', '!=', 'Archived') // Exclude archived
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->select(
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'subjectclass.id as subjectclass_id'
            )
            ->distinct() // Avoid duplicates if any
            ->get();

        $subjectsWithAssessments = collect();
        $overallProgress = [
            'total_subjects' => 0,
            'completed_subjects' => 0,
            'total_score' => 0,
            'average_cum' => 0,
            'gpa' => '-',
            'cgpa' => '-',
            'gpa_grade' => '-',
            'num_subjects' => 0,
            'total_grade_points' => 0.0,
            'calculated_gpa' => 0.0
        ];

        foreach ($registeredSubjects as $regSubject) {
            // Get assessments for the class
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')
                ->get();

            if ($assessments->isEmpty()) {
                continue;
            }

            // Get broadsheet record
            $broadsheetRecord = BroadsheetRecord::where('student_id', $studentId)
                ->where('subject_id', $regSubject->subject_id)
                ->where('schoolclass_id', $studentClassData->class_id)
                ->where('session_id', $selectedSessionId ?? $studentClassData->session_id)
                ->first();

            if (!$broadsheetRecord) {
                continue;
            }

            // Get broadsheet for the selected term (for all terms, uses latest term)
            $broadsheet = Broadsheets::where('broadSheet_record_id', $broadsheetRecord->id)
                ->where('term_id', $selectedTermId)
                ->first();

            if (!$broadsheet) {
                continue;
            }

            // Load scores
            $broadsheet->load(['assessmentScores', 'subAssessmentScores']);

            // Prepare assessment data
            $assessmentData = $assessments->map(function ($assessment) use ($broadsheet) {
                $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                $score = $scoreObj ? $scoreObj->score : 0;

                $subScores = collect();
                if ($assessment->subAssessments->isNotEmpty()) {
                    $subScores = $assessment->subAssessments->map(function ($sub) use ($broadsheet) {
                        $subScoreObj = $broadsheet->subAssessmentScores->where('sub_assessment_id', $sub->id)->first();
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                            'max_score' => $sub->max_score,
                            'score' => $subScoreObj ? $subScoreObj->score : 0,
                            'percentage' => $sub->max_score > 0 ? round(($subScoreObj ? $subScoreObj->score : 0) / $sub->max_score * 100, 2) : 0
                        ];
                    });
                }

                return [
                    'id' => $assessment->id,
                    'name' => $assessment->name,
                    'max_score' => $assessment->max_score,
                    'score' => $score,
                    'percentage' => $assessment->max_score > 0 ? round($score / $assessment->max_score * 100, 2) : 0,
                    'sub_assessments' => $subScores
                ];
            });

            $subjectGPA = $this->getGradePoint($broadsheet->cum ?? 0, $isSenior);

            $subjectsWithAssessments->push([
                'subject_id' => $regSubject->subject_id,
                'subject_name' => $regSubject->subject_name,
                'subject_code' => $regSubject->subject_code,
                'assessments' => $assessmentData,
                'total' => $broadsheet->total ?? 0,
                'bf' => $broadsheet->bf ?? 0,
                'cum' => $broadsheet->cum ?? 0,
                'grade' => $broadsheet->grade ?? '-',
                'subject_gpa' => round($subjectGPA, 1),
                'remark' => $broadsheet->remark ?? '-',
                'position' => $broadsheet->position ? $broadsheet->position . getOrdinalSuffix($broadsheet->position) : '-'
            ]);

            // Update progress stats
            $overallProgress['total_subjects']++;
            if ($broadsheet->cum > 0) {
                $overallProgress['completed_subjects']++;
                $overallProgress['total_score'] += $broadsheet->cum;
            }
        }

        // Calculate average
        if ($overallProgress['completed_subjects'] > 0) {
            $overallProgress['average_cum'] = round($overallProgress['total_score'] / $overallProgress['completed_subjects'], 2);
        }

        // Compute overall GPA and CGPA
        if ($subjectsWithAssessments->isNotEmpty() && $schoolclass) {
            $gpaCgpaData = $this->computeOverallForStudent($studentId, $schoolclass, $selectedTermId, $selectedSessionId ?? $studentClassData->session_id, $isSenior);
            $overallProgress['gpa'] = round($gpaCgpaData['gpa'], 2);
            $overallProgress['cgpa'] = round($gpaCgpaData['cgpa'], 2);
            $overallProgress['gpa_grade'] = $gpaCgpaData['gpa_grade'] ?? 'F';
            $overallProgress['num_subjects'] = $gpaCgpaData['num_subjects'];
            $overallProgress['total_grade_points'] = $gpaCgpaData['total_grade_points'];
            $overallProgress['calculated_gpa'] = $gpaCgpaData['num_subjects'] > 0 ? round($gpaCgpaData['total_grade_points'] / $gpaCgpaData['num_subjects'], 2) : 0;
        }

        return view('student.assessments.index', compact(
            'pagetitle',
            'student',
            'class',
            'term',
            'session',
            'subjectsWithAssessments',
            'terms',
            'sessions',
            'userSelectedTermId',
            'selectedSessionId',
            'overallProgress'
        ));
    }

    /**
     * Get grade point from score based on the class category scale.
     */
    private function getGradePoint($score, $isSenior = false)
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

    /**
     * Compute overall GPA and CGPA for a single student.
     *
     * @param int $studentId
     * @param \App\Models\Schoolclass $schoolclass
     * @param int $termId
     * @param int $sessionId
     * @param bool $isSenior
     * @return array ['gpa' => float, 'cgpa' => float, 'gpa_grade' => string, 'num_subjects' => int, 'total_grade_points' => float]
     */
    private function computeOverallForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior)
    {
        // Current Term GPA and Grade (across all subjects) using total scores
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
            return $this->getGradePoint($b->total, $isSenior); // Use total, not cum
        });
        $gpa = $termGradePoints->avg() ?? 0.0;
        $num_subjects = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();
        // CGPA: Average of up to 3 most recent sessions' annual GPAs within the class category
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
                    ->get(['total']); // Use total for past terms too
                $termGradePointsPast = $termBroadsheets->map(function ($b) use ($isSenior) {
                    return $this->getGradePoint($b->total, $isSenior); // Use total
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
        ];
    }

    /**
     * Fallback grading logic when class category is not available
     */
    protected function getDefaultGrade($score)
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
} 