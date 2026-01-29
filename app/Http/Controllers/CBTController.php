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

        // $totalreg = DB::table('subjectclass')
        //     ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
        //     ->where('subjectclass.schoolclassid', $studentClassData->class_id)
        //     ->where('subjectteacher.sessionid', $selectedSessionId)
        //     ->where('subjectteacher.termid', $selectedTermId)
        //     ->distinct()
        //     ->count('subjectteacher.subjectid');

        $totalreg = DB::table('subjectclass')
                ->where('schoolclassid', $studentClassData->class_id)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                // ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                // ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                // ->where('schoolsession.status', '=', $current)
                ->distinct('subjectteacher.subjectid')
                ->count('subjectteacher.subjectid');

        // $reg = DB::table('student_subject_register_record')
        //     ->where('student_subject_register_record.studentId', $studentId)
        //     ->where('student_subject_register_record.session', $selectedSessionId)
        //     ->count();

             $reg = DB::table('student_subject_register_record')
                ->where('student_subject_register_record.studentId', $studentId)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                // ->where('schoolsession.status', '=', $current)
                ->count();

        // $registeredSubjects = DB::table('student_subject_register_record')
        //     ->where('student_subject_register_record.studentId', $studentId)
        //     ->where('student_subject_register_record.session', $selectedSessionId)
        //     ->join('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
        //     ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
        //     ->where('subjectteacher.sessionid', $selectedSessionId)
        //     ->where('subjectteacher.termid', $selectedTermId)
        //     ->distinct()
        //     ->pluck('subjectteacher.subjectid')
        //     ->toArray();


        $registeredSubjects = DB::table('student_subject_register_record')
                ->where('student_subject_register_record.studentId', $studentId)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                // ->where('schoolsession.status', '=', $current)
                ->where('subjectteacher.sessionid', $selectedSessionId)
                // ->where('subjectteacher.termid', $selectedTermId)
                ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->pluck('subjectteacher.id')
                ->toArray();

        $examsQuery = DB::table('exams')
            ->whereIn('subject_id', $registeredSubjects ?: [0])
            ->where('schoolclass_id', $studentClassData->class_id)
            // ->where('termid', $selectedTermId)
            ->where('session', $selectedSessionId);



        if ($search !== '') {
            $examsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $exams = $examsQuery
            ->select('id', 'title', 'subject_id', 'description', 'duration', 'start_time', 'end_time')
            ->paginate(15)
            ->appends($request->query());

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

            $questions = $exam->questions->map(function ($question) {
                return [
                    'id'        => $question->id,
                    'text'      => $question->question_text,
                    'options'   => $question->options->pluck('option_text')->toArray(),
                    'image_url' => $question->image ? asset('storage/' . $question->image) : null
                ];
            })->toArray();

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
                'attempt'
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
                'answers.*.answer'      => 'nullable|string|max:255',
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

            $totalMarks = $exam->questions->count();
            $score      = 0;
            $attempted  = 0;

            foreach ($data['answers'] as $submittedAnswer) {
                $question = $exam->questions->firstWhere('id', $submittedAnswer['question_id']);
                if ($question && !empty(trim($submittedAnswer['answer'] ?? ''))) {
                    $attempted++;
                    $selectedOption = $question->options->firstWhere('option_text', $submittedAnswer['answer']);
                    if ($selectedOption) {
                        Answer::create([
                            'user_id'     => $student,
                            'exam_id'     => $data['exam_id'],
                            'question_id' => $submittedAnswer['question_id'],
                            'option_id'   => $selectedOption->id,
                        ]);
                        if ($selectedOption->is_correct) {
                            $score++;
                        }
                    }
                }
            }

            Result::create([
                'user_id'     => $student,
                'exam_id'     => $data['exam_id'],
                'score'       => $score,
                'total_marks' => $totalMarks,
            ]);

            Log::info('Result saved', [
                'score'       => $score,
                'total_marks' => $totalMarks,
                'attempted'   => $attempted
            ]);

            return response()->json(['success' => true, 'message' => 'Exam submitted successfully']);

        } catch (\Exception $e) {
            Log::error('Submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // CRUD stubs (empty)
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
