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

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View exam', ['only' => ['index', 'show', 'edit', 'showStudents', 'showStudentAnswers']]);
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

        $query = Exam::query();

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
            ])->sortBy('subject');

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
     * Show students who attempted the specified exam (completed or in_progress).
     */
    public function showStudents(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        $query = DB::table('exam_attempts')
            ->join('studentRegistration', 'exam_attempts.student_id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
            ->leftJoin('results', function ($join) use ($examId) {
                $join->on('exam_attempts.student_id', '=', 'results.user_id')
                     ->where('results.exam_id', '=', $examId);
            })
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('exam_attempts.status', ['completed', 'in_progress'])
            ->select(
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

        $students = $query->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($students);
        }

        $pagetitle = 'Students who Attempted: ' . $exam->title;

        return view('exam.students', compact('pagetitle', 'exam', 'students'));
    }

    /**
     * Delete a student's exam attempt so they can retake it.
     */
    public function deleteStudentAttempt($examId, $studentId)
    {
        $exam = Exam::findOrFail($examId);

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
        $exam = Exam::findOrFail($examId);

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
            'schoolclass_id' => 'required|integer',
            'is_published' => 'boolean|nullable',
        ]);
        
        // Handle the checkbox value (will be null if not checked)
        $validated['is_published'] = $request->has('is_published') ? true : false;
        
        $exam = Exam::create($validated);

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
        $exam = Exam::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'exam' => $exam
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
        $exam = Exam::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'termid' => 'required|integer',
            'session' => 'required|integer',
            'subject_id' => 'required|integer',
            'schoolclass_id' => 'required|integer',
            'is_published' => 'boolean|nullable',
        ]);
        
        // Handle the checkbox value
        $validated['is_published'] = $request->has('is_published') ? true : false;

        $exam->update($validated);

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
        $exam = Exam::findOrFail($id);
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
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No exams selected for deletion.'
            ], 400);
        }

        Exam::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' exams deleted successfully.'
        ]);
    }


  /**
     * Generate PDF question paper for a student's exam attempt.
     */
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
}