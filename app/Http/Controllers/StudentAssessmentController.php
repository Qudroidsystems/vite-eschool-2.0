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

    $selectedTermId = $request->get('term_id', $terms->first()?->id ?? null);
    $selectedSessionId = $request->get('session_id', $sessions->first()?->id ?? null);

    // Initialize these as null to avoid undefined variable errors
    $class = null;
    $term = null;
    $session = null;

    // Get student details
    $studentClassData = DB::table('studentclass')
        ->where('studentId', $studentId)
        ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
        ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
        ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
        ->when($selectedTermId, function ($query) use ($selectedTermId) {
            return $query->where('schoolterm.id', $selectedTermId);
        })
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
        // Removed 'class', 'term', 'session' from compact since they're null
        return view('student.assessments.index', compact('pagetitle', 'student', 'terms', 'sessions', 'selectedTermId', 'selectedSessionId'))
            ->with('error', 'No class registration found for the selected term and session.');
    }

    // Define class, term, session only if data exists (as before)
    $class = (object) ['id' => $studentClassData->class_id, 'schoolclass' => $studentClassData->class_name];
    $term = (object) ['id' => $studentClassData->term_id, 'term' => $studentClassData->term_name];
    $session = (object) ['id' => $studentClassData->session_id, 'session' => $studentClassData->session_name];

    // Get registered subjects for selected term/session
    $registeredSubjects = DB::table('student_subject_register_record')
        ->where('student_subject_register_record.studentId', $studentId)
        ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
        ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
        ->when($selectedSessionId, function ($query) use ($selectedSessionId) {
            return $query->where('schoolsession.id', $selectedSessionId);
        })
        ->where('schoolsession.status', '!=', 'Archived') // Exclude archived
        ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
        ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
        ->select(
            'subject.id as subject_id',
            'subject.subject as subject_name',
            'subject.subject_code',
            'subjectclass.id as subjectclass_id'
        )
        ->get();

    $subjectsWithAssessments = collect();
    $overallProgress = [
        'total_subjects' => 0,
        'completed_subjects' => 0,
        'total_score' => 0,
        'average_cum' => 0,
        'gpa' => '-'
    ];

    foreach ($registeredSubjects as $regSubject) {
        // Get schoolclass for category IDs
        $schoolclass = Schoolclass::with('classcategories')->find($studentClassData->class_id);
        if (!$schoolclass || $schoolclass->classcategories->isEmpty()) {
            continue;
        }

        $categoryIds = $schoolclass->classcategories->pluck('id');

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

        // Get broadsheet for the selected term
        $broadsheet = Broadsheets::where('broadSheet_record_id', $broadsheetRecord->id)
            ->where('term_id', $selectedTermId ?? $studentClassData->term_id)
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

        $subjectsWithAssessments->push([
            'subject_id' => $regSubject->subject_id,
            'subject_name' => $regSubject->subject_name,
            'subject_code' => $regSubject->subject_code,
            'assessments' => $assessmentData,
            'total' => $broadsheet->total ?? 0,
            'bf' => $broadsheet->bf ?? 0,
            'cum' => $broadsheet->cum ?? 0,
            'grade' => $broadsheet->grade ?? '-',
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

    // Calculate average and GPA
    if ($overallProgress['completed_subjects'] > 0) {
        $overallProgress['average_cum'] = round($overallProgress['total_score'] / $overallProgress['completed_subjects'], 2);
        // Simple GPA calculation (A=4.0, B=3.0, etc.)
        $gpaMap = ['A' => 4.0, 'B' => 3.0, 'C' => 2.0, 'D' => 1.0, 'F' => 0.0];
        $totalGPA = 0;
        foreach ($subjectsWithAssessments as $subject) {
            if (isset($gpaMap[$subject['grade']])) {
                $totalGPA += $gpaMap[$subject['grade']];
            }
        }
        $overallProgress['gpa'] = $subjectsWithAssessments->count() > 0 ? round($totalGPA / $subjectsWithAssessments->count(), 2) : 0;
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
        'selectedTermId',
        'selectedSessionId',
        'overallProgress'
    ));
}
}