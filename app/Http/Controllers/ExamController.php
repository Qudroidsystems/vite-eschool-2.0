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
use App\Models\Assessment;
use App\Models\SubAssessment;
use App\Models\Broadsheets;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetAssessmentScore;
use App\Models\BroadsheetSubAssessmentScore;
use App\Models\Subjectclass;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // First, get terms and sessions for dropdowns
        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        // Query exams with all necessary relationships including term and session
        $query = Exam::query()
            ->with([
                'schoolclass' => function($query) {
                    $query->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                          ->select('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm');
                },
                'subject:id,subject,subject_code',
                'termRelation:id,term',
                'sessionRelation:id,session'
            ])
            ->withCount('questions')
            ->where('staffId', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('subject', function($q) use ($search) {
                      $q->where('subject', 'like', "%{$search}%");
                  });
            });
        }

        // Apply sorting
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $exams = $query->paginate(15);

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

        // Calculate statistics for the dashboard
        $totalExams = $exams->total();
        $totalQuestions = $exams->sum('questions_count');
        $totalClasses = $myclass->count();
        $totalSubjects = $mysubjects->count();

        $pagetitle = 'Exams Management';

        return view('exam.index', compact(
            'pagetitle',
            'exams',
            'terms',
            'sessions',
            'mysubjects',
            'myclass',
            'totalExams',
            'totalQuestions',
            'totalClasses',
            'totalSubjects'
        ));
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

            // Validate duration against start and end times
            $startTime = strtotime($validated['start_time']);
            $endTime = strtotime($validated['end_time']);
            $durationMinutes = $validated['duration'];
            $totalMinutes = round(($endTime - $startTime) / 60);

            if ($durationMinutes > $totalMinutes) {
                return response()->json([
                    'success' => false,
                    'message' => "Duration ({$durationMinutes} minutes) exceeds the time between start and end ({$totalMinutes} minutes). Please adjust.",
                    'errors' => ['duration' => 'Duration exceeds available time']
                ], 422);
            }

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
            Log::error('Error creating exam: ' . $e->getMessage());

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
        // This method now shows questions for the exam
        $user = Auth::user();

        $exam = Exam::with([
                'schoolclass' => function($query) {
                    $query->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                          ->select('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm');
                },
                'subject:id,subject',
                'termRelation:id,term',
                'sessionRelation:id,session'
            ])
            ->where('id', $id)
            ->where('staffId', $user->id)
            ->firstOrFail();

        // Get questions for this exam with options
        $questions = Question::with('options')
            ->where('exam_id', $id)
            ->orderBy('order')
            ->get();

        // Get term and session for display
        $term = Schoolterm::find($exam->termid);
        $session = Schoolsession::find($exam->session);

        $pagetitle = 'Questions for: ' . $exam->title;

        return view('questions.show', compact(
            'pagetitle',
            'exam',
            'questions',
            'term',
            'session'
        ));
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
            Log::info('Update request received:', [
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
                'copy_questions'  => 'boolean|nullable',
                'copy_all_questions' => 'boolean|nullable',
                'selected_questions' => 'nullable|array',
                'selected_questions.*' => 'integer|exists:questions,id',
            ]);

            // Validate duration against start and end times
            $startTime = strtotime($validated['start_time']);
            $endTime = strtotime($validated['end_time']);
            $durationMinutes = $validated['duration'];
            $totalMinutes = round(($endTime - $startTime) / 60);

            if ($durationMinutes > $totalMinutes) {
                return response()->json([
                    'success' => false,
                    'message' => "Duration ({$durationMinutes} minutes) exceeds the time between start and end ({$totalMinutes} minutes). Please adjust.",
                    'errors' => ['duration' => 'Duration exceeds available time']
                ], 422);
            }

            $validated['is_published'] = $request->has('is_published');
            $validated['staffId'] = $exam->staffId;

            Log::info('Validated data:', $validated);

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

            Log::info('Original class IDs:', $originalClassIds);
            Log::info('New class IDs:', $newClassIds);

            // Update existing exams
            $updatedCount = 0;
            $createdCount = 0;
            $copiedQuestionsCount = 0;

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
                    Log::info("Updated existing exam for class {$classId}");
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
                Log::info("Created new exam for class {$classId}");

                // Copy questions to the new exam if requested
                if ($request->has('copy_questions') && $request->copy_questions) {
                    $sourceExam = $exam; // Use the current exam as source

                    if ($request->has('copy_all_questions') && $request->copy_all_questions) {
                        // Copy all questions from source exam
                        $copiedCount = $this->copyQuestionsToExam($sourceExam->id, $newExam->id);
                        $copiedQuestionsCount += $copiedCount;
                        Log::info("Copied {$copiedCount} questions to new exam for class {$classId}");
                    } elseif ($request->has('selected_questions') && !empty($request->selected_questions)) {
                        // Copy only selected questions
                        $copiedCount = $this->copySelectedQuestionsToExam($sourceExam->id, $newExam->id, $request->selected_questions);
                        $copiedQuestionsCount += $copiedCount;
                        Log::info("Copied {$copiedCount} selected questions to new exam for class {$classId}");
                    }
                }
            }

            $message = "Exam updated successfully. ";
            if ($updatedCount > 0) {
                $message .= "Updated {$updatedCount} existing class" . ($updatedCount > 1 ? 'es' : '') . ". ";
            }
            if ($createdCount > 0) {
                $message .= "Added {$createdCount} new class" . ($createdCount > 1 ? 'es' : '') . ". ";
                if ($copiedQuestionsCount > 0) {
                    $message .= "Copied {$copiedQuestionsCount} questions to new class" . ($createdCount > 1 ? 'es' : '') . ". ";
                }
            }

            Log::info($message);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('exams.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error updating exam: ' . $e->getMessage());

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

    /**
     * Copy all questions from source exam to target exam
     */
    private function copyQuestionsToExam($sourceExamId, $targetExamId)
    {
        $copiedCount = 0;

        // Get all questions from source exam with their options
        $questions = Question::with('options')
            ->where('exam_id', $sourceExamId)
            ->orderBy('order')
            ->get();

        foreach ($questions as $question) {
            // Get next order number for target exam
            $order = Question::where('exam_id', $targetExamId)->max('order') + 1;

            // Create new question
            $newQuestion = Question::create([
                'exam_id' => $targetExamId,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'image' => $question->image,
                'marks' => $question->marks,
                'order' => $order,
                'is_reusable' => $question->is_reusable,
            ]);

            // Copy options
            foreach ($question->options as $option) {
                $newQuestion->options()->create([
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                    'label' => $option->label,
                ]);
            }

            $copiedCount++;
        }

        return $copiedCount;
    }

    /**
     * Copy selected questions from source exam to target exam
     */
    private function copySelectedQuestionsToExam($sourceExamId, $targetExamId, $selectedQuestionIds)
    {
        $copiedCount = 0;

        // Get selected questions from source exam with their options
        $questions = Question::with('options')
            ->where('exam_id', $sourceExamId)
            ->whereIn('id', $selectedQuestionIds)
            ->orderBy('order')
            ->get();

        foreach ($questions as $question) {
            // Get next order number for target exam
            $order = Question::where('exam_id', $targetExamId)->max('order') + 1;

            // Create new question
            $newQuestion = Question::create([
                'exam_id' => $targetExamId,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'image' => $question->image,
                'marks' => $question->marks,
                'order' => $order,
                'is_reusable' => $question->is_reusable,
            ]);

            // Copy options
            foreach ($question->options as $option) {
                $newQuestion->options()->create([
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                    'label' => $option->label,
                ]);
            }

            $copiedCount++;
        }

        return $copiedCount;
    }

    /**
     * Get questions for a specific exam to display in copy modal
     */
    public function getExamQuestions($examId)
    {
        try {
            $exam = Exam::where('id', $examId)
                ->where('staffId', auth()->user()->id)
                ->firstOrFail();

            $questions = Question::where('exam_id', $examId)
                ->orderBy('order')
                ->get()
                ->map(function($question) {
                    return [
                        'id' => $question->id,
                        'text' => strip_tags($question->question_text),
                        'type' => $question->type,
                        'marks' => $question->marks,
                        'options_count' => $question->options()->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'exam_title' => $exam->title
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting exam questions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load questions'
            ], 500);
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
            Log::error('Error deleting exam: ' . $e->getMessage());

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
            Log::error('Error bulk deleting exams: ' . $e->getMessage());
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
            Log::error('Error getting filtered subjects: ' . $e->getMessage());
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
            Log::error('Error getting classes for subject: ' . $e->getMessage());
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
                ->with(['schoolclass', 'termRelation:id,term', 'sessionRelation:id,session', 'subject:id,subject'])
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

        // Main query - updated to join with studentclass table
        $query = DB::table('exam_attempts')
            ->join('studentRegistration', 'exam_attempts.student_id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
            ->leftJoin('studentclass', function ($join) {
                $join->on('studentRegistration.id', '=', 'studentclass.studentId')
                     ->where('studentclass.sessionid', function ($q) {
                         // Get current or most recent session
                         $q->select('id')
                           ->from('schoolsession')
                           ->where('status', 'Current')
                           ->orWhereRaw('id = (SELECT MAX(id) FROM schoolsession)')
                           ->limit(1);
                     })
                     ->where('studentclass.termid', function ($q) {
                         // Get current or most recent term
                         $q->select('id')
                           ->from('schoolterm')
                           ->where('status', 'Current')
                           ->orWhereRaw('id = (SELECT MAX(id) FROM schoolterm)')
                           ->limit(1);
                     });
            })
            ->leftJoin('results', function ($join) use ($examId) {
                $join->on('exam_attempts.student_id', '=', 'results.user_id')
                     ->where('results.exam_id', '=', $examId);
            })
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('exam_attempts.status', ['completed', 'in_progress']);

        // Apply class filter if specified
        if ($classId) {
            $query->where('studentclass.schoolclassid', $classId);
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
            'studentclass.schoolclassid' // Include class ID for reference
        );

        $students = $query->orderBy('studentRegistration.lastname')->paginate(15);

        // Get correct options for all questions in this exam
        $correctOptions = DB::table('options')
            ->join('questions', 'options.question_id', '=', 'questions.id')
            ->where('questions.exam_id', $examId)
            ->where('options.is_correct', true)
            ->select('options.id', 'options.question_id', 'options.option_text')
            ->get()
            ->keyBy('question_id');

        // Get all questions with their marks and types
        $questions = DB::table('questions')
            ->where('exam_id', $examId)
            ->select('id', 'marks', 'type')
            ->get()
            ->keyBy('id');

        // Now calculate for each student
        foreach ($students as $student) {
            // Skip calculation for in_progress attempts
            if ($student->attempt_status === 'in_progress') {
                $student->attempted_questions = 0;
                $student->correct_count = 0;
                $student->marks_earned = 0;
                $student->incorrect = 0;
                $student->score = 0;
                continue;
            }

            // Get all answers for this student
            $answers = Answer::where('exam_id', $examId)
                            ->where('user_id', $student->id)
                            ->with(['option'])
                            ->get();

            $attempted = 0;
            $correctCount = 0;
            $marksEarned = 0;

            foreach ($answers as $answer) {
                $attempted++;

                $question = $questions->get($answer->question_id);
                $questionMarks = (float) ($question->marks ?? 1.0);
                $correctOption = $correctOptions->get($answer->question_id);

                if ($question && $question->type === 'short_answer') {
                    // For short answers, use consistent normalization
                    $studentAnswer = trim(strip_tags($answer->short_answer ?? ''));
                    $correctAnswer = trim(strip_tags($correctOption->option_text ?? ''));

                    if (!empty($studentAnswer) && !empty($correctAnswer)) {
                        // Normalize for comparison
                        $normalizedStudent = $this->normalizeTextForComparison($studentAnswer);
                        $normalizedCorrect = $this->normalizeTextForComparison($correctAnswer);

                        if ($normalizedStudent === $normalizedCorrect) {
                            $correctCount++;
                            $marksEarned += $questionMarks;
                        }
                    }
                } else {
                    // For MCQ/TrueFalse
                    if ($answer->option && $answer->option->is_correct) {
                        $correctCount++;
                        $marksEarned += $questionMarks;
                    }
                }
            }

            // Add calculated fields to student object
            $student->attempted_questions = $attempted;
            $student->correct_count = $correctCount;
            $student->marks_earned = $marksEarned;
            $student->incorrect = $attempted - $correctCount;

            // Use calculated marks_earned for score
            $student->score = $marksEarned;
            $student->total_marks = $examTotal->total_marks ?? 0;

            // Update results table if student has completed the exam
            if ($student->attempt_status === 'completed') {
                DB::table('results')
                    ->updateOrInsert(
                        [
                            'user_id' => $student->id,
                            'exam_id' => $examId
                        ],
                        [
                            'score' => $marksEarned,
                            'total_marks' => $examTotal->total_marks ?? 0,
                            'updated_at' => now()
                        ]
                    );
            }
        }

        // Get assigned classes for the exam group
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

        // Get term and session for display
        $term = Schoolterm::find($exam->termid);
        $session = Schoolsession::find($exam->session);

        if ($request->ajax()) {
            return response()->json($students);
        }

        $pagetitle = 'Students who Attempted: ' . $exam->title;

        return view('exam.students', compact(
            'pagetitle',
            'exam',
            'students',
            'assignedClasses',
            'classId',
            'examTotals',
            'term',
            'session'
        ));
    }

    // Helper method for consistent text normalization
    private function normalizeTextForComparison($text)
    {
        $text = trim($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces with single space
        $text = strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // Remove punctuation
        return $text;
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
            Log::error("Error deleting student attempt: " . $e->getMessage());

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
                    // Use short_answer column
                    $studentAnswerText = $answer->short_answer ?? '';

                    if (!empty($studentAnswerText) && $correctOption) {
                        // Clean the student answer
                        $studentAnswerText = strip_tags($studentAnswerText);

                        // Clean the correct answer
                        $cleanCorrectAnswer = strip_tags($correctOption->option_text);

                        // Use the same normalization as showStudents
                        $normalizedStudent = $this->normalizeTextForComparison($studentAnswerText);
                        $normalizedCorrect = $this->normalizeTextForComparison($cleanCorrectAnswer);

                        // Compare normalized text
                        $isCorrect = $normalizedStudent === $normalizedCorrect;
                    }
                } elseif ($question->type === 'true_false') {
                    // For true/false, get the selected option
                    if ($answer->option) {
                        $studentAnswerText = ucfirst($answer->option->label);
                        $isCorrect = $answer->option->is_correct;
                    } else {
                        // If option not found, check short_answer (for backward compatibility)
                        $studentAnswerText = $answer->short_answer ?? '';
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
                        // If option not found, check short_answer (for backward compatibility)
                        $studentAnswerText = $answer->short_answer ?? '';
                        if (!empty($studentAnswerText) && $correctOption) {
                            $cleanStudentAnswer = trim(strip_tags($studentAnswerText));
                            $cleanCorrectAnswer = trim(strip_tags($correctOption->option_text));
                            $isCorrect = strtolower($cleanStudentAnswer) === strtolower($cleanCorrectAnswer);
                        }
                    }
                }
            } else {
                $studentAnswerText = 'Not Attempted';
            }

            // Clean up the student answer text for display
            if ($studentAnswerText !== 'Not Attempted') {
                $studentAnswerText = strip_tags($studentAnswerText);
                if (empty(trim($studentAnswerText))) {
                    $studentAnswerText = 'Not Attempted';
                }
            }

            // Clean up the correct answer text for display
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

            // Use short_answer field for short answers
            if ($question->type === 'short_answer') {
                $question->student_answer = $studentAnswer ? ($studentAnswer->short_answer ?? 'Not Attempted') : 'Not Attempted';
            } else {
                $question->student_answer = $studentAnswer ? ($studentAnswer->option->option_text ?? 'Not Attempted') : 'Not Attempted';
            }

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


    /**
 * Get available assessments for the exam's class
 */
public function getAssessments($examId)
{
    try {
        Log::info('Fetching assessments for exam', ['exam_id' => $examId, 'user_id' => auth()->id()]);

        $exam = Exam::where('id', $examId)
            ->where('staffId', auth()->user()->id)
            ->with(['schoolclass', 'subject'])
            ->firstOrFail();

        // Get the class category for this exam's class
        $schoolclass = Schoolclass::with('classcategories')
            ->find($exam->schoolclass_id);

        if (!$schoolclass) {
            Log::warning('School class not found', ['schoolclass_id' => $exam->schoolclass_id]);
            return response()->json([
                'success' => false,
                'message' => 'School class not found'
            ]);
        }

        if ($schoolclass->classcategories->isEmpty()) {
            Log::warning('No class categories found', ['schoolclass_id' => $exam->schoolclass_id]);
            return response()->json([
                'success' => false,
                'message' => 'No class category found for this class'
            ]);
        }

        $categoryIds = $schoolclass->classcategories->pluck('id');
        Log::info('Found class categories', ['category_ids' => $categoryIds]);

        // Get all assessments for this class category with their sub-assessments
        $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
            ->with(['subAssessments' => function($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        Log::info('Found assessments', ['count' => $assessments->count()]);

        // Get current term and session from the exam
        $term = Schoolterm::find($exam->termid);
        $session = Schoolsession::find($exam->session);

        return response()->json([
            'success' => true,
            'assessments' => $assessments,
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject->subject ?? 'N/A',
                'class' => $schoolclass->schoolclass . ' ' . ($schoolclass->arm ?? ''),
                'term' => $term->term ?? 'N/A',
                'session' => $session->session ?? 'N/A'
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Error getting assessments: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'exam_id' => $examId
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to load assessments: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Update student's exam score to assessment scoresheet
 */
public function updateAssessmentScore(Request $request)
{
    try {
        Log::info('========== ASSESSMENT SCORE TRANSFER ==========');
        Log::info('Request data:', $request->all());

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:studentRegistration,id',
            'assessment_id' => 'required|exists:assessments,id',
            'sub_assessment_id' => 'nullable|exists:sub_assessments,id',
            'score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:0',
            'is_sub' => 'boolean'
        ]);

        $isSub = $validated['is_sub'] ?? false;

        if ($validated['score'] > $validated['max_score']) {
            return response()->json([
                'success' => false,
                'message' => "Score cannot exceed maximum of {$validated['max_score']}"
            ], 422);
        }

        DB::beginTransaction();

        // Get the exam
        $exam = Exam::with(['subject', 'schoolclass'])->find($validated['exam_id']);

        if (!$exam) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Exam not found'
            ], 404);
        }

        // Get student details
        $student = DB::table('studentRegistration')
            ->where('id', $validated['student_id'])
            ->first();

        // Get assessment details
        $assessment = Assessment::with('subAssessments')->find($validated['assessment_id']);

        // IMPORTANT: Find the subjectclass that matches what the scoresheet uses
        // From logs, scoresheet uses subjectclass_id = 5 for this class
        $subjectclass = Subjectclass::where('subjectid', $exam->subject_id)
            ->where('schoolclassid', $exam->schoolclass_id)
            ->first();

        if (!$subjectclass) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Subject class configuration not found'
            ], 404);
        }

        Log::info('Using subjectclass_id:', ['id' => $subjectclass->id]);

        // First, check if there's an existing broadsheet with this subjectclass_id
        // that matches what the scoresheet is using
        $broadsheet = Broadsheets::whereHas('broadsheetRecord', function($q) use ($validated, $exam) {
                $q->where('student_id', $validated['student_id'])
                  ->where('session_id', $exam->session)
                  ->where('subject_id', $exam->subject_id)
                  ->where('schoolclass_id', $exam->schoolclass_id);
            })
            ->where('subjectclass_id', $subjectclass->id)  // Use the correct subjectclass_id
            ->where('staff_id', auth()->user()->id)
            ->where('term_id', $exam->termid)
            ->first();

        if (!$broadsheet) {
            Log::info('No existing broadsheet found with subjectclass_id: ' . $subjectclass->id);

            // Check if there's a broadsheet with a different subjectclass_id
            $otherBroadsheet = Broadsheets::whereHas('broadsheetRecord', function($q) use ($validated, $exam) {
                    $q->where('student_id', $validated['student_id'])
                      ->where('session_id', $exam->session)
                      ->where('subject_id', $exam->subject_id)
                      ->where('schoolclass_id', $exam->schoolclass_id);
                })
                ->where('staff_id', auth()->user()->id)
                ->where('term_id', $exam->termid)
                ->first();

            if ($otherBroadsheet) {
                Log::info('Found broadsheet with different subjectclass_id', [
                    'old_id' => $otherBroadsheet->subjectclass_id,
                    'new_id' => $subjectclass->id,
                    'broadsheet_id' => $otherBroadsheet->id
                ]);

                // Update the subjectclass_id to match what scoresheet uses
                $otherBroadsheet->subjectclass_id = $subjectclass->id;
                $otherBroadsheet->save();
                $broadsheet = $otherBroadsheet;
            } else {
                // Create new broadsheet record
                $broadsheetRecord = BroadsheetRecord::firstOrCreate(
                    [
                        'student_id' => $validated['student_id'],
                        'session_id' => $exam->session,
                        'subject_id' => $exam->subject_id,
                        'schoolclass_id' => $exam->schoolclass_id,
                    ]
                );

                $broadsheet = Broadsheets::create([
                    'broadSheet_record_id' => $broadsheetRecord->id,
                    'subjectclass_id' => $subjectclass->id,
                    'staff_id' => auth()->user()->id,
                    'term_id' => $exam->termid,
                    'total' => 0,
                    'bf' => 0,
                    'cum' => 0,
                    'vettedstatus' => 0
                ]);
            }
        }

        Log::info('Using broadsheet_id:', ['id' => $broadsheet->id]);

        // Save the score
        if ($isSub && !empty($validated['sub_assessment_id'])) {
            BroadsheetSubAssessmentScore::updateOrCreate(
                [
                    'broadsheet_id' => $broadsheet->id,
                    'sub_assessment_id' => $validated['sub_assessment_id'],
                    'assessment_id' => $validated['assessment_id'],
                ],
                ['score' => $validated['score']]
            );

            if ($assessment && $assessment->subAssessments->isNotEmpty()) {
                $subTotal = BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheet->id)
                    ->where('assessment_id', $validated['assessment_id'])
                    ->sum('score');

                BroadsheetAssessmentScore::updateOrCreate(
                    [
                        'broadsheet_id' => $broadsheet->id,
                        'assessment_id' => $validated['assessment_id'],
                    ],
                    ['score' => $subTotal]
                );
            }
        } else {
            BroadsheetAssessmentScore::updateOrCreate(
                [
                    'broadsheet_id' => $broadsheet->id,
                    'assessment_id' => $validated['assessment_id'],
                ],
                ['score' => $validated['score']]
            );
        }

        // Rest of the calculations remain the same...
        $schoolclass = Schoolclass::with('classcategories')->find($exam->schoolclass_id);

        $allAssessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->get();
        }

        $broadsheet->load(['assessmentScores', 'subAssessmentScores']);

        $totalRaw = 0;
        foreach ($allAssessments as $assess) {
            $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assess->id)->first();
            $totalRaw += $scoreObj ? $scoreObj->score : 0;
        }

        $previousTerm = Broadsheets::where('broadsheet_records.student_id', $broadsheet->student_id)
            ->where('broadsheet_records.subject_id', $exam->subject_id)
            ->where('broadsheets.term_id', $exam->termid - 1)
            ->where('broadsheet_records.session_id', $exam->session)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->value('broadsheets.cum');

        $newBf = $previousTerm ? round($previousTerm, 2) : 0;
        $newCum = $exam->termid == 1 ? round($totalRaw, 2) : round(($totalRaw + $newBf) / 2, 2);

        $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
        $newGrade = $this->calculateGrade($newCum, $isSenior);
        $newRemark = $this->getRemark($newGrade);

        $broadsheet->total = $totalRaw;
        $broadsheet->bf = $newBf;
        $broadsheet->cum = $newCum;
        $broadsheet->grade = $newGrade;
        $broadsheet->remark = $newRemark;
        $broadsheet->save();

        $this->updateClassMetrics($subjectclass->id, auth()->user()->id, $exam->termid, $exam->session);
        $this->updateSubjectPositions($subjectclass->id, auth()->user()->id, $exam->termid, $exam->session);

        DB::commit();

        Log::info('TRANSFER COMPLETED:', [
            'student' => $student->firstname . ' ' . $student->lastname,
            'admission' => $student->admissionNo,
            'broadsheet_id' => $broadsheet->id,
            'subjectclass_id' => $subjectclass->id,
            'assessment' => $assessment->name,
            'score_saved' => $validated['score']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Score successfully transferred to assessment sheet',
            'data' => [
                'broadsheet_id' => $broadsheet->id,
                'subjectclass_id' => $subjectclass->id,
                'total' => $totalRaw,
                'cum' => $newCum,
                'grade' => $newGrade,
                'student_name' => $student->firstname . ' ' . $student->lastname,
                'admission_no' => $student->admissionNo
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('ERROR IN TRANSFER:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update score: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Calculate grade based on score and level
 */
private function calculateGrade($score, $isSenior = false)
{
    if (!$isSenior) {
        // Junior secondary grading
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    } else {
        // Senior secondary grading
        if ($score >= 75) return 'A1';
        if ($score >= 70) return 'B2';
        if ($score >= 65) return 'B3';
        if ($score >= 60) return 'C4';
        if ($score >= 55) return 'C5';
        if ($score >= 50) return 'C6';
        if ($score >= 45) return 'D7';
        if ($score >= 40) return 'E8';
        return 'F9';
    }
}

/**
 * Get remark based on grade
 */
private function getRemark($grade)
{
    $remarks = [
        'A' => 'Excellent',
        'B' => 'Very Good',
        'C' => 'Good',
        'D' => 'Credit',
        'E' => 'Pass',
        'F' => 'Fail',
        'A1' => 'Excellent',
        'B2' => 'Very Good',
        'B3' => 'Good',
        'C4' => 'Credit',
        'C5' => 'Credit',
        'C6' => 'Credit',
        'D7' => 'Pass',
        'E8' => 'Pass',
        'F9' => 'Fail',
    ];

    return $remarks[$grade] ?? 'Unknown';
}

/**
 * Update class metrics
 */
private function updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
{
    $subjectClass = DB::table('subjectclass')
        ->where('id', $subjectclassid)
        ->first(['subjectteacherid']);

    if (!$subjectClass) {
        Log::warning('Subjectclass not found', ['subjectclass_id' => $subjectclassid]);
        return;
    }

    $subjectTeacher = DB::table('subjectteacher')
        ->where('id', $subjectClass->subjectteacherid)
        ->first(['subjectid']);

    if (!$subjectTeacher) {
        Log::warning('Subjectteacher not found', ['subjectteacherid' => $subjectClass->subjectteacherid]);
        return;
    }

    $subjectId = $subjectTeacher->subjectid;

    $metrics = Broadsheets::where('broadsheets.subjectclass_id', $subjectclassid)
        ->where('broadsheets.staff_id', $staffid)
        ->where('broadsheets.term_id', $termid)
        ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
        ->where('broadsheet_records.session_id', $sessionid)
        ->where('broadsheet_records.subject_id', $subjectId)
        ->select([
            DB::raw('MIN(broadsheets.cum) as class_min'),
            DB::raw('MAX(broadsheets.cum) as class_max'),
            DB::raw('SUM(broadsheets.cum) as cum_sum'),
            DB::raw('COUNT(broadsheets.id) as student_count')
        ])
        ->first();

    $classMin = $metrics->class_min ?? 0;
    $classMax = $metrics->class_max ?? 0;
    $classAvg = $metrics->student_count > 0 ? round($metrics->cum_sum / $metrics->student_count, 1) : 0;

    Broadsheets::where('subjectclass_id', $subjectclassid)
        ->where('staff_id', $staffid)
        ->where('term_id', $termid)
        ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
        ->where('broadsheet_records.session_id', $sessionid)
        ->where('broadsheet_records.subject_id', $subjectId)
        ->update([
            'cmin' => $classMin,
            'cmax' => $classMax,
            'avg' => $classAvg,
        ]);

    Log::info('Class metrics updated:', [
        'subjectclass_id' => $subjectclassid,
        'cmin' => $classMin,
        'cmax' => $classMax,
        'avg' => $classAvg
    ]);
}

/**
 * Update subject positions
 */
private function updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id)
{
    $broadsheets = Broadsheets::where('subjectclass_id', $subjectclass_id)
        ->where('staff_id', $staff_id)
        ->where('term_id', $term_id)
        ->where('broadsheet_records.session_id', $session_id)
        ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
        ->orderByDesc('broadsheets.cum')
        ->orderBy('broadsheets.id')
        ->get();

    if ($broadsheets->isEmpty()) {
        return;
    }

    $rank = 0;
    $lastCum = null;
    $lastPosition = 0;

    foreach ($broadsheets as $broadsheet) {
        $rank++;
        if ($lastCum !== null && $broadsheet->cum == $lastCum) {
            // Tied rank - keep same position
        } else {
            $lastPosition = $rank;
            $lastCum = $broadsheet->cum;
        }

        if ($broadsheet->subject_position_class != $lastPosition) {
            $broadsheet->subject_position_class = $lastPosition;
            $broadsheet->save();
        }
    }

    Log::info('Subject positions updated for subjectclass_id: ' . $subjectclass_id);
}
}
