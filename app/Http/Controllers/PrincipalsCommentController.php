<?php

namespace App\Http\Controllers;

use App\Models\Principalscomment;
use App\Models\Schoolclass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrincipalsCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View principals-comment|Create principals-comment|Update principals-comment|Delete principals-comment', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create principals-comment', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update principals-comment', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete principals-comment', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $pagetitle = "Principals Comment Management";

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $staff = User::get(['id', 'name', 'avatar'])->sortBy('name');

        $principalscomments = Principalscomment::leftJoin('schoolclass', 'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('users', 'principalscomments.staffId', '=', 'users.id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select([
                'principalscomments.id as pcid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'users.id as staffid',
                'users.name as staffname',
                'users.avatar as picture',
                'principalscomments.updated_at'
            ])
            ->orderBy('staffname') // Changed to order by staff name for better grouping
            ->get();

        return view('principalscomment.index')
            ->with('principalscomments', $principalscomments)
            ->with('schoolclasses', $schoolclasses)
            ->with('staff', $staff)
            ->with('pagetitle', $pagetitle);
    }

    public function create()
    {
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $staff = User::get(['id', 'name', 'avatar'])->sortBy('name');

        return view('principalscomment.create')
            ->with('schoolclasses', $schoolclasses)
            ->with('staff', $staff);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffId' => 'required|exists:users,id',
            'schoolclassid.*' => 'required|exists:schoolclass,id',
        ], [
            'staffId.required' => 'Please select a staff member!',
            'staffId.exists' => 'Selected staff member does not exist!',
            'schoolclassid.*.required' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $staffId = $request->input('staffId');
        $schoolClassIds = $request->input('schoolclassid', []);

        if (empty($schoolClassIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one class.'
            ], 422);
        }

        $createdRecords = [];
        foreach ($schoolClassIds as $schoolClassId) {
            $exists = Principalscomment::where('staffId', $staffId)
                ->where('schoolclassid', $schoolClassId)
                ->exists();

            if ($exists) {
                continue;
            }

            $principalscomment = Principalscomment::create([
                'staffId' => $staffId,
                'schoolclassid' => $schoolClassId,
            ]);

            $createdRecords[] = $principalscomment;
        }

        if (empty($createdRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'The selected staff member is already assigned to all selected classes.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Principals Comment assignment(s) added successfully.',
            'data' => $createdRecords
        ], 201);
    }

    public function edit($id)
    {
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $staff = User::get(['id', 'name', 'avatar'])->sortBy('name');

        $principalscomment = Principalscomment::where('principalscomments.id', $id)
            ->leftJoin('schoolclass', 'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('users', 'principalscomments.staffId', '=', 'users.id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->first([
                'principalscomments.id as pcid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'users.id as staffid',
                'users.name as staffname',
                'users.avatar as picture',
                'principalscomments.updated_at'
            ]);

        if (!$principalscomment) {
            return redirect()->route('principalscomment.index')->with('danger', 'Principals Comment assignment not found.');
        }

        return view('principalscomment.edit')
            ->with('principalscomments', collect([$principalscomment]))
            ->with('schoolclasses', $schoolclasses)
            ->with('staff', $staff);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffId' => 'required|exists:users,id',
            'schoolclassid' => 'required|exists:schoolclass,id',
        ], [
            'staffId.required' => 'Please select a staff member!',
            'staffId.exists' => 'Selected staff member does not exist!',
            'schoolclassid.required' => 'Please select a class!',
            'schoolclassid.exists' => 'Selected class does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $staffId = $request->input('staffId');
        $schoolClassId = $request->input('schoolclassid');

        $exists = Principalscomment::where('staffId', $staffId)
            ->where('schoolclassid', $schoolClassId)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This staff member is already assigned to this class.'
            ], 422);
        }

        $principalscomment = Principalscomment::updateOrCreate(
            ['id' => $id],
            [
                'staffId' => $staffId,
                'schoolclassid' => $schoolClassId,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Principals Comment assignment updated successfully.',
            'data' => $principalscomment
        ], 200);
    }

    public function destroy($id)
    {
        $principalscomment = Principalscomment::find($id);
        if (!$principalscomment) {
            return response()->json([
                'success' => false,
                'message' => 'Principals Comment assignment not found.'
            ], 404);
        }

        $principalscomment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Principals Comment assignment deleted successfully.'
        ], 200);
    }
}