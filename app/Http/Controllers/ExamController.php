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

        $query = Exam::query()
            ->with(['schoolclass' => function($query) {
                $query->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                      ->select('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm');
            }, 'subject'])
            ->withCount('questions')
            ->where('staffId', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $exams = $query->orderBy('id', 'desc')->paginate(15);

        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        // Get all subjects for the teacher - FIXED to get subject.id
        $mysubjects = SubjectTeacher::where('staffid', $user->id)
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->join('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->join('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->select([
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'schoolclass.schoolclass as class_name',
                'schoolclass.id as class_id',
                'schoolarm.arm as arm_name',
                'schoolterm.term as term_name',
                'schoolterm.id as term_id',
                'schoolsession.session as session_name',
                'schoolsession.id as session_id',
                'subjectteacher.termid',
                'subjectteacher.sessionid'
            ])
            ->get()
            ->unique('subject_id')
            ->sortBy('subject_name')
            ->values();

        $myclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(
                'schoolclass.id as schoolclassID',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name'
            )->get();

        $pagetitle = 'Exams Management';

        return view('exam.index', compact('pagetitle', 'exams', 'terms', 'sessions', 'mysubjects', 'myclass'));
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
            'termid'          => 'required|integer|exists:schoolterm,id',
            'session'         => 'required|integer|exists:schoolsession,id',
            'subject_id'      => 'required|integer|exists:subject,id',
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
        abort(404);
    }

    public function edit(string $id)
    {
        $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();

        // Get all exams in the same group
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
            'termid'          => 'required|integer|exists:schoolterm,id',
            'session'         => 'required|integer|exists:schoolsession,id',
            'subject_id'      => 'required|integer|exists:subject,id',
            'schoolclass_ids' => 'required|array|min:1',
            'schoolclass_ids.*' => 'integer|exists:schoolclass,id',
            'is_published'    => 'boolean|nullable',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['staffId'] = $exam->staffId;

        \Log::info('Validated data:', $validated);

        // Get all exams in the group
        $groupExams = Exam::where('staffId', $exam->staffId)
            ->where('title', $exam->title)
            ->where('subject_id', $exam->subject_id)
            ->where('termid', $exam->termid)
            ->where('session', $exam->session)
            ->get();

        // Get exam IDs in the group
        $groupExamIds = $groupExams->pluck('id')->toArray();

        // Get questions for all exams in the group
        $questionIds = Question::whereIn('exam_id', $groupExamIds)->pluck('id')->toArray();

        \Log::info('Group exam IDs:', $groupExamIds);
        \Log::info('Question IDs to preserve:', $questionIds);

        // Create new exams for each selected class
        $createdCount = 0;
        $newExamIds = [];

        foreach ($validated['schoolclass_ids'] as $classId) {
            // Check if an exam already exists for this class in the group
            $existingExam = $groupExams->where('schoolclass_id', $classId)->first();

            if ($existingExam) {
                // Update existing exam
                $existingExam->update([
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'duration' => $validated['duration'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'termid' => $validated['termid'],
                    'session' => $validated['session'],
                    'subject_id' => $validated['subject_id'],
                    'is_published' => $validated['is_published']
                ]);
                $newExamIds[] = $existingExam->id;
                $createdCount++;
                \Log::info("Updated existing exam for class {$classId}");
            } else {
                // Create new exam for this class
                $newExam = Exam::create([
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
                ]);
                $newExamIds[] = $newExam->id;
                $createdCount++;
                \Log::info("Created new exam for class {$classId}");
            }
        }

        // If there are existing questions, copy them to new exams
        if (!empty($questionIds) && !empty($newExamIds)) {
            foreach ($newExamIds as $newExamId) {
                foreach ($questionIds as $questionId) {
                    // Check if this question exists for this exam already
                    $existingQuestion = Question::where('exam_id', $newExamId)
                        ->where('id', $questionId)
                        ->first();

                    if (!$existingQuestion) {
                        // Get the original question
                        $originalQuestion = Question::find($questionId);

                        // Duplicate the question for the new exam
                        $newQuestion = $originalQuestion->replicate();
                        $newQuestion->exam_id = $newExamId;
                        $newQuestion->save();

                        // Duplicate options if they exist
                        if ($originalQuestion->options()->exists()) {
                            foreach ($originalQuestion->options as $option) {
                                $newOption = $option->replicate();
                                $newOption->question_id = $newQuestion->id;
                                $newOption->save();
                            }
                        }
                    }
                }
            }
            \Log::info("Copied questions to new exams");
        }

        // Delete exams for classes that are no longer selected
        $classesToDelete = $groupExams->whereNotIn('schoolclass_id', $validated['schoolclass_ids']);

        foreach ($classesToDelete as $examToDelete) {
            // Only delete if the exam has no attempts
            $hasAttempts = ExamAttempt::where('exam_id', $examToDelete->id)->exists();
            if (!$hasAttempts) {
                $examToDelete->delete();
                \Log::info("Deleted exam for removed class {$examToDelete->schoolclass_id}");
            } else {
                \Log::info("Skipped deletion of exam {$examToDelete->id} because it has attempts");
            }
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
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->join('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->join('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid');

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
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'schoolclass.schoolclass as class_name',
                'schoolclass.id as class_id',
                'schoolarm.arm as arm_name',
                'schoolterm.term as term_name',
                'schoolterm.id as term_id',
                'schoolsession.session as session_name',
                'schoolsession.id as session_id',
                'subjectteacher.termid',
                'subjectteacher.sessionid'
            ])
            ->get()
            ->unique('subject_id')
            ->sortBy('subject_name')
            ->map(function($item) {
                $displayText = sprintf('%s (%s) - %s %s - %s %s',
                    $item->subject_name,
                    $item->subject_code,
                    $item->term_name,
                    $item->session_name,
                    $item->class_name,
                    $item->arm_name ? '(' . $item->arm_name . ')' : ''
                );

                return [
                    'id' => $item->subject_id,
                    'display_text' => $displayText,
                    'subject' => $item->subject_name,
                    'subjectcode' => $item->subject_code,
                    'schoolclass' => $item->class_name,
                    'class_id' => $item->class_id,
                    'arm' => $item->arm_name,
                    'term' => $item->term_name,
                    'session' => $item->session_name,
                    'termid' => $item->termid,
                    'sessionid' => $item->sessionid
                ];
            })
            ->values();

        return response()->json(['subjects' => $subjects]);
    }

    public function getClassesForSubject($subjectId)
    {
        $user = auth()->user();

        // Get classes where the teacher teaches this subject
        $classes = DB::table('subjectteacher')
            ->join('subjectclass', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->join('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('subjectteacher.staffid', $user->id)
            ->where('subjectteacher.subjectid', $subjectId)
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass',
                'schoolarm.arm'
            )
            ->distinct()
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
