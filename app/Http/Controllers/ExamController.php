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

        // Load all subjects initially for the dropdown (will be filtered by JS)
        $mysubjects = SubjectTeacher::where('staffid', $user->id)
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->join('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->select([
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'schoolterm.term as term_name',
                'schoolterm.id as term_id',
                'schoolsession.session as session_name',
                'schoolsession.id as session_id',
                'subjectteacher.termid',
                'subjectteacher.sessionid'
            ])
            ->distinct()
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
        try {
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
            $createdExams = [];

            foreach ($validated['schoolclass_ids'] as $classId) {
                $examData = $validated;
                $examData['schoolclass_id'] = $classId;
                unset($examData['schoolclass_ids']);
                $exam = Exam::create($examData);
                $createdExams[] = $exam;
                $createdCount++;
            }

            $message = "Exam created successfully for {$createdCount} class" . ($createdCount > 1 ? 'es' : '.') . ".";

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'exams' => $createdExams
                ]);
            }

            return redirect()->route('exams.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error creating exam: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the exam. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while creating the exam.');
        }
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
        try {
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

            // Get all exams in the original group
            $originalGroupExams = Exam::where('staffId', $exam->staffId)
                ->where('title', $exam->title)
                ->where('subject_id', $exam->subject_id)
                ->where('termid', $exam->termid)
                ->where('session', $exam->session)
                ->get();

            // Get original class IDs
            $originalClassIds = $originalGroupExams->pluck('schoolclass_id')->toArray();
            $newClassIds = $validated['schoolclass_ids'];

            \Log::info('Original class IDs:', $originalClassIds);
            \Log::info('New class IDs:', $newClassIds);

            // Update existing exams
            $updatedCount = 0;
            $createdCount = 0;

            // Update exams for classes that exist in both original and new
            $classesToUpdate = array_intersect($originalClassIds, $newClassIds);

            foreach ($classesToUpdate as $classId) {
                $existingExam = $originalGroupExams->where('schoolclass_id', $classId)->first();
                if ($existingExam) {
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
                    $updatedCount++;
                    \Log::info("Updated existing exam for class {$classId}");
                }
            }

            // Create new exams for classes that are new
            $classesToCreate = array_diff($newClassIds, $originalClassIds);

            foreach ($classesToCreate as $classId) {
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
                $createdCount++;
                \Log::info("Created new exam for class {$classId}");
            }

            // Do NOT delete exams for removed classes - keep them as separate exams
            // $classesToRemove = array_diff($originalClassIds, $newClassIds);
            // We are NOT deleting exams anymore - they remain as separate exams

            $message = "Exam updated successfully. ";
            if ($updatedCount > 0) {
                $message .= "Updated {$updatedCount} existing class" . ($updatedCount > 1 ? 'es' : '') . ". ";
            }
            if ($createdCount > 0) {
                $message .= "Added {$createdCount} new class" . ($createdCount > 1 ? 'es' : '') . ". ";
            }

            \Log::info($message);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('exams.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error updating exam: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the exam. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while updating the exam.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $exam = Exam::where('id', $id)->where('staffId', auth()->user()->id)->firstOrFail();

            // Check if exam has attempts - if yes, we shouldn't delete it
            $hasAttempts = ExamAttempt::where('exam_id', $exam->id)->exists();

            if ($hasAttempts) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete exam because students have already attempted it.'
                    ], 400);
                }
                return redirect()->route('exams.index')->with('error', 'Cannot delete exam because students have already attempted it.');
            }

            // Do NOT delete questions - they remain in the system for other exams
            // Questions are not deleted when exam is deleted

            $exam->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Exam deleted successfully.']);
            }

            return redirect()->route('exams.index')->with('success', 'Exam deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Error deleting exam: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the exam. Please try again.'
                ], 500);
            }

            return redirect()->route('exams.index')->with('error', 'An error occurred while deleting the exam.');
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => 'No exams selected'], 400);
            }

            // Check if any of the exams have attempts
            $examsWithAttempts = Exam::whereIn('id', $ids)
                ->where('staffId', auth()->user()->id)
                ->whereHas('attempts')
                ->count();

            if ($examsWithAttempts > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete {$examsWithAttempts} exam(s) because students have already attempted them."
                ], 400);
            }

            $count = Exam::whereIn('id', $ids)
                ->where('staffId', auth()->user()->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} exam(s) deleted successfully."
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting exams: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting exams. Please try again.'
            ], 500);
        }
    }

    public function getFilteredSubjects(Request $request)
    {
        try {
            $user = auth()->user();

            $query = SubjectTeacher::where('subjectteacher.staffid', $user->id)
                ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->join('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->join('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid');

            if ($request->filled('term_id')) {
                $query->where('subjectteacher.termid', $request->term_id);
            }

            if ($request->filled('session_id')) {
                $query->where('subjectteacher.sessionid', $request->session_id);
            }

            $subjects = $query->select([
                    'subject.id as subject_id',
                    'subject.subject as subject_name',
                    'subject.subject_code',
                    'schoolterm.term as term_name',
                    'schoolterm.id as term_id',
                    'schoolsession.session as session_name',
                    'schoolsession.id as session_id',
                    'subjectteacher.termid',
                    'subjectteacher.sessionid'
                ])
                ->distinct()
                ->get()
                ->unique('subject_id')
                ->sortBy('subject_name')
                ->map(function($item) {
                    $displayText = sprintf('%s (%s) - %s %s',
                        $item->subject_name,
                        $item->subject_code,
                        $item->term_name,
                        $item->session_name
                    );

                    return [
                        'id' => $item->subject_id,
                        'display_text' => $displayText,
                        'subject' => $item->subject_name,
                        'subjectcode' => $item->subject_code,
                        'term' => $item->term_name,
                        'session' => $item->session_name,
                        'termid' => $item->termid,
                        'sessionid' => $item->sessionid
                    ];
                })
                ->values();

            return response()->json(['subjects' => $subjects]);
        } catch (\Exception $e) {
            \Log::error('Error getting filtered subjects: ' . $e->getMessage());
            return response()->json(['subjects' => []], 500);
        }
    }

    public function getClassesForSubject($subjectId)
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('Error getting classes for subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading classes. Please try again.',
                'classes' => []
            ], 500);
        }
    }

    public function showStudents(Request $request, $examId)
    {
        $exam = Exam::where('id', $examId)
                    ->where('staffId', auth()->user()->id)
                    ->with('schoolclass')
                    ->firstOrFail();

        // Get total number of questions and total marks for this exam
        $examTotal = DB::table('questions')
            ->where('exam_id', $examId)
            ->select(
                DB::raw('COUNT(*) as total_questions'),
                DB::raw('SUM(COALESCE(marks, 1.0)) as total_marks')
            )
            ->first();

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

        // Get correct answers count and attempted questions with marks
        $subQuery = DB::table('answers')
            ->join('options', function($join) {
                $join->on('answers.option_id', '=', 'options.id')
                     ->where('options.is_correct', '=', 1);
            })
            ->join('questions', 'answers.question_id', '=', 'questions.id')
            ->where('answers.exam_id', $examId)
            ->groupBy('answers.user_id')
            ->select(
                'answers.user_id',
                DB::raw('COUNT(*) as correct_count'),
                DB::raw('SUM(COALESCE(questions.marks, 1.0)) as marks_earned')
            );

        $query->leftJoinSub($subQuery, 'correct_answers', function($join) {
            $join->on('exam_attempts.student_id', '=', 'correct_answers.user_id');
        });

        $query->select(
            'studentRegistration.id',
            'studentRegistration.firstname',
            'studentRegistration.lastname',
            'studentRegistration.admissionNo',
            'studentpicture.picture as picture',
            'results.score',
            'results.total_marks',
            'exam_attempts.status as attempt_status',
            DB::raw('COALESCE(correct_answers.correct_count, 0) as correct_count'),
            DB::raw('COALESCE(correct_answers.marks_earned, 0) as marks_earned'),
            DB::raw('(SELECT COUNT(*) FROM answers WHERE answers.user_id = studentRegistration.id AND answers.exam_id = ' . $examId . ') as attempted_questions')
        )
        ->orderBy('studentRegistration.lastname');

        $students = $query->paginate(15)->appends(['class_id' => $classId]);

        // Update each student with proper calculations
        foreach ($students as $student) {
            // If results exist, use them, otherwise calculate from answers
            if ($student->score === null && $student->attempt_status === 'completed') {
                $student->score = $student->marks_earned ?? 0;
                $student->total_marks = $examTotal->total_marks ?? 0;
            }

            // Calculate missed questions
            $student->missed = $examTotal->total_questions - ($student->attempted_questions ?? 0);

            // Calculate incorrect answers
            $student->incorrect = ($student->attempted_questions ?? 0) - ($student->correct_count ?? 0);
        }

        $assignedClasses = Schoolclass::whereIn('id',
            Exam::where('title', $exam->title)
                ->where('staffId', $exam->staffId)
                ->where('subject_id', $exam->subject_id)
                ->where('termid', $exam->termid)
                ->where('session', $exam->session)
                ->pluck('schoolclass_id')
        )->get(['id as schoolclassID', 'schoolclass', 'arm']);

        // Pass exam totals to view
        $examTotals = [
            'total_questions' => $examTotal->total_questions ?? 0,
            'total_marks' => $examTotal->total_marks ?? 0
        ];

        if ($request->ajax()) {
            return response()->json($students);
        }

        $pagetitle = 'Students who Attempted: ' . $exam->title;

        return view('exam.students', compact('pagetitle', 'exam', 'students', 'assignedClasses', 'classId', 'examTotals'));
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
    $exam = Exam::where('id', $examId)
                ->where('staffId', auth()->user()->id)
                ->with(['questions.options' => function($query) {
                    $query->select('id', 'question_id', 'option_text', 'is_correct', 'label');
                }])
                ->firstOrFail();

    $student = DB::table('studentRegistration')
        ->where('id', $studentId)
        ->select('id', 'firstname', 'lastname', 'admissionNo')
        ->firstOrFail();

    $result = DB::table('results')
        ->where('user_id', $studentId)
        ->where('exam_id', $examId)
        ->first();

    // Get all answers for this student and exam
    $answers = Answer::where('exam_id', $examId)
                     ->where('user_id', $studentId)
                     ->with('option')
                     ->get()
                     ->keyBy('question_id');

    $questionAnswers = [];
    $totalMarks = 0;
    $marksEarned = 0;
    $attempted = 0;
    $correct = 0;

    foreach ($exam->questions as $question) {
        $answer = $answers->get($question->id);

        // Get correct option for this question
        $correctOption = $question->options->where('is_correct', true)->first();

        // Determine if answer is correct
        $isCorrect = false;
        $studentAnswerText = '';
        $correctAnswerText = '';

        // Get correct answer text based on question type
        if ($correctOption) {
            if ($question->type === 'true_false') {
                // For true/false, use the label (true/false) but display as True/False
                $correctAnswerText = ucfirst($correctOption->label);
            } else {
                // For MCQ and short answer, use option_text
                $correctAnswerText = $correctOption->option_text;
            }
        }

        if ($answer) {
            $attempted++;

            if ($question->type === 'short_answer') {
                // For short answer, get answer_text
                $studentAnswerText = $answer->answer_text ?? '';
                if (!empty($studentAnswerText) && $correctOption) {
                    // Strip HTML tags for display
                    $studentAnswerText = strip_tags($studentAnswerText);
                    // Compare with correct answer (case-insensitive, trimmed)
                    $isCorrect = strtolower(trim($studentAnswerText)) === strtolower(trim($correctOption->option_text));
                }
            } elseif ($question->type === 'true_false') {
                // For true/false, get the selected option
                if ($answer->option) {
                    $studentAnswerText = ucfirst($answer->option->label);
                    $isCorrect = $answer->option->is_correct;
                } else {
                    // If option not found, check answer_text
                    $studentAnswerText = $answer->answer_text ?? '';
                    if (!empty($studentAnswerText) && $correctOption) {
                        $studentAnswerText = ucfirst(strtolower(trim($studentAnswerText)));
                        $isCorrect = $studentAnswerText === ucfirst($correctOption->label);
                    }
                }
            } else {
                // For MCQ, get the selected option
                if ($answer->option) {
                    $studentAnswerText = $answer->option->option_text;
                    $isCorrect = $answer->option->is_correct;
                } else {
                    // If option not found, check answer_text
                    $studentAnswerText = $answer->answer_text ?? '';
                    if (!empty($studentAnswerText) && $correctOption) {
                        $isCorrect = strtolower(trim($studentAnswerText)) === strtolower(trim($correctOption->option_text));
                    }
                }
            }
        } else {
            $studentAnswerText = 'Not Attempted';
        }

        // Clean up the student answer text
        if ($studentAnswerText !== 'Not Attempted') {
            $studentAnswerText = strip_tags($studentAnswerText);
            if (empty(trim($studentAnswerText))) {
                $studentAnswerText = 'Not Attempted';
            }
        }

        // Clean up the correct answer text
        if (!empty($correctAnswerText)) {
            $correctAnswerText = strip_tags($correctAnswerText);
        }

        // Calculate marks
        $questionMarks = (float)($question->marks ?? 1.0);
        $totalMarks += $questionMarks;

        if ($isCorrect) {
            $correct++;
            $marksEarned += $questionMarks;
        }

        $questionAnswers[] = (object)[
            'id' => $question->id,
            'question_text' => $question->question_text,
            'image' => $question->image,
            'type' => $question->type,
            'marks' => $questionMarks,
            'student_answer' => $studentAnswerText,
            'correct_answer' => $correctAnswerText,
            'answer_id' => $answer ? $answer->id : null,
            'status' => $answer ? ($isCorrect ? 'Correct' : 'Incorrect') : 'Not Attempted',
            'marks_earned' => $isCorrect ? $questionMarks : 0,
        ];
    }

    $totalQuestions = count($questionAnswers);

    $pagetitle = 'Exam Answers: ' . $student->firstname . ' ' . $student->lastname . ' - ' . $exam->title;

    return view('exam.student-answers', compact(
        'pagetitle',
        'exam',
        'student',
        'questionAnswers',
        'result',
        'totalQuestions',
        'attempted',
        'correct',
        'totalMarks',
        'marksEarned'
    ));
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
                'attempted' => $attemptedCount,
                'marks' => $question->marks ?? 1.0
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
