<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Answer;
use App\Models\Result;
use App\Models\Student;
use App\Models\Schoolterm;
use App\Models\ExamAttempt;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\StudentSubjectRecord;

class CBTController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('permission:View cbt-exam', ['only' => ['index']]);
        $this->middleware('permission:Take cbt-exam', ['only' => ['takeCBT']]);
        $this->middleware('permission:Submit cbt-exam', ['only' => ['submit']]);
    }

    public function index()
    {
        $pagetitle = 'CBT Management'; // Define the page title

        $studentId = auth()->user()->student_id;

        $studentClassData = DB::table('studentclass')
            ->where('studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->select(
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            )
            ->first();

        $student = DB::table('studentRegistration')
            ->where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'admissionNo')
            ->first();

        $current = 'Current';

        $totalreg = 0;
        $reg = 0;
        $registeredSubjects = [];
        $exams = collect(); // Default empty collection
        $attempts = []; // Initialize empty

        if ($studentClassData) {
            $totalreg = DB::table('subjectclass')
                ->where('schoolclassid', $studentClassData->class_id)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                // ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                // ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                // ->where('schoolsession.status', '=', $current)
                ->distinct('subjectteacher.subjectid')
                ->count('subjectteacher.subjectid');

            $reg = DB::table('student_subject_register_record')
                ->where('student_subject_register_record.studentId', $studentId)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                // ->where('schoolsession.status', '=', $current)
                ->count();

            $registeredSubjects = DB::table('student_subject_register_record')
                ->where('student_subject_register_record.studentId', $studentId)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                // ->where('schoolsession.status', '=', $current)
                ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->pluck('subjectteacher.id')
                ->toArray();

            $exams = DB::table('exams')
                ->whereIn('subject_id', $registeredSubjects)
                ->where('schoolclass_id', $studentClassData->class_id)
                // ->where('termid', 1)
                // ->where('session', $studentClassData->session_id)
                ->select('id', 'title', 'subject_id', 'description', 'duration', 'start_time', 'end_time')
                ->paginate(15);

            // Fetch attempted exam IDs for this student (efficient for pagination)
            $examIds = $exams->pluck('id')->toArray();
            $attempts = ExamAttempt::where('student_id', $studentId)
                ->whereIn('exam_id', $examIds)
                ->where('status', 'completed')  // Only count completed attempts
                ->pluck('exam_id')
                ->toArray();
        }

        $class = $studentClassData ? (object) ['id' => $studentClassData->class_id, 'schoolclass' => $studentClassData->class_name] : null;
        $term = $studentClassData ? (object) ['id' => $studentClassData->term_id, 'term' => $studentClassData->term_name] : null;
        $session = $studentClassData ? (object) ['id' => $studentClassData->session_id, 'session' => $studentClassData->session_name] : null;

        // NEW: AJAX Support for Pagination
    if (request()->ajax()) {
        return response()->view('cbt.partials.exams-table', [
            'exams' => $exams,
            'attempts' => $attempts,
            'student' => $student,
            'class' => $class,
            'term' => $term,
            'session' => $session,
            'totalreg' => $totalreg,
            'reg' => $reg,
        ]);
    }


        return view('cbt.index', [
            'pagetitle' => $pagetitle,
            'exams' => $exams,
            'student' => $student,
            'class' => $class,
            'term' => $term,
            'session' => $session,
            'totalreg' => $totalreg,
            'reg' => $reg,
            'attempts' => $attempts,  // Added: Pass attempts to view for $hasAttempted
        ]);
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
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function takeCBT($examid)
    {
        $pagetitle = 'CBT Exams'; // Define the page title
        try {
            // Get the authenticated student
            $student = auth()->user()->student_id;

            // Verify student has permission to take this exam
            $exam = Exam::where('id', $examid)
                ->with(['questions.options' => function ($query) {
                    $query->select('id', 'question_id', 'option_text', 'is_correct');
                }])
                ->firstOrFail();

            // Check if exam is currently available
            $now = Carbon::now();
            $startTime = Carbon::parse($exam->start_time);
            $endTime = Carbon::parse($exam->end_time);

            if (!$now->between($startTime, $endTime)) {
                return redirect()->route('cbt.index')->with('error', 'This exam is not currently available.');
            }

            // Check if student has already taken the exam
            $existingAttempt = ExamAttempt::where('student_id', $student)
                ->where('exam_id', $exam->id)
                ->first();

            if ($existingAttempt) {
                return redirect()->route('cbt.index')->with('error', 'You have already taken this exam.');
            }

            // Create new exam attempt
            $attempt = ExamAttempt::create([
                'student_id' => $student,
                'exam_id' => $exam->id,
                'start_time' => $now,
                'status' => 'in_progress'
            ]);

            // Prepare question data for frontend
            $questions = $exam->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'text' => $question->question_text,
                    'options' => $question->options->pluck('option_text')->toArray(),
                    'image_url' => $question->image ? asset('storage/' . $question->image) : null // Adjust path as needed
                ];
            })->toArray();

            // Get student registration details
            $studentReg = DB::table('studentRegistration')
                ->where('id', $student)
                ->select('id', 'firstname', 'lastname', 'admissionNo')
                ->first();

            $studentClassData = DB::table('studentclass')
                ->where('studentId', $student)
                ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
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
                return redirect()->route('cbt.index')->with('error', 'No registration found for this student.');
            }

            $class = (object) ['id' => $studentClassData->class_id, 'schoolclass' => $studentClassData->class_name];
            $term = (object) ['id' => $studentClassData->term_id, 'term' => $studentClassData->term_name];
            $session = (object) ['id' => $studentClassData->session_id, 'session' => $studentClassData->session_name];

            return view('cbt.take', [
                'pagetitle' => $pagetitle,
                'exam' => $exam,
                'questions' => $questions,
                'student' => $studentReg,
                'class' => $class,
                'term' => $term,
                'session' => $session,
                'attempt' => $attempt
            ]);

        } catch (\Exception $e) {
            return redirect()->route('cbt.index')
                ->with('error', 'An error occurred while loading the exam: ' . $e->getMessage());
        }
    }

    public function submit(Request $request)
    {
        try {
            Log::info('Submit request received', $request->all());

            $data = $request->validate([
                'attempt_id' => 'required|exists:exam_attempts,id',
                'exam_id' => 'required|exists:exams,id',
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answer' => 'nullable|string|max:255',
                'answers.*.notes' => 'nullable|string|max:1000',
            ]);

            $student = auth()->user()->student_id;
            if (!$student) {
                throw new \Exception('No authenticated student found');
            }
            Log::info('Student ID', ['student_id' => $student]);

            $attempt = ExamAttempt::findOrFail($data['attempt_id']);
            Log::info('Attempt found', ['attempt_id' => $attempt->id]);

            if ($attempt->student_id != $student || $attempt->exam_id != $data['exam_id']) {
                return response()->json(['success' => false, 'message' => 'Invalid attempt or exam'], 403);
            }

            if ($attempt->status === 'completed') {
                return response()->json(['success' => true, 'message' => 'Exam already submitted']);
            }

            // Check submission time
            $exam = Exam::with(['questions.options' => function ($query) {
                $query->select('id', 'question_id', 'option_text', 'is_correct');
            }])->findOrFail($data['exam_id']);
            $now = Carbon::now();
            $startTime = Carbon::parse($exam->start_time);
            $endTime = Carbon::parse($exam->end_time);
            if (!$now->between($startTime, $endTime)) {
                return response()->json(['success' => false, 'message' => 'Exam submission time has expired.'], 403);
            }

            $attempt->update([
                'end_time' => $now,
                'status' => 'completed'
            ]);
            Log::info('Attempt updated', ['attempt_id' => $attempt->id]);

            $totalMarks = $exam->questions->count();
            $score = 0;
            $attempted = 0;

            foreach ($data['answers'] as $submittedAnswer) {
                $question = $exam->questions->firstWhere('id', $submittedAnswer['question_id']);
                if ($question && !empty(trim($submittedAnswer['answer'] ?? ''))) {
                    $attempted++;
                    $selectedOption = $question->options->firstWhere('option_text', $submittedAnswer['answer']);
                    if ($selectedOption) {
                        Answer::create([
                            'user_id' => $student,
                            'exam_id' => $data['exam_id'],
                            'question_id' => $submittedAnswer['question_id'],
                            'option_id' => $selectedOption->id,
                        ]);
                        if ($selectedOption->is_correct) {
                            $score++;
                        }
                    }
                }
            }

            Result::create([
                'user_id' => $student,
                'exam_id' => $data['exam_id'],
                'score' => $score,
                'total_marks' => $totalMarks,
            ]);
            Log::info('Result saved', ['score' => $score, 'total_marks' => $totalMarks, 'attempted' => $attempted]);

            return response()->json(['success' => true, 'message' => 'Exam submitted successfully']);

        } catch (\Exception $e) {
            Log::error('Submission failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
