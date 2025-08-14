<?php

namespace App\Http\Controllers;

use App\Models\ClassTeacher;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClassTeacherController extends Controller
{
    public function index(Request $request)
    {
        $pagetitle = "Class Teacher Management";

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $subjectteachers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name']);

        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $classteachers = ClassTeacher::leftJoin('users', 'users.id', '=', 'classteacher.staffid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->select([
                'classteacher.id as id',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.id as schoolarmid',
                'schoolarm.arm as schoolarm',
                'schoolterm.id as termid',
                'schoolterm.term as term',
                'schoolsession.id as sessionid',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at'
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('users.name')
            ->paginate(100);

        if ($request->ajax()) {
            $html = view('classteacher.index', compact('classteachers', 'schoolclass', 'subjectteachers', 'schoolterms', 'schoolsessions', 'pagetitle'))->render();
            if (empty($html)) {
                Log::error("Empty HTML response in ClassTeacherController::index for AJAX request", ['url' => $request->fullUrl()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to render view',
                ], 500);
            }
            Log::info("AJAX response generated", ['html_length' => strlen($html), 'count' => $classteachers->count(), 'total' => $classteachers->total()]);
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $classteachers->count(),
                'total' => $classteachers->total(),
            ]);
        }

        return view('classteacher.index')
            ->with('classteachers', $classteachers)
            ->with('schoolclass', $schoolclass)
            ->with('subjectteachers', $subjectteachers)
            ->with('schoolterms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('pagetitle', $pagetitle);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $createdRecords = [];
        $duplicateClasses = [];
        $assignedClasses = [];

        foreach ($request->input('schoolclassid') as $classId) {
            $exists = ClassTeacher::where('staffid', $request->input('staffid'))
                ->where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->exists();

            if ($exists) {
                $schoolclass = Schoolclass::find($classId);
                $duplicateClasses[] = $schoolclass ? $schoolclass->schoolclass : $classId;
                continue;
            }

            $otherTeacher = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('staffid', '!=', $request->input('staffid'))
                ->first();

            if ($otherTeacher) {
                $schoolclass = Schoolclass::find($classId);
                $assignedClasses[] = $schoolclass ? $schoolclass->schoolclass : $classId;
                continue;
            }

            $classteacher = ClassTeacher::create([
                'staffid' => $request->input('staffid'),
                'schoolclassid' => $classId,
                'termid' => $request->input('termid'),
                'sessionid' => $request->input('sessionid'),
            ]);
            $createdRecords[] = $classteacher;
        }

        if (!empty($duplicateClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the following class(es) for the selected term and session: ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($assignedClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher for the selected term and session: ' . implode(', ', $assignedClasses)
            ], 422);
        }

        if (empty($createdRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'No new class teachers were added due to duplicates or existing assignments.'
            ], 422);
        }

        Log::info("Class teacher(s) added", ['records' => count($createdRecords)]);
        return response()->json([
            'success' => true,
            'message' => 'Class Teacher(s) added successfully.',
            'data' => $createdRecords
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $primaryRecord = ClassTeacher::find($id);
        if (!$primaryRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher not found.'
            ], 404);
        }

        $existingRecords = ClassTeacher::where('staffid', $primaryRecord->staffid)
            ->where('termid', $primaryRecord->termid)
            ->where('sessionid', $primaryRecord->sessionid)
            ->get();

        $duplicateClasses = [];
        $assignedClasses = [];
        foreach ($request->input('schoolclassid') as $classId) {
            $exists = ClassTeacher::where('staffid', $request->input('staffid'))
                ->where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->whereNotIn('id', $existingRecords->pluck('id')->toArray())
                ->exists();

            if ($exists) {
                $schoolclass = Schoolclass::find($classId);
                $duplicateClasses[] = $schoolclass ? $schoolclass->schoolclass : $classId;
                continue;
            }

            $otherTeacher = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('staffid', '!=', $request->input('staffid'))
                ->first();

            if ($otherTeacher) {
                $schoolclass = Schoolclass::find($classId);
                $assignedClasses[] = $schoolclass ? $schoolclass->schoolclass : $classId;
                continue;
            }
        }

        if (!empty($duplicateClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the following class(es) for the selected term and session: ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($assignedClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher for the selected term and session: ' . implode(', ', $assignedClasses)
            ], 422);
        }

        ClassTeacher::where('staffid', $primaryRecord->staffid)
            ->where('termid', $primaryRecord->termid)
            ->where('sessionid', $primaryRecord->sessionid)
            ->delete();

        $createdRecords = [];
        foreach ($request->input('schoolclassid') as $classId) {
            $classteacher = ClassTeacher::create([
                'staffid' => $request->input('staffid'),
                'schoolclassid' => $classId,
                'termid' => $request->input('termid'),
                'sessionid' => $request->input('sessionid'),
            ]);
            $createdRecords[] = $classteacher;
        }

        Log::info("Class teacher(s) updated", ['records' => count($createdRecords)]);
        return response()->json([
            'success' => true,
            'message' => 'Class Teacher updated successfully.',
            'data' => $createdRecords
        ], 200);
    }

    public function destroy($id)
    {
        $classteacher = ClassTeacher::find($id);
        if (!$classteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher not found.'
            ], 404);
        }

        $classteacher->delete();
        Log::info("Class teacher deleted", ['id' => $id]);
        return response()->json([
            'success' => true,
            'message' => 'Class Teacher deleted successfully.'
        ], 200);
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No class teachers selected for deletion.'
            ], 400);
        }

        $deleted = ClassTeacher::whereIn('id', $ids)->delete();
        Log::info("Multiple class teachers deleted", ['count' => $deleted, 'ids' => $ids]);
        return response()->json([
            'success' => true,
            'message' => "$deleted class teacher(s) deleted successfully."
        ], 200);
    }

    public function assignments($staffId, $termId, $sessionId)
    {
        $classIds = ClassTeacher::where('staffid', $staffId)
            ->where('termid', $termId)
            ->where('sessionid', $sessionId)
            ->pluck('schoolclassid')
            ->toArray();

        return response()->json([
            'success' => true,
            'classIds' => $classIds
        ], 200);
    }
}