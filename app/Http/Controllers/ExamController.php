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
        $this->middleware('permission:View exam', ['only' => ['index', 'show', 'showStudents', 'showStudentAnswers', 'analytics']]);
        $this->middleware('permission:Create exam', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update exam', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete exam', ['only' => ['destroy', 'bulkDestroy', 'deleteStudentAttempt']]);
    }

  public function index(Request $request)
{
    $user = auth()->user();

    $query = Exam::query()->with(['schoolclass', 'subject']);

    $query->where('staffId', $user->id);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Get all exams and group them by their common attributes
    $exams = $query->orderBy('id', 'desc')->get();

    // Group exams by title, subject_id, termid, session to show as single records
    $groupedExams = collect();

    foreach ($exams->groupBy(['title', 'subject_id', 'termid', 'session']) as $group) {
        $firstExam = $group->first();

        // Get all classes for this exam group
        $classIds = $group->pluck('schoolclass_id')->toArray();
        $classes = Schoolclass::whereIn('id', $classIds)
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select('schoolclass.schoolclass', 'schoolarm.arm', 'schoolclass.id')
            ->get();

        // Format class names
        $classNames = $classes->map(function($class) {
            return $class->schoolclass . ($class->arm ? ' (' . $class->arm . ')' : '');
        })->implode(', ');

        // Create a modified exam object with class information
        $groupedExam = (object)[
            'id' => $firstExam->id,
            'title' => $firstExam->title,
            'description' => $firstExam->description,
            'duration' => $firstExam->duration,
            'start_time' => $firstExam->start_time,
            'end_time' => $firstExam->end_time,
            'is_published' => $firstExam->is_published,
            'classes' => $classes,
            'class_names' => $classNames,
            'total_exams' => $group->count(), // Number of individual exam records
            'subject' => $firstExam->subject,
            'termid' => $firstExam->termid,
            'session' => $firstExam->session,
            'subject_id' => $firstExam->subject_id
        ];

        $groupedExams->push($groupedExam);
    }

    // Paginate the grouped exams
    $page = $request->get('page', 1);
    $perPage = 15;
    $paginatedExams = new \Illuminate\Pagination\LengthAwarePaginator(
        $groupedExams->forPage($page, $perPage),
        $groupedExams->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'data'         => $paginatedExams->items(),
            'current_page' => $paginatedExams->currentPage(),
            'last_page'    => $paginatedExams->lastPage(),
            'per_page'     => $paginatedExams->perPage(),
            'total'        => $paginatedExams->total(),
            'from'         => $paginatedExams->firstItem(),
            'to'           => $paginatedExams->lastItem(),
        ]);
    }

    $terms = Schoolterm::all();
    $sessions = Schoolsession::all();

    // Get all subjects for the teacher
    $mysubjects = SubjectTeacher::where('staffid', $user->id)
        ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
        ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
        ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
        ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
        ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
        ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
        ->select([
            'subjectteacher.id as id',
            'subject.subject as subject',
            'subject.subject_code as subjectcode',
            'schoolclass.schoolclass as schoolclass',
            'schoolclass.id as class_id',
            'schoolarm.arm as arm',
            'subjectteacher.termid as termid',
            'subjectteacher.sessionid as sessionid',
            'schoolterm.term as term',
            'schoolsession.session as session'
        ])
        ->get()
        ->unique('id')
        ->sortBy('subject')
        ->values();

    $myclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
        ->select(
            'schoolclass.id as schoolclassID',
            'schoolclass.schoolclass',
            'schoolarm.arm as arm_name'
        )->get();

    $pagetitle = 'Exams Management';

    return view('exam.index', compact('pagetitle', 'paginatedExams', 'terms', 'sessions', 'mysubjects', 'myclass'));
}


    public function create()
    {
        //
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

    public function show($id)
    {
        // Not implemented - use showStudents instead
        abort(404);
    }

    public function edit(string $id)
    {
        $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();

        // Get all exams in the same group (same title, subject, term, session)
        $groupExams = Exam::where('staffId', $exam->staffId)
            ->where('title', $exam->title)
            ->where('subject_id', $exam->subject_id)
            ->where('termid', $exam->termid)
            ->where('session', $exam->session)
            ->get();

        // Get selected class IDs
        $groupClassIds = $groupExams->pluck('schoolclass_id')->toArray();

        if (request()->ajax()) {
            return response()->json([
                'success'         => true,
                'exam'            => $exam,
                'schoolclass_ids' => $groupClassIds,
                'termid'          => $exam->termid,
                'sessionid'       => $exam->session,
                'subject_id'      => $exam->subject_id
            ]);
        }

        abort(404);
    }

    public function update(Request $request, string $id)
    {
        \Log::info('Update request received:', [
            'exam_id' => $id,
            'data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();

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

        \Log::info('Validated data:', $validated);

        // Delete all exams in the group
        $deletedCount = Exam::where('staffId', $exam->staffId)
            ->where('title', $exam->title)
            ->where('subject_id', $exam->subject_id)
            ->where('termid', $exam->termid)
            ->where('session', $exam->session)
            ->delete();

        \Log::info("Deleted {$deletedCount} exams from group");

        // Create new exams for each selected class
        $createdCount = 0;
        foreach ($validated['schoolclass_ids'] as $classId) {
            $examData = [
                'staffId' => $validated['staffId'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration' => $validated['duration'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'termid' => $validated['termid'],
                'session' => $validated['session'],
                'subject_id' => $validated['subject_id'],
                'schoolclass_id' => $classId,
                'is_published' => $validated['is_published']
            ];

            Exam::create($examData);
            $createdCount++;
            \Log::info("Created exam for class {$classId}");
        }

        $message = "Exam updated successfully for {$createdCount} class" . ($createdCount > 1 ? 'es' : '.') . ".";

        \Log::info($message);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('exams.index')->with('success', $message);
    }

    public function destroy(string $id)
    {
        $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();
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

    public function getFilteredSubjects(Request $request)
    {
        $user = auth()->user();

        $query = SubjectTeacher::where('staffid', $user->id)
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid');

        if ($request->filled('term_id')) {
            $query->where('subjectteacher.termid', $request->term_id);
        }

        if ($request->filled('session_id')) {
            $query->where('subjectteacher.sessionid', $request->session_id);
        }

        if ($request->filled('class_id')) {
            $query->where('schoolclass.id', $request->class_id);
        }

        $subjects = $query->select([
                'subjectteacher.id as id',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'schoolclass.schoolclass as schoolclass',
                'schoolclass.id as class_id',
                'schoolarm.arm as arm',
                'subjectteacher.termid as termid',
                'subjectteacher.sessionid as sessionid',
                'schoolterm.term as term',
                'schoolsession.session as session'
            ])
            ->get()
            ->unique('id')
            ->sortBy('subject')
            ->map(function($item) {
                $displayText = sprintf('%s (%s) - %s %s - %s %s',
                    $item->subject,
                    $item->subjectcode,
                    $item->term,
                    $item->session,
                    $item->schoolclass,
                    $item->arm ? '(' . $item->arm . ')' : ''
                );

                return [
                    'id' => $item->id,
                    'display_text' => $displayText,
                    'subject' => $item->subject,
                    'subjectcode' => $item->subjectcode,
                    'schoolclass' => $item->schoolclass,
                    'class_id' => $item->class_id,
                    'arm' => $item->arm,
                    'term' => $item->term,
                    'session' => $item->session,
                    'termid' => $item->termid,
                    'sessionid' => $item->sessionid
                ];
            })
            ->values();

        return response()->json(['subjects' => $subjects]);
    }

    public function getClassesForSubject($subjectTeacherId)
    {
        $user = auth()->user();

        // Verify the subject belongs to the teacher
        $subjectTeacher = SubjectTeacher::where('id', $subjectTeacherId)
            ->where('staffid', $user->id)
            ->firstOrFail();

        // Get all classes for this subject teacher
        $classes = DB::table('subjectclass')
            ->join('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('subjectclass.subjectteacherid', $subjectTeacherId)
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass',
                'schoolarm.arm'
            )
            ->get();

        return response()->json([
            'success' => true,
            'classes' => $classes
        ]);
    }

    public function showStudents(Request $request, $examId)
    {
        $exam = Exam::where('id', $examId)
                    ->where('staffId', auth()->user()->id)
                    ->with('schoolclass')
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

    public function deleteStudentAttempt($examId, $studentId)
    {
        $exam = Exam::where('id', $examId)->where('staffId', auth()->user()->id)->firstOrFail();

        try {
            // Delete student answers
            Answer::where('exam_id', $examId)
                  ->where('user_id', $studentId)
                  ->delete();

            // Delete results
            DB::table('results')
              ->where('exam_id', $examId)
              ->where('user_id', $studentId)
              ->delete();

            // Delete exam attempt
            $deletedAttempt = DB::table('exam_attempts')
              ->where('exam_id', $examId)
              ->where('student_id', $studentId)
              ->delete();

            $message = $deletedAttempt > 0
                ? 'Student\'s exam attempt deleted successfully. They can now retake the exam.'
                : 'No active attempt found for this student.';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error("Error deleting student attempt: " . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting attempt'], 500);
            }

            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

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

    public function generateQuestionPaperPdf(Exam $exam, $studentId)
    {
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

        if ($student->picture && Storage::disk('public')->exists($student->picture)) {
            $student->picture_path = asset('storage/' . $student->picture);
        } else {
            $defaultPath = 'student_avatars/unnamed.jpg';
            $student->picture_path = Storage::disk('public')->exists($defaultPath)
                ? asset('storage/' . $defaultPath)
                : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNFNUU1RTUiLz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM5Qzk5QUMiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj4KICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSI4Ii8+Cjwvc3ZnPgo=';
        }

        $result = DB::table('results')
            ->where('user_id', $studentId)
            ->where('exam_id', $exam->id)
            ->first();

        $school = SchoolInformation::where('is_active', true)->first();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->whereIn('status', ['completed', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first();

        $questions = Question::where('exam_id', $exam->id)
            ->with('options')
            ->get();

        foreach ($questions as $question) {
            $studentAnswer = Answer::where('question_id', $question->id)
                ->where('user_id', $studentId)
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

    public function analytics($examId)
    {
        $exam = Exam::where('id', $examId)
                    ->where('staffId', auth()->user()->id)
                    ->with(['schoolclass', 'questions.options'])
                    ->firstOrFail();

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

        $topPerformers = DB::table('results')
            ->join('studentRegistration', 'results.user_id', '=', 'studentRegistration.id')
            ->where('results.exam_id', $examId)
            ->select('studentRegistration.firstname', 'studentRegistration.lastname', 'results.score', 'results.total_marks')
            ->orderByDesc('results.score')
            ->limit(5)
            ->get();

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
