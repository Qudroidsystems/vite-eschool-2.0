<?php

namespace App\Http\Controllers;


use App\Models\BroadsheetsMock;
use App\Models\MockSubjectVetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subjectclass;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MockSubjectVettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View mock-subject-vettings|Create mock-subject-vettings|Update mock-subject-vettings|Delete mock-subject-vettings', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create mock-subject-vettings', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update mock-subject-vettings', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete mock-subject-vettings', ['only' => ['destroy']]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userid' => 'required|exists:users,id',
                'termid.*' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'subjectclassid.*' => 'required|exists:subjectclass,id',
            ], [
                'userid.required' => 'Please select a staff member!',
                'userid.exists' => 'Selected staff member does not exist!',
                'termid.*.required' => 'Please select at least one term!',
                'termid.*.exists' => 'Selected term does not exist!',
                'sessionid.required' => 'Please select a session!',
                'sessionid.exists' => 'Selected session does not exist!',
                'subjectclassid.*.required' => 'Please select at least one subject-class!',
                'subjectclassid.*.exists' => 'Selected subject-class does not exist!',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for mock store request: ' . json_encode($validator->errors()));
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->input('userid');
            $termIds = $request->input('termid', []);
            $sessionId = $request->input('sessionid');
            $subjectClassIds = array_unique($request->input('subjectclassid', []));

            Log::debug('Mock store inputs', [
                'userId' => $userId,
                'termIds' => $termIds,
                'sessionId' => $sessionId,
                'subjectClassIds' => $subjectClassIds
            ]);

            if (empty($termIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one term.'
                ], 422);
            }

            if (empty($subjectClassIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one subject-class.'
                ], 422);
            }

            // Check if the vetting staff is the same as the subject teacher
            $subjectClasses = Subjectclass::whereIn('subjectclass.id', $subjectClassIds)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->pluck('subjectteacher.staffid')
                ->toArray();

            if (in_array($userId, $subjectClasses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected staff member cannot vet their own subject-class assignment.'
                ], 422);
            }

            // Check for existing assignments for the same subject, term, and session (regardless of vetting staff)
            $existingAssignments = MockSubjectVetting::whereIn('subjectclassid', $subjectClassIds)
                ->whereIn('termid', $termIds)
                ->where('sessionid', $sessionId)
                ->pluck('subjectclassid')
                ->toArray();

            Log::debug('Existing mock assignments', ['existingAssignments' => $existingAssignments]);

            if (!empty($existingAssignments)) {
                $assignedSubjectClasses = Subjectclass::whereIn('subjectclass.id', array_unique($existingAssignments))
                    ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                    ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                    ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                    ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->get(['subjectclass.id', 'subject.subject as subjectname', 'schoolclass.schoolclass as sclass', 'schoolarm.arm as schoolarm'])
                    ->map(function ($sc) {
                        return "{$sc->subjectname} - {$sc->sclass} ({$sc->schoolarm})";
                    })->toArray();

                return response()->json([
                    'success' => false,
                    'message' => 'The following subject-classes are already assigned for mock vetting in the selected term and session: ' . implode(', ', $assignedSubjectClasses)
                ], 422);
            }

           $createdRecords = [];
                foreach ($termIds as $termId) {
                    foreach ($subjectClassIds as $subjectClassId) {
                        $mockSubjectVetting = MockSubjectVetting::create([
                            'userid' => $userId,
                            'subjectclassId' => $subjectClassId, // Use capital 'I' to match fillable and database column
                            'termid' => $termId,
                            'sessionid' => $sessionId,
                            'status' => 'pending',
                        ]);

                        $createdRecords[] = $mockSubjectVetting;
                    }
                }
                
            if (empty($createdRecords)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No new mock subject vetting assignments were created.'
                ], 422);
            }

            Log::info('Mock subject vetting assignments created', ['count' => count($createdRecords)]);
            return response()->json([
                'success' => true,
                'message' => 'Mock Subject Vetting assignment(s) added successfully.',
                'data' => $createdRecords
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error storing mock subject vetting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error adding mock subject vetting assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $pagetitle = "Mock Subject Vetting Management";

            $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
                ->sortBy('schoolclass');

            $subjectclasses = Subjectclass::leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->get([
                    'subjectclass.id as scid',
                    'schoolclass.id as schoolclassid',
                    'schoolclass.schoolclass as sclass',
                    'schoolarm.arm as schoolarm',
                    'subjectteacher.id as subteacherid',
                    'subjectteacher.staffid as subtid',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'users.name as teachername',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname'
                ])
                ->sortBy('subjectname');

            $staff = User::get(['id', 'name', 'avatar'])->sortBy('name');
            $terms = Schoolterm::get(['id', 'term'])->sortBy('term');
            $sessions = Schoolsession::get(['id', 'session'])->sortBy('session');

            $mocksubjectvettings = MockSubjectVetting::leftJoin('subjectclass', 'mock_subject_vettings.subjectclassid', '=', 'subjectclass.id')
                ->leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users as vetting_user', 'mock_subject_vettings.userid', '=', 'vetting_user.id') // Fixed: removed 狂
                ->leftJoin('users as teacher_user', 'subjectteacher.staffid', '=', 'teacher_user.id')
                ->leftJoin('schoolterm', 'mock_subject_vettings.termid', '=', 'schoolterm.id')
                ->leftJoin('schoolsession', 'mock_subject_vettings.sessionid', '=', 'schoolsession.id')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->select([
                    'mock_subject_vettings.id as svid',
                    'mock_subject_vettings.userid as vetting_userid',
                    'vetting_user.name as vetting_username',
                    'vetting_user.avatar as vetting_picture',
                    'subjectclass.id as subjectclassid',
                    'schoolclass.id as schoolclassid',
                    'schoolclass.schoolclass as sclass',
                    'schoolarm.arm as schoolarm',
                    'subjectteacher.staffid as subtid',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'teacher_user.name as teachername',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname',
                    'mock_subject_vettings.status',
                    'mock_subject_vettings.updated_at'
                ])
                ->orderBy('vetting_username')
                ->get();

            $statusCounts = MockSubjectVetting::groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status')
                ->toArray();

            $statusCounts = array_merge([
                'pending' => 0,
                'completed' => 0,
                'rejected' => 0
            ], $statusCounts);

            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'mocksubjectvettings' => $mocksubjectvettings,
                    'statusCounts' => $statusCounts
                ], 200);
            }

            return view('mocksubjectvetting.index')
                ->with('mocksubjectvettings', $mocksubjectvettings)
                ->with('schoolclasses', $schoolclasses)
                ->with('subjectclasses', $subjectclasses)
                ->with('staff', $staff)
                ->with('terms', $terms)
                ->with('sessions', $sessions)
                ->with('pagetitle', $pagetitle)
                ->with('statusCounts', $statusCounts);
        } catch (\Exception $e) {
            Log::error('Error loading mock subject vetting index: ' . $e->getMessage());
            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load mock subject vetting data: ' . $e->getMessage()
                ], 500);
            }
            return view('mocksubjectvetting.index')
                ->with('mocksubjectvettings', collect([]))
                ->with('schoolclasses', collect([]))
                ->with('subjectclasses', collect([]))
                ->with('staff', collect([]))
                ->with('terms', collect([]))
                ->with('sessions', collect([]))
                ->with('pagetitle', 'Mock Subject Vetting Management')
                ->with('statusCounts', ['pending' => 0, 'completed' => 0, 'rejected' => 0])
                ->with('danger', 'Failed to load mock subject vetting data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
                ->sortBy('schoolclass');

            $subjectclasses = Subjectclass::leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->get([
                    'subjectclass.id as scid',
                    'schoolclass.id as schoolclassid',
                    'schoolclass.schoolclass as sclass',
                    'schoolarm.arm as schoolarm',
                    'subjectteacher.id as subteacherid',
                    'subjectteacher.staffid as subtid',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'users.name as teachername',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname'
                ])
                ->sortBy('sclass');

            $staff = User::get(['id', 'name', 'avatar'])->sortBy('name');
            $terms = Schoolterm::get(['id', 'term'])->sortBy('term');
            $sessions = Schoolsession::get(['id', 'session'])->sortBy('session');

            return view('mocksubjectvetting.create')
                ->with('subjectclasses', $subjectclasses)
                ->with('schoolclasses', $schoolclasses)
                ->with('staff', $staff)
                ->with('terms', $terms)
                ->with('sessions', $sessions);
        } catch (\Exception $e) {
            Log::error('Error loading mock subject vetting create page: ' . $e->getMessage());
            return redirect()->route('mocksubjectvetting.index')
                ->with('danger', 'Failed to load create page: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
                ->sortBy('schoolclass');

            $subjectclasses = Subjectclass::leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->get([
                    'subjectclass.id as scid',
                    'schoolclass.id as schoolclassid',
                    'schoolclass.schoolclass as sclass',
                    'schoolarm.arm as schoolarm',
                    'subjectteacher.id as subteacherid',
                    'subjectteacher.staffid as subtid',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'users.name as teachername',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname'
                ])
                ->sortBy('sclass');

            $staff = User::get(['id', 'name', 'avatar'])->sortBy('name');
            $terms = Schoolterm::get(['id', 'term'])->sortBy('term');
            $sessions = Schoolsession::get(['id', 'session'])->sortBy('session');

            $mocksubjectvetting = MockSubjectVetting::where('mock_subject_vettings.id', $id)
                ->leftJoin('subjectclass', 'mock_subject_vettings.subjectclassid', '=', 'subjectclass.id')
                ->leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users as vetting_user', 'mock_subject_vettings.userid', '=', 'vetting_user.id')
                ->leftJoin('users as teacher_user', 'subjectteacher.staffid', '=', 'teacher_user.id')
                ->leftJoin('schoolterm', 'mock_subject_vettings.termid', '=', 'schoolterm.id')
                ->leftJoin('schoolsession', 'mock_subject_vettings.sessionid', '=', 'schoolsession.id')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->first([
                    'mock_subject_vettings.id as svid',
                    'mock_subject_vettings.userid as vetting_userid',
                    'vetting_user.name as vetting_username',
                    'vetting_user.avatar as vetting_picture',
                    'subjectclass.id as subjectclassid',
                    'schoolclass.id as schoolclassid',
                    'schoolclass.schoolclass as sclass',
                    'schoolarm.arm as schoolarm',
                    'subjectteacher.staffid as subtid',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'teacher_user.name as teachername',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname',
                    'mock_subject_vettings.status',
                    'mock_subject_vettings.updated_at'
                ]);

            if (!$mocksubjectvetting) {
                Log::warning('Mock subject vetting not found for edit', ['id' => $id]);
                return redirect()->route('mocksubjectvetting.index')->with('danger', 'Mock Subject Vetting assignment not found.');
            }

            return view('mocksubjectvetting.edit')
                ->with('mocksubjectvettings', collect([$mocksubjectvetting]))
                ->with('subjectclasses', $subjectclasses)
                ->with('schoolclasses', $schoolclasses)
                ->with('staff', $staff)
                ->with('terms', $terms)
                ->with('sessions', $sessions);
        } catch (\Exception $e) {
            Log::error('Error loading mock subject vetting edit page: ' . $e->getMessage());
            return redirect()->route('mocksubjectvetting.index')
                ->with('danger', 'Failed to load edit page: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userid' => 'required|exists:users,id',
                'subjectclassid' => 'required|exists:subjectclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'status' => 'required|in:pending,completed,rejected',
            ], [
                'userid.required' => 'Please select a staff member!',
                'userid.exists' => 'Selected staff member does not exist!',
                'subjectclassid.required' => 'Please select a subject-class!',
                'subjectclassid.exists' => 'Selected subject-class does not exist!',
                'termid.required' => 'Please select a term!',
                'termid.exists' => 'Selected term does not exist!',
                'sessionid.required' => 'Please select a session!',
                'sessionid.exists' => 'Selected session does not exist!',
                'status.required' => 'Please select a status!',
                'status.in' => 'Invalid status selected!',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for mock update request: ' . json_encode($validator->errors()));
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->input('userid');
            $subjectClassId = $request->input('subjectclassid');
            $termId = $request->input('termid');
            $sessionId = $request->input('sessionid');
            $status = $request->input('status');

            // Check if the vetting staff is the same as the subject teacher
            $subjectClass = Subjectclass::where('subjectclass.id', $subjectClassId)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->first(['subjectteacher.staffid']);

            if ($subjectClass && $subjectClass->staffid == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected staff member cannot vet their own subject-class assignment.'
                ], 422);
            }

            // Check for existing assignments for the same subject, term, and session (excluding current record)
            $existingAssignment = MockSubjectVetting::where('subjectclassid', $subjectClassId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->where('id', '!=', $id)
                ->first();

            if ($existingAssignment) {
                $assignedSubjectClass = Subjectclass::where('subjectclass.id', $subjectClassId)
                    ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                    ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                    ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                    ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->first(['subject.subject as subjectname', 'schoolclass.schoolclass as sclass', 'schoolarm.arm as schoolarm']);

                return response()->json([
                    'success' => false,
                    'message' => "The subject-class {$assignedSubjectClass->subjectname} - {$assignedSubjectClass->sclass} ({$assignedSubjectClass->schoolarm}) is already assigned for mock vetting in the selected term and session."
                ], 422);
            }

            $mockSubjectVetting = MockSubjectVetting::find($id);
            if (!$mockSubjectVetting) {
                Log::warning('Mock subject vetting not found for update', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mock Subject Vetting assignment not found.'
                ], 404);
            }

            $mockSubjectVetting->update([
                'userid' => $userId,
                'subjectclassid' => $subjectClassId,
                'termid' => $termId,
                'sessionid' => $sessionId,
                'status' => $status,
            ]);

            Log::info('Mock subject vetting updated', ['id' => $id]);
            return response()->json([
                'success' => true,
                'message' => 'Mock Subject Vetting assignment updated successfully.',
                'data' => $mockSubjectVetting
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating mock subject vetting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating mock subject vetting assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mockSubjectVetting = MockSubjectVetting::find($id);
            if (!$mockSubjectVetting) {
                Log::warning('Mock subject vetting not found for deletion', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mock Subject Vetting assignment not found.'
                ], 404);
            }

            $vettingUserId = $mockSubjectVetting->userid;
            $subjectClassId = $mockSubjectVetting->subjectclassid; // Fixed: lowercase 'i'
            $termId = $mockSubjectVetting->termid;
            $sessionId = $mockSubjectVetting->sessionid;

            DB::transaction(function () use ($vettingUserId, $subjectClassId, $termId, $sessionId, $mockSubjectVetting) {
                $broadsheetsExist = BroadsheetsMock::where('vettedby', $vettingUserId)
                    ->where('subjectclass_id', $subjectClassId)
                    ->where('term_id', $termId)
                    ->exists();

                if ($broadsheetsExist) {
                    BroadsheetsMock::where('vettedby', $vettingUserId)
                        ->where('subjectclass_id', $subjectClassId)
                        ->where('term_id', $termId)
                        ->update([
                            'vettedby' => null,
                            'vettedstatus' => null
                        ]);
                }

                $mockSubjectVetting->delete();
            });

            Log::info('Mock subject vetting deleted', ['id' => $id]);
            return response()->json([
                'success' => true,
                'message' => 'Mock Subject Vetting assignment deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting mock subject vetting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting mock subject vetting assignment: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>