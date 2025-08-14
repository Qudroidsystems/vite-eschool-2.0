<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Subject;
use App\Models\Schoolarm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClassBroadsheetController extends Controller
{
    /**
     * Display the class broadsheet for a given class, session, and term.
     *
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return \Illuminate\View\View
     */
    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Class Broadsheet";

        // Fetch students enrolled in the specified class and session
        $students = Studentclass::where('studentclass.schoolclassid', $schoolclassid)
            ->where('studentclass.sessionid', $sessionid)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentpicture.picture as picture'
            ])->sortBy('lastname');

        // Log student IDs for debugging
        Log::info('Students fetched for class broadsheet', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'student_ids' => $students->pluck('id')->toArray(),
        ]);

        // Ensure a Studentpersonalityprofile exists for each student
        foreach ($students as $student) {
            $profile = Studentpersonalityprofile::firstOrNew([
                'studentid' => $student->id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                //'staffid' => Auth::user()->id
            ]);

            // Set fields for new records
            if (!$profile->exists) {
                $profile->studentid = $student->id;
                $profile->schoolclassid = $schoolclassid;
                $profile->sessionid = $sessionid;
                $profile->termid = $termid;
                $profile->staffid = Auth::user()->id;
                $profile->classteachercomment = null;
                $profile->guidancescomment = null;
                $profile->remark_on_other_activities = null;
                $profile->no_of_times_school_absent = null;
                $profile->signature = null;
                $profile->save();
                Log::info("Created new profile for student ID: {$student->id}", [
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'staffid' => Auth::user()->id,
                ]);
            }
        }

        // Fetch all subjects for the class
        $subjects = Subject::whereHas('broadsheetRecords', function ($query) use ($schoolclassid, $sessionid) {
            $query->where('schoolclass_id', $schoolclassid)
                  ->where('session_id', $sessionid);
        })->orderBy('subject')->get(['id', 'subject', 'subject_code']);

        // Fetch scores for all students in the class
        $scores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->get([
                'broadsheet_records.student_id',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'subject.subject as subject_name',
                'subject.subject_code',
                'broadsheets.ca1',
                'broadsheets.ca2',
                'broadsheets.ca3',
                'broadsheets.exam',
                'broadsheets.total',
                'broadsheets.grade',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
            ]);

        // Fetch personality profiles for comments and other fields, filtered by staffid
        $personalityProfiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->where('staffid', Auth::user()->id)
            ->get([
                'studentid',
                'classteachercomment',
                'guidancescomment',
                'remark_on_other_activities',
                'no_of_times_school_absent',
                'signature'
            ]);

        // Log profiles for debugging
        Log::info('Personality profiles fetched', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'staffid' => Auth::user()->id,
            'profiles' => $personalityProfiles->toArray(),
        ]);

        // Fetch schoolclass with arm
        $schoolclass = Schoolclass::where('schoolclass.id', $schoolclassid)
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->first(['schoolclass.schoolclass', 'schoolclass.arm', 'schoolarm.arm']);

        $schoolterm = Schoolterm::where('id', $termid)->value('term') ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';

        return view('classbroadsheet.classbroadsheet')
            ->with('students', $students)
            ->with('subjects', $subjects)
            ->with('scores', $scores)
            ->with('personalityProfiles', $personalityProfiles)
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession)
            ->with('schoolclassid', $schoolclassid)
            ->with('sessionid', $sessionid)
            ->with('termid', $termid)
            ->with('pagetitle', $pagetitle);
    }

    /**
     * Update class teacher and guidance counselor comments, remark on other activities, absence count, and signature for students.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $schoolclassid
     * @param int $sessionid
     * @param int $termid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        // Log the request for debugging
        Log::info('updateComments called', [
            'schoolclassid' => $schoolclassid,
            'sessionid' => $sessionid,
            'termid' => $termid,
            'raw_post_data' => $_POST,
            'request_data' => $request->all(),
            'has_file' => $request->hasFile('signature'),
        ]);

        // Validate the input
        $request->validate([
            'teacher_comments.*' => 'nullable|string|max:1000',
            'guidance_comments.*' => 'nullable|string|max:1000',
            'remarks_on_other_activities.*' => 'nullable|string|max:1000',
            'no_of_times_school_absent.*' => 'nullable|integer|min:0',
            'signature' => 'mimes:jpg,png,pdf|max:5048', // 2MB max
        ]);

        $teacherComments = $request->input('teacher_comments', []);
        $guidanceComments = $request->input('guidance_comments', []);
        $remarksOnOtherActivities = $request->input('remarks_on_other_activities', []);
        $noOfTimesSchoolAbsent = $request->input('no_of_times_school_absent', []);

        // Check if any data was provided
        if (empty($teacherComments) && empty($guidanceComments) && empty($remarksOnOtherActivities) && empty($noOfTimesSchoolAbsent)) {
            Log::warning('No data provided for update (excluding signature)', [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
            ]);
            return redirect()->back()->with('error', 'No data provided to update.');
        }

        // Handle signature file upload
        $signaturePath = null;
        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $filename = 'signature_' . time() . '.' . $file->getClientOriginalExtension();
            $signaturePath = $file->storeAs('public/signatures', $filename);
            $signaturePath = str_replace('public/', '', $signaturePath); // Store path relative to storage
            Log::info('Signature uploaded', ['path' => $signaturePath]);
        }

        // Use a database transaction to ensure atomicity
        DB::beginTransaction();
        try {
            $updatedCount = 0;
            foreach ($teacherComments as $studentId => $teacherComment) {
                $guidanceComment = $guidanceComments[$studentId] ?? '';
                $remarkOnOtherActivities = $remarksOnOtherActivities[$studentId] ?? '';
                $absenceCount = $noOfTimesSchoolAbsent[$studentId] ?? null;

                // Log each student update
                Log::info("Processing student ID: $studentId", [
                    'teacherComment' => $teacherComment,
                    'guidanceComment' => $guidanceComment,
                    'remarkOnOtherActivities' => $remarkOnOtherActivities,
                    'noOfTimesSchoolAbsent' => $absenceCount,
                    'signature' => $signaturePath,
                ]);

                // Find or create the Studentpersonalityprofile record
                $profile = Studentpersonalityprofile::firstOrNew([
                    'studentid' => $studentId,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'staffid' => Auth::user()->id,
                ]);

                // Update fields (allow empty strings or null to overwrite)
                $profile->classteachercomment = $teacherComment;
                $profile->guidancescomment = $guidanceComment;
                $profile->remark_on_other_activities = $remarkOnOtherActivities;
                $profile->no_of_times_school_absent = $absenceCount;
                $profile->signature = $signaturePath;

                // Save only if there are changes to avoid unnecessary updates
                if ($profile->isDirty()) {
                    $profile->save();
                    $updatedCount++;
                    Log::info("Saved profile for student ID: $studentId", [
                        'staffid' => $profile->staffid,
                        'classteachercomment' => $teacherComment,
                        'guidancescomment' => $guidanceComment,
                        'remark_on_other_activities' => $remarkOnOtherActivities,
                        'no_of_times_school_absent' => $absenceCount,
                        'signature' => $signaturePath,
                    ]);
                } else {
                    Log::info("No changes to save for student ID: $studentId");
                }
            }

            // Log executed queries
            DB::enableQueryLog();
            Log::info('Executed queries', DB::getQueryLog());
            DB::disableQueryLog();

            DB::commit();
            return redirect()->route('classbroadsheet.viewcomments', [$schoolclassid, $sessionid, $termid])
                ->with('success', "Data updated successfully for $updatedCount students.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating data: ' . $e->getMessage(), [
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to update data: ' . $e->getMessage());
        }
    }
}
