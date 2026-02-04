<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Models\Exam;
use App\Models\Answer;
use App\Models\Result;
use App\Models\ExamAttempt;
use App\Models\Schoolterm;
use App\Models\Schoolsession;

class CBTController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View cbt-exam', ['only' => ['index']]);
        $this->middleware('permission:Take cbt-exam', ['only' => ['takeCBT']]);
        $this->middleware('permission:Submit cbt-exam', ['only' => ['submit']]);
    }

    public function index(Request $request)
    {
        $pagetitle = 'CBT Management';

        $studentId = auth()->user()->student_id;

        $student = DB::table('studentRegistration')
            ->where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'admissionNo')
            ->first();

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

        $class      = $studentClassData ? (object) ['id' => $studentClassData->class_id,   'schoolclass' => $studentClassData->class_name]   : null;
        $termObj    = $studentClassData ? (object) ['id' => $studentClassData->term_id,    'term'       => $studentClassData->term_name]     : null;
        $sessionObj = $studentClassData ? (object) ['id' => $studentClassData->session_id, 'session'   => $studentClassData->session_name] : null;

        $terms    = Schoolterm::orderBy('id', 'desc')->get(['id', 'term']);
        $sessions = Schoolsession::orderBy('id', 'desc')->get(['id', 'session', 'status']);

        $selectedTermId    = $request->query('term');
        $selectedSessionId = $request->query('session');
        $search            = trim($request->query('search', ''));

        // Selected names for display
        $selectedTermName    = $selectedTermId ? Schoolterm::find($selectedTermId)?->term ?? 'Unknown Term' : null;
        $selectedSessionName = null;
        if ($selectedSessionId) {
            $sessionRecord = Schoolsession::select('session', 'status')->find($selectedSessionId);
            if ($sessionRecord) {
                $selectedSessionName = $sessionRecord->session;
                if ($sessionRecord->status) {
                    $selectedSessionName .= " ({$sessionRecord->status})";
                }
            }
        }

        $exams    = new LengthAwarePaginator(collect([]), 0, 15, 1, [
            'path'  => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);
        $attempts = [];
        $totalreg = 0;
        $reg      = 0;

        if ($selectedTermId && $selectedSessionId && $studentClassData) {

            $totalreg = DB::table('subjectclass')
                ->where('schoolclassid', $studentClassData->class_id)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->where('subjectteacher.sessionid', $selectedSessionId)
                ->where('subjectteacher.termid', $selectedTermId)
                ->distinct('subjectteacher.subjectid')
                ->count('subjectteacher.subjectid');

            $reg = DB::table('student_subject_register_record')
                ->where('student_subject_register_record.studentId', $studentId)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                ->count();

            // Get the student's registered subject IDs (from subject table, not subjectteacher)
            $registeredSubjectIds = DB::table('student_subject_register_record')
                ->where('student_subject_register_record.studentId', $studentId)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->pluck('subject.id') // Get subject IDs, not subjectteacher IDs
                ->unique()
                ->toArray();

            // Debug: Log the registered subjects
            \Log::info('Registered Subject IDs for student', [
                'student_id' => $studentId,
                'registered_subjects' => $registeredSubjectIds,
                'class_id' => $studentClassData->class_id,
                'term_id' => $selectedTermId,
                'session_id' => $selectedSessionId
            ]);

            // Fetch exams based on the student's class, term, session, and registered subjects
            $examsQuery = Exam::where('schoolclass_id', $studentClassData->class_id)
                ->where('termid', $selectedTermId)
                ->where('session', $selectedSessionId);

            // If student has registered subjects, filter by them
            if (!empty($registeredSubjectIds)) {
                $examsQuery->whereIn('subject_id', $registeredSubjectIds);
            } else {
                // If no subjects registered, show no exams
                $examsQuery->whereRaw('1=0'); // Force no results
            }

            if ($search !== '') {
                $examsQuery->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $exams = $examsQuery
                ->select('id', 'title', 'subject_id', 'description', 'duration', 'start_time', 'end_time')
                ->with(['subject:id,subject']) // Load subject name
                ->paginate(15)
                ->appends($request->query());

            // Debug: Log the exams found
            \Log::info('Exams found for student', [
                'student_id' => $studentId,
                'exams_count' => $exams->count(),
                'exams' => $exams->pluck('id')->toArray()
            ]);

            $examIds = $exams->pluck('id')->toArray();

            $attempts = ExamAttempt::where('student_id', $studentId)
                ->whereIn('exam_id', $examIds)
                ->where('status', 'completed')
                ->pluck('exam_id')
                ->toArray();
        }

        if ($request->ajax()) {
            return response()->view('cbt.partials.exams-table', compact(
                'exams', 'attempts', 'student', 'class', 'termObj', 'sessionObj', 'totalreg', 'reg'
            ));
        }

        return view('cbt.index', compact(
            'pagetitle',
            'student',
            'class',
            'termObj',
            'sessionObj',
            'terms',
            'sessions',
            'selectedTermId',
            'selectedSessionId',
            'selectedTermName',
            'selectedSessionName',
            'exams',
            'attempts',
            'totalreg',
            'reg'
        ));
    }

    public function takeCBT($examid)
    {
        $pagetitle = 'CBT Exams';

        try {
            $student = auth()->user()->student_id;

            $exam = Exam::where('id', $examid)
                ->with(['questions.options' => function ($query) {
                    $query->select('id', 'question_id', 'option_text', 'is_correct');
                }])
                ->firstOrFail();

            $now       = Carbon::now();
            $startTime = Carbon::parse($exam->start_time);
            $endTime   = Carbon::parse($exam->end_time);

            if (!$now->between($startTime, $endTime)) {
                return redirect()->route('cbt.index')->with('error', 'This exam is not currently available.');
            }

            $existingAttempt = ExamAttempt::where('student_id', $student)
                ->where('exam_id', $exam->id)
                ->first();

            if ($existingAttempt) {
                return redirect()->route('cbt.index')->with('error', 'You have already taken this exam.');
            }

            $attempt = ExamAttempt::create([
                'student_id' => $student,
                'exam_id'    => $exam->id,
                'start_time' => $now,
                'status'     => 'in_progress'
            ]);

            // Include marks and type in questions data
            $questions = $exam->questions->map(function ($question) {
                // Get the correct option for True/False and Short Answer
                $correctOption = $question->options->where('is_correct', true)->first();

                return [
                    'id'        => $question->id,
                    'text'      => $question->question_text,
                    'type'      => $question->type,
                    'options'   => $question->options->map(function($option) {
                        return [
                            'id' => $option->id,
                            'text' => $option->option_text,
                            'is_correct' => $option->is_correct
                        ];
                    })->toArray(),
                    'correct_answer' => $correctOption ? $correctOption->option_text : '',
                    'image_url' => $question->image ? asset('storage/' . $question->image) : null,
                    'marks'     => (float) ($question->marks ?? 1.0), // Use float value with default 1.0
                ];
            })->toArray();

            // Calculate total marks - ensure we're using float values
            $totalExamMarks = $exam->questions->sum('marks');

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

            $class   = (object) ['id' => $studentClassData->class_id,   'schoolclass' => $studentClassData->class_name];
            $term    = (object) ['id' => $studentClassData->term_id,    'term'       => $studentClassData->term_name];
            $session = (object) ['id' => $studentClassData->session_id, 'session'   => $studentClassData->session_name];

            return view('cbt.take', compact(
                'pagetitle',
                'exam',
                'questions',
                'studentReg',
                'class',
                'term',
                'session',
                'attempt',
                'totalExamMarks'
            ));

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
                'attempt_id'            => 'required|exists:exam_attempts,id',
                'exam_id'               => 'required|exists:exams,id',
                'answers'               => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answer'      => 'nullable|string|max:1000',
                'answers.*.notes'       => 'nullable|string|max:1000',
            ]);

            $student = auth()->user()->student_id;
            if (!$student) {
                throw new \Exception('No authenticated student found');
            }

            $attempt = ExamAttempt::findOrFail($data['attempt_id']);

            if ($attempt->student_id != $student || $attempt->exam_id != $data['exam_id']) {
                return response()->json(['success' => false, 'message' => 'Invalid attempt or exam'], 403);
            }

            if ($attempt->status === 'completed') {
                return response()->json(['success' => true, 'message' => 'Exam already submitted']);
            }

            $exam = Exam::with(['questions.options' => function ($query) {
                $query->select('id', 'question_id', 'option_text', 'is_correct');
            }])->findOrFail($data['exam_id']);

            $now       = Carbon::now();
            $startTime = Carbon::parse($exam->start_time);
            $endTime   = Carbon::parse($exam->end_time);

            if (!$now->between($startTime, $endTime)) {
                return response()->json(['success' => false, 'message' => 'Exam submission time has expired.'], 403);
            }

            $attempt->update([
                'end_time' => $now,
                'status'   => 'completed'
            ]);

            // Calculate marks
            $totalMarks = 0;
            $score = 0;
            $attempted = 0;

            foreach ($data['answers'] as $submittedAnswer) {
                $question = $exam->questions->firstWhere('id', $submittedAnswer['question_id']);
                if ($question) {
                    Log::info('Processing answer', [
                        'question_id' => $question->id,
                        'question_type' => $question->type,
                        'student_answer' => $submittedAnswer['answer'] ?? null,
                        'question_marks' => $question->marks,
                    ]);

                    // Add question marks to total
                    $questionMarks = (float) ($question->marks ?? 1.0);
                    $totalMarks += $questionMarks;

                    $studentAnswer = trim($submittedAnswer['answer'] ?? '');

                    if (!empty($studentAnswer)) {
                        $attempted++;

                        // Initialize variables
                        $optionId = null;
                        $shortAnswerText = null;

                        if ($question->type === 'short_answer') {
                            // For short answer questions
                            $shortAnswerText = $studentAnswer;

                            // Find correct option for comparison and scoring
                            $correctOption = $question->options->where('is_correct', true)->first();
                            if ($correctOption) {
                                $correctAnswer = trim($correctOption->option_text);

                                Log::info('Short answer comparison', [
                                    'student_answer' => $studentAnswer,
                                    'correct_answer' => $correctAnswer,
                                    'type' => $question->type
                                ]);

                                // Case-insensitive comparison for scoring
                                if (strtolower($studentAnswer) === strtolower($correctAnswer)) {
                                    $score += $questionMarks;
                                } else {
                                    // Optional: Allow partial matches
                                    similar_text(strtolower($studentAnswer), strtolower($correctAnswer), $percent);
                                    if ($percent >= 80) { // 80% similarity threshold
                                        $score += $questionMarks;
                                    }
                                }
                            }
                            // For short answer, option_id remains null
                        } else {
                            // For MCQ and True/False questions
                            $shortAnswerText = null;

                            // Find the selected option
                            $selectedOption = $question->options->first(function($option) use ($studentAnswer) {
                                return trim($option->option_text) === $studentAnswer;
                            });

                            if ($selectedOption) {
                                $optionId = $selectedOption->id;
                                if ($selectedOption->is_correct) {
                                    $score += $questionMarks;
                                }
                            }
                        }

                        Log::info('Creating answer record', [
                            'question_id' => $question->id,
                            'question_type' => $question->type,
                            'option_id' => $optionId,
                            'short_answer' => $shortAnswerText,
                            'question_marks' => $questionMarks,
                            'score_added' => $score
                        ]);

                        // Create answer record
                        Answer::create([
                            'user_id'       => $student,
                            'exam_id'       => $data['exam_id'],
                            'question_id'   => $submittedAnswer['question_id'],
                            'option_id'     => $optionId, // null for short answers
                            'short_answer'  => $shortAnswerText, // Store short answer text here
                        ]);
                    }
                }
            }

            // Create or update result
            Result::updateOrCreate(
                [
                    'user_id' => $student,
                    'exam_id' => $data['exam_id'],
                ],
                [
                    'score'           => $score,
                    'total_marks'     => $totalMarks,
                    'percentage'      => $totalMarks > 0 ? ($score / $totalMarks) * 100 : 0,
                    'attempted'       => $attempted,
                    'total_questions' => $exam->questions->count(),
                ]
            );

            Log::info('Result saved', [
                'score'       => $score,
                'total_marks' => $totalMarks,
                'percentage'  => $totalMarks > 0 ? ($score / $totalMarks) * 100 : 0,
                'attempted'   => $attempted
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exam submitted successfully',
                'score'   => $score,
                'total_marks' => $totalMarks,
                'percentage' => $totalMarks > 0 ? round(($score / $totalMarks) * 100, 2) : 0,
                'attempted' => $attempted,
                'total_questions' => $exam->questions->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // CRUD stubs
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
