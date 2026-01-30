<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Schoolterm;
use App\Models\ExamAttempt;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\SubjectTeacher;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View exam', ['only' => ['index', 'showStudents', 'showStudentAnswers', 'analytics']]);
        $this->middleware('permission:Create exam', ['only' => ['store']]);
        $this->middleware('permission:Update exam', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete exam', ['only' => ['destroy', 'bulkDestroy', 'deleteStudentAttempt']]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $current = "Current";

        $query = Exam::query()->with(['schoolclass']);

        $query->where('staffId', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $exams = $query->orderBy('id', 'desc')->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'data'         => $exams->items(),
                'current_page' => $exams->currentPage(),
                'last_page'    => $exams->lastPage(),
                'per_page'     => $exams->perPage(),
                'total'        => $exams->total(),
                'from'         => $exams->firstItem(),
                'to'           => $exams->lastItem(),
            ]);
        }

        $terms = Schoolterm::all();
        $session = Schoolsession::all();

        // Get subjects with all necessary information
        $mysubjects = SubjectTeacher::where('staffid', $user->id)
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current)
            ->select([
                'subjectteacher.id as id',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            ])
            ->get()
            ->map(function ($item) {
                // Create formatted display name
                $item->display_name = sprintf(
                    '%s (%s) %s-%s %s%s',
                    $item->subject,
                    $item->subjectcode,
                    $item->term_name,
                    $item->session_name,
                    $item->class_name,
                    $item->arm_name ? ' - ' . $item->arm_name : ''
                );
                return $item;
            })
            ->sortBy('subject')
            ->unique('id');

        $myclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(
                'schoolclass.id as schoolclassID',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name'
            )->get();

        $pagetitle = 'Exams Management';

        return view('exam.index', compact('pagetitle', 'exams', 'terms', 'session', 'mysubjects', 'myclass'));
    }

    public function create()
    {
        // This method is handled by index() which returns the view with the modal
        return redirect()->route('exams.index');
    }

    /**
     * Get filtered subjects based on term, session, and class
     */
    public function getSubjects(Request $request)
    {
        $user = auth()->user();
        $current = "Current";

        $query = SubjectTeacher::where('staffid', $user->id)
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->where('schoolsession.status', '=', $current);

        // Apply filters if provided
        if ($request->filled('term_id')) {
            $query->where('schoolterm.id', $request->term_id);
        }

        if ($request->filled('session_id')) {
            $query->where('schoolsession.id', $request->session_id);
        }

        if ($request->filled('class_id')) {
            $query->where('schoolclass.id', $request->class_id);
        }

        $subjects = $query->select([
                'subjectteacher.id as id',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            ])
            ->distinct()
            ->get()
            ->map(function ($item) {
                $item->display_name = sprintf(
                    '%s (%s) %s-%s %s%s',
                    $item->subject,
                    $item->subjectcode,
                    $item->term_name,
                    $item->session_name,
                    $item->class_name,
                    $item->arm_name ? ' - ' . $item->arm_name : ''
                );
                return $item;
            });

        return response()->json($subjects);
    }

    /**
     * Get classes for a specific subject teacher (subject)
     */
    public function getClassesForSubject($subjectTeacherId)
    {
        $subjectTeacher = SubjectTeacher::findOrFail($subjectTeacherId);

        $classes = DB::table('subjectclass')
            ->join('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('subjectclass.subjectteacherid', $subjectTeacherId)
            ->select(
                'schoolclass.id as id',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm'
            )
            ->get();

        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staffId'         => 'required|integer',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'duration'        => 'required|integer|min:1',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date|after:start_time',
            'termid'          => 'required|integer',
            'session'         => 'required|integer',
            'subject_id'      => 'required|integer',
            'schoolclass_ids' => 'required|array|min:1',
            'schoolclass_ids.*' => 'integer|exists:schoolclass,id',
            'is_published'    => 'boolean|nullable',
        ]);

        $validated['is_published'] = $request->has('is_published');

        $createdCount = 0;
        foreach ($validated['schoolclass_ids'] as $classId) {
            $examData = $validated;
            $examData['schoolclass_id'] = $classId;
            unset($examData['schoolclass_ids']);
            Exam::create($examData);
            $createdCount++;
        }

        $message = "Exam created successfully for {$createdCount} class" . ($createdCount > 1 ? 'es' : '.') . ".";

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('exams.index')->with('success', $message);
    }

    public function edit(Exam $exam)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $groupClassIds = Exam::where('staffId', $exam->staffId)
            ->where('title', $exam->title)
            ->where('subject_id', $exam->subject_id)
            ->where('termid', $exam->termid)
            ->where('session', $exam->session)
            ->pluck('schoolclass_id')
            ->toArray();

        if (request()->ajax()) {
            return response()->json([
                'success'         => true,
                'exam'            => $exam,
                'schoolclass_ids' => $groupClassIds
            ]);
        }

        abort(404);
    }

    public function update(Request $request, Exam $exam)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'duration'        => 'required|integer|min:1',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date|after:start_time',
            'termid'          => 'required|integer',
            'session'         => 'required|integer',
            'subject_id'      => 'required|integer',
            'schoolclass_ids' => 'required|array|min:1',
            'schoolclass_ids.*' => 'integer|exists:schoolclass,id',
            'is_published'    => 'boolean|nullable',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['staffId'] = $exam->staffId;

        Exam::where('staffId', $exam->staffId)
            ->where('title', $exam->title)
            ->where('subject_id', $exam->subject_id)
            ->where('termid', $exam->termid)
            ->where('session', $exam->session)
            ->delete();

        $createdCount = 0;
        foreach ($validated['schoolclass_ids'] as $classId) {
            $examData = $validated;
            $examData['schoolclass_id'] = $classId;
            unset($examData['schoolclass_ids']);
            Exam::create($examData);
            $createdCount++;
        }

        $message = "Exam updated successfully for {$createdCount} class" . ($createdCount > 1 ? 'es' : '.') . ".";

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('exams.index')->with('success', $message);
    }

    public function destroy(Exam $exam)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $exam->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Exam deleted successfully.']);
        }

        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No exams selected'], 400);
        }

        $count = Exam::whereIn('id', $ids)
            ->where('staffId', auth()->user()->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} exam(s) deleted successfully."
        ]);
    }

    public function showStudents(Exam $exam, Request $request)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $classId = $request->query('class_id');

        $query = DB::table('exam_attempts')
            ->join('studentRegistration', 'exam_attempts.student_id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
            ->leftJoin('results', function ($join) use ($exam) {
                $join->on('exam_attempts.student_id', '=', 'results.user_id')
                     ->where('results.exam_id', '=', $exam->id);
            })
            ->where('exam_attempts.exam_id', $exam->id)
            ->whereIn('exam_attempts.status', ['completed', 'in_progress']);

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
            DB::raw('(SELECT COUNT(*) FROM answers WHERE answers.user_id = studentRegistration.id AND answers.exam_id = ' . $exam->id . ') as attempted_questions')
        )
        ->orderBy('studentRegistration.lastname');

        $students = $query->paginate(15)->appends(['class_id' => $classId]);

        $assignedClasses = Schoolclass::whereIn('id',
            Exam::where('title', $exam->title)
                ->where('staffId', $exam->staffId)
                ->where('subject_id', $exam->subject_id)
                ->where('termid', $exam->termid)
                ->where('session', $exam->session)
                ->pluck('schoolclass_id')
        )->get(['id as schoolclassID', 'schoolclass', 'arm']);

        if ($request->ajax()) {
            return response()->json($students);
        }

        $pagetitle = 'Students who Attempted: ' . $exam->title;

        return view('exam.students', compact('pagetitle', 'exam', 'students', 'assignedClasses', 'classId'));
    }

    public function deleteStudentAttempt(Exam $exam, $student)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        try {
            Answer::where('exam_id', $exam->id)
                  ->where('user_id', $student)
                  ->delete();

            DB::table('results')
              ->where('exam_id', $exam->id)
              ->where('user_id', $student)
              ->delete();

            $deletedAttempt = DB::table('exam_attempts')
              ->where('exam_id', $exam->id)
              ->where('student_id', $student)
              ->delete();

            $message = $deletedAttempt > 0
                ? 'Student\'s exam attempt deleted successfully. They can now retake the exam.'
                : 'No active attempt found for this student.';

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error("Error deleting student attempt: " . $e->getMessage());

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting attempt'], 500);
            }

            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    public function showStudentAnswers(Exam $exam, $student)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $student = DB::table('studentRegistration')
            ->where('id', $student)
            ->select('id', 'firstname', 'lastname', 'admissionNo')
            ->firstOrFail();

        $result = DB::table('results')
            ->where('user_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();

        $questionAnswers = DB::table('questions')
            ->leftJoin('answers', function($join) use ($exam, $student) {
                $join->on('questions.id', '=', 'answers.question_id')
                     ->where('answers.exam_id', '=', $exam->id)
                     ->where('answers.user_id', '=', $student->id);
            })
            ->leftJoin('options as student_opt', 'answers.option_id', '=', 'student_opt.id')
            ->leftJoin('options as correct_opt', function($join) {
                $join->on('correct_opt.question_id', '=', 'questions.id')
                     ->where('correct_opt.is_correct', '=', 1);
            })
            ->where('questions.exam_id', $exam->id)
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

    public function generateQuestionPaperPdf(Exam $exam, $student)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $student = DB::table('studentRegistration')
            ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
            ->where('studentRegistration.id', $student)
            ->select(
                'studentRegistration.id',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.admissionNo',
                'studentpicture.picture as picture'
            )
            ->firstOrFail();

        if ($student->picture && Storage::disk('public')->exists($student->picture)) {
            $student->picture_path = asset('storage/' . $student->picture);
        } else {
            $defaultPath = 'student_avatars/unnamed.jpg';
            $student->picture_path = Storage::disk('public')->exists($defaultPath)
                ? asset('storage/' . $defaultPath)
                : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNFNUU1RTUiLz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM5Qzk5QUMiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj4KICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSI4Ii8+Cjwvc3ZnPgo=';
        }

        $result = DB::table('results')
            ->where('user_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();

        $school = SchoolInformation::where('is_active', true)->first();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['completed', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first();

        $questions = Question::where('exam_id', $exam->id)
            ->with('options')
            ->get();

        foreach ($questions as $question) {
            $studentAnswer = Answer::where('question_id', $question->id)
                ->where('user_id', $student->id)
                ->where('exam_id', $exam->id)
                ->with('option')
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
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true
        ]);

        $filename = "Question-Paper-{$student->firstname}-{$student->lastname}-{$exam->title}.pdf";
        return $pdf->download($filename);
    }

    public function analytics(Exam $exam)
    {
        // Check if the exam belongs to the current user
        if ($exam->staffId != auth()->user()->id) {
            abort(403, 'Unauthorized');
        }

        $exam->load(['schoolclass', 'questions.options']);

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->whereIn('status', ['completed'])
            ->get();

        $totalStudents = $attempts->count();
        $completedCount = $attempts->where('status', 'completed')->count();
        $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 1) : 0;

        $results = DB::table('results')
            ->where('exam_id', $exam->id)
            ->get();

        $avgScore = $results->avg('score') ?? 0;
        $highestScore = $results->max('score') ?? 0;
        $lowestScore = $results->min('score') ?? 0;

        $topPerformers = DB::table('results')
            ->join('studentRegistration', 'results.user_id', '=', 'studentRegistration.id')
            ->where('results.exam_id', $exam->id)
            ->select('studentRegistration.firstname', 'studentRegistration.lastname', 'results.score', 'results.total_marks')
            ->orderByDesc('results.score')
            ->limit(5)
            ->get();

        $questionStats = [];
        foreach ($exam->questions as $question) {
            $correctCount = DB::table('answers')
                ->join('options', 'answers.option_id', '=', 'options.id')
                ->where('answers.question_id', $question->id)
                ->where('answers.exam_id', $exam->id)
                ->where('options.is_correct', 1)
                ->count();

            $attemptedCount = DB::table('answers')
                ->where('question_id', $question->id)
                ->where('exam_id', $exam->id)
                ->count();

            $correctRate = $attemptedCount > 0 ? round(($correctCount / $attemptedCount) * 100, 1) : 0;

            $questionStats[] = [
                'text' => Str::limit($question->question_text, 60),
                'correct_rate' => $correctRate,
                'attempted' => $attemptedCount
            ];
        }

        $scoreBins = array_fill(0, 10, 0);
        foreach ($results as $result) {
            if ($result->total_marks > 0) {
                $percentage = ($result->score / $result->total_marks) * 100;
                $bin = min(9, floor($percentage / 10));
                $scoreBins[$bin]++;
            }
        }

        $questionAvgCorrect = collect($questionStats)->pluck('correct_rate')->toArray();

        $pagetitle = 'Analytics: ' . $exam->title;

        return view('exam.analytics', compact(
            'pagetitle', 'exam', 'totalStudents', 'completedCount', 'completionRate',
            'avgScore', 'highestScore', 'lowestScore', 'topPerformers', 'questionStats',
            'scoreBins', 'questionAvgCorrect'
        ));
    }
}
