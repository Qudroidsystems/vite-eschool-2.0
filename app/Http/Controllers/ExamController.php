<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Schoolterm;
use App\Models\ExamAttempt;
use App\Models\Schoolclass;
use App\Models\ClassTeacher;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SubjectTeacher;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolInformation;
use App\Models\Staffclasssetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View exam', ['only' => ['index', 'show', 'edit', 'showStudents', 'showStudentAnswers', 'analytics']]);
        $this->middleware('permission:Create exam', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update exam', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete exam', ['only' => ['destroy', 'bulkDestroy', 'deleteStudentAttempt']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $current = "Current";

        $pagetitle = 'Exams Management'; // Define the page title

        $terms = Schoolterm::all();
        $session = Schoolsession::all();

        $query = Exam::query()->with(['schoolclasses' => function ($q) {
            $q->select('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name')
              ->join('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm');
        }]);

        // Filter exams by the logged-in user's staff ID
        $query->where('staffId', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->direction === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $exams = $query->paginate(15);

        $mysubjects = SubjectTeacher::where('staffid', $user->id)
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current)
            ->get([
                'subjectteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'subjectteacher.termid as termid',
                'subjectteacher.sessionid as sessionid',
                'schoolterm.term as term',
                'schoolsession.session as session'
            ])->sortBy('subject')->unique('id'); // Added unique to avoid duplicate subjectteacher IDs

        $myclass = Schoolclass::leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(
                'schoolclass.id as schoolclassID',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name',
                'schoolclass.arm as arm_id',
                'classcategories.category as classcategory',
                'classcategories.id as classcategoryid',
                'schoolclass.updated_at'
            )->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($exams);
        }

        return view('exam.index', compact('pagetitle', 'exams', 'terms', 'session', 'mysubjects', 'myclass'));
    }

    /**
     * Fetch classes for a given subject teacher ID.
     */
    public function getClassesForSubject($subjectTeacherId)
    {
        $classes = DB::table('subjectclass')
            ->where('subjectclass.subjectteacherid', $subjectTeacherId)
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(
                'schoolclass.id as schoolclassID',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name'
            )
            ->get();

        return response()->json($classes);
    }

    /**
     * Show students who attempted the specified exam (completed or in_progress).
     */
    public function showStudents(Request $request, $examId)
    {
        $exam = Exam::where('id', $examId)
                    ->where('staffId', auth()->user()->id)
                    ->with('schoolclasses')
                    ->firstOrFail();

        $classId = $request->query('class_id');

        $query = DB::table('exam_attempts')
            ->join('studentRegistration', 'exam_attempts.student_id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
            ->leftJoin('results', function ($join) use ($examId) {
                $join->on('exam_attempts.student_id', '=', 'results.user_id')
                     ->where('results.exam_id', '=', $examId);
            })
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('exam_attempts.status', ['completed', 'in_progress']);

        // Filter by class if selected
        if ($classId) {
            $query->where('studentRegistration.schoolclassid', $classId);
        }

        $query->select(
            'studentRegistration.id',
            'studentRegistration.firstname',
            'studentRegistration.lastname',
            'studentRegistration.admissionNo',
            'studentpicture.picture as picture',
            'results.score',
            'results.total_marks',
            'exam_attempts.status as attempt_status',
            DB::raw('(SELECT COUNT(*) FROM answers WHERE answers.user_id = studentRegistration.id AND answers.exam_id = ' . $examId . ') as attempted_questions')
        )
        ->orderBy('studentRegistration.lastname');

        $students = $query->paginate(15)->appends(['class_id' => $classId]);

        // Get classes this exam is assigned to (for dropdown)
        $assignedClasses = $exam->schoolclasses()
            ->select('schoolclass.id as schoolclassID', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name')
            ->join('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($students);
        }

        $pagetitle = 'Students who Attempted: ' . $exam->title;

        return view('exam.students', compact('pagetitle', 'exam', 'students', 'assignedClasses', 'classId'));
    }

    /**
     * Delete a student's exam attempt so they can retake it.
     */
    public function deleteStudentAttempt($examId, $studentId)
    {
        $exam = Exam::where('id', $examId)->where('staffId', auth()->user()->id)->firstOrFail();

        try {
            // Delete answers
            Answer::where('exam_id', $examId)
                  ->where('user_id', $studentId)
                  ->delete();

            // Delete result
            DB::table('results')
              ->where('exam_id', $examId)
              ->where('user_id', $studentId)
              ->delete();

            // Delete exam attempt (for both completed and in_progress)
            $deletedAttempt = DB::table('exam_attempts')
              ->where('exam_id', $examId)
              ->where('student_id', $studentId)
              ->delete();

            $message = $deletedAttempt > 0
                ? 'Student\'s exam attempt deleted successfully. They can now retake the exam.'
                : 'No active attempt found for this student.';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error("Error deleting student attempt: " . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the attempt. Please check the logs for details.'
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while deleting the attempt.');
        }
    }

    /**
     * Show detailed answers for a student in the specified exam.
     */
    public function showStudentAnswers($examId, $studentId)
    {
        $exam = Exam::where('id', $examId)->where('staffId', auth()->user()->id)->firstOrFail();

        $student = DB::table('studentRegistration')
            ->where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'admissionNo')
            ->firstOrFail();

        $result = DB::table('results')
            ->where('user_id', $studentId)
            ->where('exam_id', $examId)
            ->first();

        $questionAnswers = DB::table('questions')
            ->leftJoin('answers', function($join) use ($examId, $studentId) {
                $join->on('questions.id', '=', 'answers.question_id')
                     ->where('answers.exam_id', '=', $examId)
                     ->where('answers.user_id', '=', $studentId);
            })
            ->leftJoin('options as student_opt', 'answers.option_id', '=', 'student_opt.id')
            ->leftJoin('options as correct_opt', function($join) {
                $join->on('correct_opt.question_id', '=', 'questions.id')
                     ->where('correct_opt.is_correct', '=', 1);
            })
            ->where('questions.exam_id', $examId)
            ->select(
                'questions.id',
                'questions.question_text',
                'questions.image',
                'questions.type',
                'student_opt.option_text as student_answer',
                'correct_opt.option_text as correct_answer',
                'answers.id as answer_id',
                DB::raw('CASE
                    WHEN answers.id IS NULL THEN "Not Attempted"
                    ELSE CASE WHEN student_opt.is_correct = 1 THEN "Yes" ELSE "No" END
                END as marked_correct')
            )
            ->orderBy('questions.id')
            ->get();

        $pagetitle = 'Exam Answers: ' . $student->firstname . ' ' . $student->lastname . ' - ' . $exam->title;

        return view('exam.student-answers', compact('pagetitle', 'exam', 'student', 'questionAnswers', 'result'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staffId' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'termid' => 'required|integer',
            'session' => 'required|integer',
            'subject_id' => 'required|integer',
            'schoolclass_ids' => 'required|array',
            'schoolclass_ids.*' => 'integer|exists:schoolclass,id',
            'is_published' => 'boolean|nullable',
        ]);

        // Handle the checkbox value (will be null if not checked)
        $validated['is_published'] = $request->has('is_published') ? true : false;

        $exam = Exam::create(collect($validated)->except('schoolclass_ids')->toArray());
        $exam->schoolclasses()->attach($validated['schoolclass_ids']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam created successfully.',
                'exam' => $exam
            ]);
        }

        return redirect()->route('exams.index')->with('success', 'Exam created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $exam = Exam::with('schoolclasses')->where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'exam' => $exam,
                'schoolclass_ids' => $exam->schoolclasses->pluck('id')->toArray()
            ]);
        }

        // For non-AJAX, you might want to return a view, but since it's AJAX-driven, optional
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'termid' => 'required|integer',
            'session' => 'required|integer',
            'subject_id' => 'required|integer',
            'schoolclass_ids' => 'required|array',
            'schoolclass_ids.*' => 'integer|exists:schoolclass,id',
            'is_published' => 'boolean|nullable',
        ]);

        // Handle the checkbox value
        $validated['is_published'] = $request->has('is_published') ? true : false;

        $exam->update(collect($validated)->except('schoolclass_ids')->toArray());
        $exam->schoolclasses()->sync($validated['schoolclass_ids']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully.',
                'exam' => $exam->fresh()
            ]);
        }

        return redirect()->route('exams.index')->with('success', 'Exam updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();
        $exam->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam deleted successfully.'
            ]);
        }

        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully');
    }

    /**
     * Bulk delete resources.
     */
    public function bulkDestroy(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No exams selected for deletion.'
            ], 400);
        }

        $deletedCount = Exam::whereIn('id', $ids)
                            ->where('staffId', $user->id)
                            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' exams deleted successfully.'
        ]);
    }


  /**
     * Generate PDF question paper for a student's exam attempt.
     */
    public function generateQuestionPaperPdf(Exam $exam, $studentId)
    {
        // Ensure the exam belongs to the logged-in user
        // if ($exam->staffId !== auth()->user()->id) {
        //     abort(403, 'Unauthorized access to this exam.');
        // }

        $student = DB::table('studentRegistration')
            ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
            ->where('studentRegistration.id', $studentId)
            ->select(
                'studentRegistration.id',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.admissionNo',
                'studentpicture.picture as picture'
            )
            ->firstOrFail();

        // NEW: Handle picture path - ensure it's a full asset path or fallback
        if ($student->picture && Storage::disk('public')->exists($student->picture)) {
            $student->picture_path = asset('storage/' . $student->picture);
        } else {
            // Fallback: Check if default file exists, else use a placeholder
            $defaultPath = 'student_avatars/unnamed.jpg';
            $student->picture_path = Storage::disk('public')->exists($defaultPath)
                ? asset('storage/' . $defaultPath)
                : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNFNUU1RTUiLz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM5Qzk5QUMiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj4KICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSI4Ii8+Cjwvc3ZnPgo='; // Base64 SVG placeholder for no image
        }

        $result = DB::table('results')
            ->where('user_id', $studentId)
            ->where('exam_id', $exam->id)
            ->first();

        // Fetch school info (active record)
        $school = SchoolInformation::where('is_active', true)->first();

        // Fetch attempt for date taken
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->whereIn('status', ['completed', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first();

        // Load questions with all options
        $questions = Question::where('exam_id', $exam->id)
            ->with('options') // All options
            ->get();

        // For each question, get student's answer
        foreach ($questions as $question) {
            $studentAnswer = Answer::where('question_id', $question->id)
                ->where('user_id', $studentId)
                ->where('exam_id', $exam->id)
                ->with('option') // Student's selected option
                ->first();

            $question->student_answer = $studentAnswer ? $studentAnswer->getAnswerTextAttribute() ?? 'Not Attempted' : 'Not Attempted';
            $question->student_option_id = $studentAnswer ? $studentAnswer->option_id : null;
            $question->marked_correct = $studentAnswer && ($studentAnswer->option ? $studentAnswer->option->is_correct : false) ? 'Yes' : ($studentAnswer ? 'No' : 'Not Attempted');
        }

        $data = compact('exam', 'student', 'result', 'school', 'attempt', 'questions');

        $pdf = Pdf::loadView('exam.question-paper-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,  // Allows loading external images
            'isPhpEnabled' => true     // For dynamic content
        ]);

        $filename = "Question-Paper-{$student->firstname}-{$student->lastname}-{$exam->title}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Exam analytics dashboard.
     */
    public function analytics($examId)
    {
        $exam = Exam::where('id', $examId)
                    ->where('staffId', auth()->user()->id)
                    ->with(['schoolclasses', 'questions.options'])
                    ->firstOrFail();

        // Basic stats
        $attempts = ExamAttempt::where('exam_id', $examId)
            ->whereIn('status', ['completed'])
            ->get();

        $totalStudents = $attempts->count();
        $completedCount = $attempts->where('status', 'completed')->count();
        $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 1) : 0;

        $results = DB::table('results')
            ->where('exam_id', $examId)
            ->get();

        $avgScore = $results->avg('score') ?? 0;
        $highestScore = $results->max('score') ?? 0;
        $lowestScore = $results->min('score') ?? 0;

        // Top 5 performers
        $topPerformers = DB::table('results')
            ->join('studentRegistration', 'results.user_id', '=', 'studentRegistration.id')
            ->where('results.exam_id', $examId)
            ->select('studentRegistration.firstname', 'studentRegistration.lastname', 'results.score', 'results.total_marks')
            ->orderByDesc('results.score')
            ->limit(5)
            ->get();

        // Question difficulty (percentage correct)
        $questionStats = [];
        foreach ($exam->questions as $question) {
            $correctCount = DB::table('answers')
                ->join('options', 'answers.option_id', '=', 'options.id')
                ->where('answers.question_id', $question->id)
                ->where('answers.exam_id', $examId)
                ->where('options.is_correct', 1)
                ->count();

            $attemptedCount = DB::table('answers')
                ->where('question_id', $question->id)
                ->where('exam_id', $examId)
                ->count();

            $correctRate = $attemptedCount > 0 ? round(($correctCount / $attemptedCount) * 100, 1) : 0;

            $questionStats[] = [
                'text' => Str::limit($question->question_text, 60),
                'correct_rate' => $correctRate,
                'attempted' => $attemptedCount
            ];
        }

        // Score distribution (histogram bins: e.g., 0-10, 11-20, ..., 91-100)
        $scoreBins = array_fill(0, 10, 0); // 0-9, 10-19, ..., 90-99 (assuming percentage or raw score)
        foreach ($results as $result) {
            if ($result->total_marks > 0) {
                $percentage = ($result->score / $result->total_marks) * 100;
                $bin = min(9, floor($percentage / 10));
                $scoreBins[$bin]++;
            }
        }

        // Average score per question (already have questionStats, but let's compute avg % correct)
        $questionAvgCorrect = collect($questionStats)->pluck('correct_rate')->toArray();

        $pagetitle = 'Analytics: ' . $exam->title;

        return view('exam.analytics', compact(
            'pagetitle', 'exam', 'totalStudents', 'completedCount', 'completionRate',
            'avgScore', 'highestScore', 'lowestScore', 'topPerformers', 'questionStats',
            'scoreBins',           // for histogram
            'questionAvgCorrect'   // for question difficulty bar
        ));
    }
}
