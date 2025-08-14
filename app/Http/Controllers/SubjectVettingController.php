<?php

namespace App\Http\Controllers;

use App\Models\SubjectVetting;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use App\Models\SubjectTeacher;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\User;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SubjectVettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject-vettings|Create subject-vettings|Update subject-vettings|Delete subject-vettings', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create subject-vettings', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update subject-vettings', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete subject-vettings', ['only' => ['destroy']]);
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
                Log::warning('Validation failed for store request: ' . json_encode($validator->errors()));
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->input('userid');
            $termIds = $request->input('termid', []);
            $sessionId = $request->input('sessionid');
            $subjectClassIds = array_unique($request->input('subjectclassid', [])); // Deduplicate subjectclassid

            Log::debug('Store inputs', [
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
            $existingAssignments = SubjectVetting::whereIn('subjectclassid', $subjectClassIds)
                ->whereIn('termid', $termIds)
                ->where('sessionid', $sessionId)
                ->pluck('subjectclassid')
                ->toArray();

            Log::debug('Existing assignments', ['existingAssignments' => $existingAssignments]);

            if (!empty($existingAssignments)) {
                $assignedSubjectClasses = Subjectclass::whereIn('subjectclass.id', array_unique($existingAssignments))
                    ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                    ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                    ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->get(['subjectclass.id', 'subject.subject as subjectname', 'schoolclass.schoolclass as sclass', 'schoolarm.arm as schoolarm'])
                    ->map(function ($sc) {
                        return "{$sc->subjectname} - {$sc->sclass} ({$sc->schoolarm})";
                    })->toArray();

                return response()->json([
                    'success' => false,
                    'message' => 'The following subject-classes are already assigned for vetting in the selected term and session: ' . implode(', ', $assignedSubjectClasses)
                ], 422);
            }

            $createdRecords = [];
            foreach ($termIds as $termId) {
                foreach ($subjectClassIds as $subjectClassId) {
                    $subjectVetting = SubjectVetting::create([
                        'userid' => $userId,
                        'subjectclassId' => $subjectClassId,
                        'termid' => $termId,
                        'sessionid' => $sessionId,
                        'status' => 'pending',
                    ]);

                    $createdRecords[] = $subjectVetting;
                }
            }

            if (empty($createdRecords)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No new subject vetting assignments were created.'
                ], 422);
            }

            Log::info('Subject vetting assignments created', ['count' => count($createdRecords)]);
            return response()->json([
                'success' => true,
                'message' => 'Subject Vetting assignment(s) added successfully.',
                'data' => $createdRecords
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error storing subject vetting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error adding subject vetting assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $pagetitle = "Subject Vetting Management";

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

            $subjectvettings = SubjectVetting::leftJoin('subjectclass', 'subject_vettings.subjectclassid', '=', 'subjectclass.id')
                ->leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users as vetting_user', 'subject_vettings.userid', '=', 'vetting_user.id')
                ->leftJoin('users as teacher_user', 'subjectteacher.staffid', '=', 'teacher_user.id')
                ->leftJoin('schoolterm', 'subject_vettings.termid', '=', 'schoolterm.id')
                ->leftJoin('schoolsession', 'subject_vettings.sessionid', '=', 'schoolsession.id')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->select([
                    'subject_vettings.id as svid',
                    'subject_vettings.userid as vetting_userid',
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
                    'subject_vettings.status',
                    'subject_vettings.updated_at'
                ])
                ->orderBy('vetting_username')
                ->get();

            $statusCounts = SubjectVetting::groupBy('status')
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
                    'subjectvettings' => $subjectvettings,
                    'statusCounts' => $statusCounts
                ], 200);
            }

            return view('subjectvetting.index')
                ->with('subjectvettings', $subjectvettings)
                ->with('schoolclasses', $schoolclasses)
                ->with('subjectclasses', $subjectclasses)
                ->with('staff', $staff)
                ->with('terms', $terms)
                ->with('sessions', $sessions)
                ->with('pagetitle', $pagetitle)
                ->with('statusCounts', $statusCounts);
        } catch (\Exception $e) {
            Log::error('Error loading subject vetting index: ' . $e->getMessage());
            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load subject vetting data: ' . $e->getMessage()
                ], 500);
            }
            return view('subjectvetting.index')
                ->with('subjectvettings', collect([]))
                ->with('schoolclasses', collect([]))
                ->with('subjectclasses', collect([]))
                ->with('staff', collect([]))
                ->with('terms', collect([]))
                ->with('sessions', collect([]))
                ->with('pagetitle', 'Subject Vetting Management')
                ->with('statusCounts', ['pending' => 0, 'completed' => 0, 'rejected' => 0])
                ->with('danger', 'Failed to load subject vetting data: ' . $e->getMessage());
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

            return view('subjectvetting.create')
                ->with('subjectclasses', $subjectclasses)
                ->with('schoolclasses', $schoolclasses)
                ->with('staff', $staff)
                ->with('terms', $terms)
                ->with('sessions', $sessions);
        } catch (\Exception $e) {
            Log::error('Error loading subject vetting create page: ' . $e->getMessage());
            return redirect()->route('subjectvetting.index')
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

            $subjectvetting = SubjectVetting::where('subject_vettings.id', $id)
                ->leftJoin('subjectclass', 'subject_vettings.subjectclassid', '=', 'subjectclass.id')
                ->leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users as vetting_user', 'subject_vettings.userid', '=', 'vetting_user.id')
                ->leftJoin('users as teacher_user', 'subjectteacher.staffid', '=', 'teacher_user.id')
                ->leftJoin('schoolterm', 'subject_vettings.termid', '=', 'schoolterm.id')
                ->leftJoin('schoolsession', 'subject_vettings.sessionid', '=', 'schoolsession.id')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->first([
                    'subject_vettings.id as svid',
                    'subject_vettings.userid as vetting_userid',
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
                    'subject_vettings.status',
                    'subject_vettings.updated_at'
                ]);

            if (!$subjectvetting) {
                Log::warning('Subject vetting not found for edit', ['id' => $id]);
                return redirect()->route('subjectvetting.index')->with('danger', 'Subject Vetting assignment not found.');
            }

            return view('subjectvetting.edit')
                ->with('subjectvettings', collect([$subjectvetting]))
                ->with('subjectclasses', $subjectclasses)
                ->with('schoolclasses', $schoolclasses)
                ->with('staff', $staff)
                ->with('terms', $terms)
                ->with('sessions', $sessions);
        } catch (\Exception $e) {
            Log::error('Error loading subject vetting edit page: ' . $e->getMessage());
            return redirect()->route('subjectvetting.index')
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
                Log::warning('Validation failed for update request: ' . json_encode($validator->errors()));
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
            $existingAssignment = SubjectVetting::where('subjectclassid', $subjectClassId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->where('id', '!=', $id)
                ->first();

            if ($existingAssignment) {
                $assignedSubjectClass = Subjectclass::where('subjectclass.id', $subjectClassId)
                    ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                    ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                    ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->first(['subject.subject as subjectname', 'schoolclass.schoolclass as sclass', 'schoolarm.arm as schoolarm']);

                return response()->json([
                    'success' => false,
                    'message' => "The subject-class {$assignedSubjectClass->subjectname} - {$assignedSubjectClass->sclass} ({$assignedSubjectClass->schoolarm}) is already assigned for vetting in the selected term and session."
                ], 422);
            }

            $subjectVetting = SubjectVetting::find($id);
            if (!$subjectVetting) {
                Log::warning('Subject vetting not found for update', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Subject Vetting assignment not found.'
                ], 404);
            }

            $subjectVetting->update([
                'userid' => $userId,
                'subjectclassid' => $subjectClassId,
                'termid' => $termId,
                'sessionid' => $sessionId,
                'status' => $status,
            ]);

            Log::info('Subject vetting updated', ['id' => $id]);
            return response()->json([
                'success' => true,
                'message' => 'Subject Vetting assignment updated successfully.',
                'data' => $subjectVetting
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating subject vetting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating subject vetting assignment: ' . $e->getMessage()
            ], 500);
        }
    }

       public function destroy($id)
    {
        try {
            $subjectVetting = SubjectVetting::find($id);
            if (!$subjectVetting) {
                Log::warning('Subject vetting not found for deletion', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Subject Vetting assignment not found.'
                ], 404);
            }

            $vettingUserId = $subjectVetting->userid;
            $subjectClassId = $subjectVetting->subjectclassId;
            $termId = $subjectVetting->termid;
            $sessionId = $subjectVetting->sessionid;

            DB::transaction(function () use ($vettingUserId, $subjectClassId, $termId, $sessionId, $subjectVetting) {
                $broadsheetsExist = Broadsheets::where('vettedby', $vettingUserId)
                    ->where('subjectclass_id', $subjectClassId)
                    ->where('term_id', $termId)
                    ->exists();

                if ($broadsheetsExist) {
                    Broadsheets::where('vettedby', $vettingUserId)
                        ->where('subjectclass_id', $subjectClassId)
                        ->where('term_id', $termId)
                        ->update([
                            'vettedby' => null,
                            'vettedstatus' => null
                        ]);
                }

                $subjectVetting->delete();
            });

            Log::info('Subject vetting deleted', ['id' => $id]);
            return response()->json([
                'success' => true,
                'message' => 'Subject Vetting assignment deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting subject vetting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting subject vetting assignment: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>