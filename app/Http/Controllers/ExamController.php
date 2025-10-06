<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Schoolterm;
use App\Models\Schoolclass;
use App\Models\ClassTeacher;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SubjectTeacher;
use App\Models\Staffclasssetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
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
}