<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\Broadsheet;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetRecordMock;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\ParentRegistration;
use App\Models\PromotionStatus;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\StudentBatchModel;
use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentBook;
use App\Models\Studentclass;  
use App\Models\Studenthouse;
use App\Models\Studentpersonalityprofile;
use App\Models\Studentpicture;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use App\Traits\ImageManager as TraitsImageManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use TraitsImageManager;

    public function __construct()
    {
        $this->middleware("permission:View student|Show Student|Create student|Update student|Delete student", ["only" => ["index", "store"]]);
        $this->middleware("permission:Create student", ["only" => ["create", "store"]]);
        $this->middleware("permission:Update student", ["only" => ["edit", "update"]]);
        $this->middleware("permission:Delete student", ["only" => ["destroy", "deletestudent"]]);
        $this->middleware("permission:Create student-bulk-upload", ["only" => ["bulkupload"]]);
        $this->middleware("permission:Create student-bulk-uploadsave", ["only" => ["bulkuploadsave"]]);
        //$this->middleware("permission:Create student-bulk-upload", ["only" => ["updateClass"]]);
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $students = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->select([
                    'studentRegistration.id',
                    'studentRegistration.admissionNo',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.othername',
                    'studentRegistration.gender',
                    'studentRegistration.statusId',
                    'studentRegistration.created_at',
                    'studentpicture.picture',
                    'schoolclass.schoolclass',
                    'schoolarm.arm',
                    'studentclass.schoolclassid'
                ])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching students: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage()
            ], 500);
        }
    }


    public function index(Request $request)
    {
        $pagetitle = "Student Management";

        $schoolclass = Schoolclass::all();
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        // Status counts
        $status_counts = Student::groupBy('statusId')
            ->selectRaw("CASE WHEN statusId = 1 THEN 'Old Student' ELSE 'New Student' END as student_status, COUNT(*) as student_count")
            ->pluck('student_count', 'student_status')
            ->toArray();
        $status_counts = [
            'Old Student' => $status_counts['Old Student'] ?? 0,
            'New Student' => $status_counts['New Student'] ?? 0
        ];

        // Gender counts
        $gender_counts = Student::groupBy('gender')
            ->selectRaw('gender, COUNT(*) as gender_count')
            ->pluck('gender_count', 'gender')
            ->toArray();
        $gender_counts = [
            'Male' => $gender_counts['Male'] ?? 0,
            'Female' => $gender_counts['Female'] ?? 0
        ];

        // Religion counts
        $religion_counts = Student::groupBy('religion')
            ->selectRaw('religion, COUNT(*) as religion_count')
            ->pluck('religion_count', 'religion')
            ->toArray();
        $religion_counts = [
            'Christianity' => $religion_counts['Christianity'] ?? 0,
            'Islam' => $religion_counts['Islam'] ?? 0,
            'Others' => $religion_counts['Others'] ?? 0
        ];

        // Calculate total population (total students)
        $total_population = Student::count();

        // Calculate staff count (assuming you have a Staff model)
        // Replace 'Staff' with your actual staff model name and table
        $staff_count = \App\Models\Staff::count(); // Adjust based on your Staff model

        return view('student.index', compact(
            'schoolclass',
            'schoolterm',
            'schoolsession',
            'status_counts',
            'gender_counts',
            'religion_counts',
            'pagetitle',
            'total_population',
            'staff_count'
        ));
    }
    
    public function show($id)
    {
        try {
            $student = Student::where('studentRegistration.id', $id)
                ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
                ->leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->leftJoin('studenthouse', 'studenthouse.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolhouse', 'schoolhouse.id', '=', 'studenthouse.schoolhouseid')
                ->leftJoin('studentpersonalityprofile', 'studentpersonalityprofile.studentId', '=', 'studentRegistration.id')
                ->where('schoolsession.status', 'Current')
                ->select([
                    'studentRegistration.id as id',
                    'studentRegistration.admissionNo as student_id', // Changed from admissionNo to match template
                    'studentRegistration.firstname as first_name',  // Changed to match template
                    'studentRegistration.lastname as last_name',    // Changed to match template
                    'studentRegistration.middle_name as middle_name',
                    'studentRegistration.gender as gender',
                    'studentRegistration.date_of_birth as date_of_birth',
                    'studentRegistration.blood_group as blood_group',
                    'studentRegistration.admission_date as admission_date',
                    'studentRegistration.home_address as address',  // Changed to match template
                    'studentRegistration.status as status',
                    'parentRegistration.father_phone as phoneNumber', // Changed to match template
                    'parentRegistration.firstName as parent_firstName',
                    'parentRegistration.lastName as parent_lastName',
                    'parentRegistration.email as parent_email',
                    'parentRegistration.address as parent_address',
                    'studentpicture.picture as picture',           // Changed to match template
                    'schoolclass.schoolclass as schoolclass',
                    'schoolarm.arm as arm',
                    'schoolterm.term as term',
                    'schoolsession.session as session',
                    'schoolhouse.schoolhouse as schoolhouse',
                    'studentpersonalityprofile.traits as traits',
                    'studentpersonalityprofile.strengths as strengths',
                    'studentpersonalityprofile.weaknesses as weaknesses',
                    'studentpersonalityprofile.comments as comments',
                ])
                ->firstOrFail();

            $billPayments = StudentBillPayment::where('student_id', $id)
                ->with(['schoolBill', 'studentBillPaymentRecords'])
                ->get();
            $billPaymentBooks = StudentBillPaymentBook::where('student_id', $id)->get();

            // Pass only $student, $billPayments, and $billPaymentBooks to the view
            return view('students.show', compact('student', 'billPayments', 'billPaymentBooks'));
        } catch (\Exception $e) {
            return redirect()->route('student.index')->with('error', 'Student not found.');
        }
    }

    public function create()
    {
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('sdesc');
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('student.create')
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession);
    }

    public function store(Request $request): JsonResponse
    {
        Log::debug('Creating new student', $request->all());

        try {
            $statesLgas = json_decode(file_get_contents(public_path('states_lgas.json')), true);
            $states = array_column($statesLgas, 'state');
            $lgas = collect($statesLgas)->pluck('lgas', 'state')->toArray();

            $validator = Validator::make($request->all(), [
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'admissionNo' => 'required|unique:studentRegistration,admissionNo',
                'title' => 'required|in:Mr,Mrs,Miss',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'othername' => 'nullable|string|max:255',
                'gender' => 'required|in:Male,Female',
                'home_address' => 'required|string|max:255',
                'home_address2' => 'required|string|max:255',
                'dateofbirth' => 'required|date|before:today',
                'age' => 'required|integer|min:1|max:100',
                'placeofbirth' => 'required|string|max:255',
                'nationality' => 'required|string|max:255',
                'state' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($states) {
                    if (!in_array($value, $states)) {
                        $fail('The selected state is invalid.');
                    }
                }],
                'local' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($request, $lgas) {
                    $state = $request->input('state');
                    if (!isset($lgas[$state]) || !in_array($value, $lgas[$state])) {
                        $fail('The selected local government is invalid for the chosen state.');
                    }
                }],
                'religion' => 'required|in:Christianity,Islam,Others',
                'last_school' => 'required|string|max:255',
                'last_class' => 'required|string|max:255',
                'schoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'statusId' => 'required|in:1,2'
            ]);

            if ($validator->fails()) {
                Log::debug('Validation failed', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $student = new Student();
            $student->admissionNo = $request->admissionNo;
            $student->title = $request->title;
            $student->firstname = $request->firstname;
            $student->lastname = $request->lastname;
            $student->othername = $request->othername;
            $student->gender = $request->gender;
            $student->home_address = $request->home_address;
            $student->home_address2 = $request->home_address2;
            $student->dateofbirth = $request->dateofbirth;
            $student->age = $request->age;
            $student->placeofbirth = $request->placeofbirth;
            $student->nationality = $request->nationality;
            $student->state = $request->state;
            $student->local = $request->local;
            $student->religion = $request->religion;
            $student->last_school = $request->last_school;
            $student->last_class = $request->last_class;
            $student->statusId = $request->statusId;
            $student->registeredBy = auth()->user()->id;
            $student->save();

            $studentId = $student->id;

            $studentClass = new Studentclass();
            $studentClass->studentId = $studentId;
            $studentClass->schoolclassid = $request->schoolclassid;
            $studentClass->termid = $request->termid;
            $studentClass->sessionid = $request->sessionid;
            $studentClass->save();

            $promotion = new PromotionStatus();
            $promotion->studentId = $studentId;
            $promotion->schoolclassid = $request->schoolclassid;
            $promotion->termid = $request->termid;
            $promotion->sessionid = $request->sessionid;
            $promotion->promotionStatus = 'PROMOTED';
            $promotion->classstatus = 'CURRENT';
            $promotion->save();

            $parent = new ParentRegistration();
            $parent->studentId = $studentId;
            $parent->save();

            $picture = new Studentpicture();
            $picture->studentid = $studentId;
            if ($request->hasFile('avatar')) {
                if ($request->file('avatar')->isValid()) {
                    $filename = $studentId . '_' . time() . '.' . $request->file('avatar')->getClientOriginalExtension();
                    $path = $request->file('avatar')->storeAs('student_avatars', $filename, 'public');
                    Log::debug('Avatar stored', [
                        'filename' => $filename,
                        'path' => $path,
                        'full_path' => storage_path('app/public/' . $path),
                        'url' => asset('storage/' . $path)
                    ]);
                    $picture->picture = $path;
                } else {
                    Log::error('Invalid avatar file uploaded');
                    throw new \Exception('Invalid avatar file uploaded');
                }
            } else {
                Log::debug('No avatar file provided in request');
            }
            $picture->save();

            $studenthouse = new Studenthouse();
            $studenthouse->studentid = $studentId;
            $studenthouse->termid = $request->termid;
            $studenthouse->sessionid = $request->sessionid;
            $studenthouse->save();

            $studentpersonalityprofile = new Studentpersonalityprofile();
            $studentpersonalityprofile->studentid = $studentId;
            $studentpersonalityprofile->schoolclassid = $request->schoolclassid;
            $studentpersonalityprofile->termid = $request->termid;
            $studentpersonalityprofile->sessionid = $request->sessionid;
            $studentpersonalityprofile->save();

            DB::commit();

            Log::debug("Student created successfully: ID {$studentId}");
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'image_url' => $picture->picture ? asset('storage/' . $picture->picture) : null
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($student)
    {
        try {
            $studentData = DB::table('studentRegistration')
                ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
                ->leftJoin('studentclass', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->select(
                    'studentRegistration.id',
                    'studentRegistration.admissionNo',
                    'studentRegistration.title',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.othername',
                    'studentRegistration.gender',
                    'studentRegistration.home_address',
                    'studentRegistration.home_address2',
                    'studentRegistration.dateofbirth',
                    'studentRegistration.age',
                    'studentRegistration.placeofbirth',
                    'studentRegistration.nationality',
                    'studentRegistration.state',
                    'studentRegistration.local',
                    'studentRegistration.religion',
                    'studentRegistration.last_school',
                    'studentRegistration.last_class',
                    'studentRegistration.statusId',
                    'studentclass.schoolclassid',
                    'studentclass.termid',
                    'studentclass.sessionid',
                    'studentpicture.picture'
                )
                ->where('studentRegistration.id', $student)
                ->first();

            if (!$studentData) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            return response()->json(['success' => true, 'student' => $studentData]);
        } catch (\Exception $e) {
            Log::error('Error fetching student ID ' . $student . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $student): JsonResponse
    {
        try {
            $statesLgas = json_decode(file_get_contents(public_path('states_lgas.json')), true);
            $states = array_column($statesLgas, 'state');
            $lgas = collect($statesLgas)->pluck('lgas', 'state')->toArray();

            $validator = Validator::make($request->all(), [
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'admissionNo' => 'required|string|max:255|unique:studentRegistration,admissionNo,' . $student,
                'title' => 'required|in:Mr,Mrs,Miss',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'othername' => 'nullable|string|max:255',
                'gender' => 'required|in:Male,Female',
                'home_address' => 'required|string|max:255',
                'home_address2' => 'required|string|max:255',
                'dateofbirth' => 'required|date|before:today',
                'placeofbirth' => 'required|string|max:255',
                'nationality' => 'required|string|max:255',
                'state' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($states) {
                    if (!in_array($value, $states)) {
                        $fail('The selected state is invalid.');
                    }
                }],
                'local' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($request, $lgas) {
                    $state = $request->input('state');
                    if (!isset($lgas[$state]) || !in_array($value, $lgas[$state])) {
                        $fail('The selected local government is invalid for the chosen state.');
                    }
                }],
                'religion' => 'required|in:Christianity,Islam,Others',
                'last_school' => 'required|string|max:255',
                'last_class' => 'required|string|max:255',
                'schoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'statusId' => 'required|in:1,2'
            ]);

            if ($validator->fails()) {
                Log::debug('Validation failed', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Calculate age based on dateofbirth
            $birthDate = new \DateTime($request->dateofbirth);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;

            $updateData = [
                'admissionNo' => $request->admissionNo,
                'title' => $request->title,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'othername' => $request->othername,
                'gender' => $request->gender,
                'home_address' => $request->home_address,
                'home_address2' => $request->home_address2,
                'dateofbirth' => $request->dateofbirth,
                'age' => $age,
                'placeofbirth' => $request->placeofbirth,
                'nationality' => $request->nationality,
                'state' => $request->state,
                'local' => $request->local,
                'religion' => $request->religion,
                'last_school' => $request->last_school,
                'last_class' => $request->last_class,
                'statusId' => $request->statusId,
                'updated_at' => now(),
            ];

            DB::table('studentRegistration')
                ->where('id', $student)
                ->update($updateData);

            DB::table('studentclass')->updateOrInsert(
                ['studentId' => $student],
                [
                    'schoolclassid' => $request->schoolclassid,
                    'termid' => $request->termid,
                    'sessionid' => $request->sessionid,
                    'updated_at' => now(),
                ]
            );

            if ($request->hasFile('avatar')) {
                if ($request->file('avatar')->isValid()) {
                    // Delete existing picture if it exists
                    $existingPicture = DB::table('studentpicture')->where('studentid', $student)->first();
                    if ($existingPicture && $existingPicture->picture) {
                        Storage::disk('public')->delete($existingPicture->picture);
                        Log::debug('Deleted existing avatar', ['path' => $existingPicture->picture]);
                    }

                    // Store new avatar
                    $filename = $student . '_' . time() . '.' . $request->file('avatar')->getClientOriginalExtension();
                    $path = $request->file('avatar')->storeAs('student_avatars', $filename, 'public');
                    if (!Storage::disk('public')->exists($path)) {
                        Log::error('Failed to store avatar', ['path' => $path]);
                        throw new \Exception('Failed to store avatar file');
                    }
                    Log::debug('Avatar stored', [
                        'filename' => $filename,
                        'path' => $path,
                        'full_path' => storage_path('app/public/' . $path),
                        'url' => asset('storage/' . $path)
                    ]);

                    // Update or insert picture record
                    DB::table('studentpicture')->updateOrInsert(
                        ['studentid' => $student],
                        ['picture' => $path, 'updated_at' => now()]
                    );
                } else {
                    Log::error('Invalid avatar file uploaded');
                    throw new \Exception('Invalid avatar file uploaded');
                }
            } else {
                Log::debug('No avatar file provided in request');
            }

            // Update related tables
            DB::table('promotionStatus')->updateOrInsert(
                ['studentId' => $student],
                [
                    'schoolclassid' => $request->schoolclassid,
                    'termid' => $request->termid,
                    'sessionid' => $request->sessionid,
                    'promotionStatus' => 'PROMOTED',
                    'classstatus' => 'CURRENT',
                    'updated_at' => now(),
                ]
            );

            DB::table('studenthouses')->updateOrInsert(
                ['studentid' => $student],
                [
                    'termid' => $request->termid,
                    'sessionid' => $request->sessionid,
                    'updated_at' => now(),
                ]
            );

            DB::table('studentpersonalityprofiles')->updateOrInsert(
                ['studentid' => $student],
                [
                    'schoolclassid' => $request->schoolclassid,
                    'termid' => $request->termid,
                    'sessionid' => $request->sessionid,
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            $imageUrl = null;
            $picture = DB::table('studentpicture')->where('studentid', $student)->first();
            if ($picture && $picture->picture) {
                $imageUrl = asset('storage/' . $picture->picture);
            }

            Log::debug("Student updated successfully: ID {$student}");
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'image_url' => $imageUrl
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating student ID {$student}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        Log::debug("Deleting student ID {$id}");

        try {
            DB::beginTransaction();

            $student = Student::findOrFail($id);
            $picture = Studentpicture::where('studentid', $id)->first();
            if ($picture && $picture->picture) {
                Storage::delete('public/' . $picture->picture);
            }

            Studentclass::where('studentId', $id)->delete();
            PromotionStatus::where('studentId', $id)->delete();
            ParentRegistration::where('studentId', $id)->delete();
            Studentpicture::where('studentid', $id)->delete();
             // Delete broadsheet-related records
            $broadsheetRecords = BroadsheetRecord::where('student_id', $id)->get();
            foreach ($broadsheetRecords as $record) {
                Broadsheets::where('broadsheet_record_id', $id)->delete();
                $record->delete();
                Log::debug("Deleted broadsheet record ID {$record->id} for student ID {$id}");
            }

            SubjectRegistrationStatus::where('studentId', $id)->delete();
            Studenthouse::where('studentid', $id)->delete();
            Studentpersonalityprofile::where('studentid', $id)->delete();
            $student->delete();

            DB::commit();

            Log::debug("Student deleted successfully: ID {$id}");
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyMultiple(Request $request): JsonResponse
    {
        Log::debug('Bulk deleting students', $request->all());

        try {
            $ids = $request->validate(['ids' => 'required|array|exists:studentRegistration,id'])['ids'];
            DB::beginTransaction();

            foreach ($ids as $id) {
                $picture = Studentpicture::where('studentid', $id)->first();
                if ($picture && $picture->picture) {
                    Storage::delete('public/' . $picture->picture);
                }

                Studentclass::where('studentId', $id)->delete();
                PromotionStatus::where('studentId', $id)->delete();
                Parentregistration::where('studentId', $id)->delete();
                Studentpicture::where('studentid', $id)->delete();
                Broadsheet::where('studentId', $id)->delete();
                SubjectRegistrationStatus::where('studentId', $id)->delete();
                Studenthouse::where('studentid', $id)->delete();
                Studentpersonalityprofile::where('studentid', $id)->delete();
            }

            Student::whereIn('id', $ids)->delete();

            DB::commit();

            Log::debug('Bulk deleted students: ' . implode(',', $ids));
            return response()->json([
                'success' => true,
                'message' => 'Students deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk delete error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete students: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletestudent(Request $request)
    {
        $s = $request->input('id');
        try {
            DB::beginTransaction();

            $student = Student::findOrFail($s);
            $picture = Studentpicture::where('studentid', $s)->first();
            if ($picture && $picture->picture) {
                Storage::delete('public/' . $picture->picture);
            }

            Studentclass::where('studentId', $s)->delete();
            PromotionStatus::where('studentId', $s)->delete();
            Parentregistration::where('studentId', $s)->delete();
            Studentpicture::where('studentid', $s)->delete();
            Broadsheet::where('studentId', $s)->delete();
            SubjectRegistrationStatus::where('studentId', $s)->delete();
            Studenthouse::where('studentid', $s)->delete();
            Studentpersonalityprofile::where('studentid', $s)->delete();
            $student->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student has been removed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting student ID {$s}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Student not found or could not be deleted'
            ]);
        }
    }

      public function deletestudentbatch(Request $request): JsonResponse
    {
        $batchId = $request->input('studentbatchid');
        Log::debug("Attempting to delete batch ID {$batchId}");

        try {
            // Validate the studentbatchid
            if (!$batchId) {
                throw new \Exception('Batch ID is missing in request');
            }

            // Verify table existence
            if (!Schema::hasTable('student_batch_upload')) {
                throw new \Exception('student_batch_upload table does not exist');
            }

            // Verify studentRegistration table and batchid column
            if (!Schema::hasTable('studentRegistration')) {
                throw new \Exception('studentRegistration table does not exist');
            }
            if (!Schema::hasColumn('studentRegistration', 'batchid')) {
                throw new \Exception('batchid column missing in studentRegistration table');
            }

            // Find the batch
            Log::debug("Querying StudentBatchModel for ID {$batchId}");
            $batch = StudentBatchModel::findOrFail($batchId);
            Log::debug("Batch found: ID {$batch->id}, Title {$batch->title}");

            DB::beginTransaction();

            // Get student IDs associated with the batch
            Log::debug("Querying students for batch ID {$batch->id}");
            $studentIds = Student::where('batchid', $batch->id)->pluck('id');
            Log::debug("Found " . count($studentIds) . " students for batch ID {$batchId}: " . $studentIds->implode(','));

            // If no students are found, log and proceed
            if ($studentIds->isEmpty()) {
                Log::warning("No students found for batch ID {$batchId}. Deleting batch only.");
            } else {
                // Delete related records for each student
                foreach ($studentIds as $studentId) {
                    Log::debug("Deleting related records for student ID {$studentId}");

                    // Delete student picture and associated file
                    $picture = Studentpicture::where('studentid', $studentId)->first();
                    if ($picture && $picture->picture) {
                        Storage::delete('public/' . $picture->picture);
                        Log::debug("Deleted picture for student ID {$studentId}");
                    }

                    // Delete broadsheet-related records
                    $broadsheetRecords = BroadsheetRecord::where('student_id', $studentId)->get();
                    foreach ($broadsheetRecords as $record) {
                        Broadsheets::where('broadsheet_record_id', $record->id)->delete();
                        $record->delete();
                        Log::debug("Deleted broadsheet record ID {$record->id} for student ID {$studentId}");
                    }

                    $broadsheetMockRecords = BroadsheetRecordMock::where('student_id', $studentId)->get();
                    foreach ($broadsheetMockRecords as $record) {
                        BroadsheetsMock::where('broadsheet_records_mock_id', $record->id)->delete();
                        $record->delete();
                        Log::debug("Deleted broadsheet mock record ID {$record->id} for student ID {$studentId}");
                    }

                    // Delete other related records
                    Studentclass::where('studentId', $studentId)->delete();
                    PromotionStatus::where('studentId', $studentId)->delete();
                    ParentRegistration::where('studentId', $studentId)->delete();
                    Studentpicture::where('studentid', $studentId)->delete();
                    SubjectRegistrationStatus::where('studentId', $studentId)->delete();
                    Studenthouse::where('studentid', $studentId)->delete();
                    Studentpersonalityprofile::where('studentid', $studentId)->delete();
                    Log::debug("Deleted other related records for student ID {$studentId}");
                }

                // Delete students associated with the batch
                Log::debug("Deleting students for batch ID {$batchId}");
                Student::where('batchid', $batch->id)->delete();
                Log::debug("Deleted students for batch ID {$batchId}");
            }

            // Delete the batch itself
            Log::debug("Deleting batch ID {$batchId}");
            $batch->delete();
            Log::debug("Deleted batch ID {$batchId}");

            DB::commit();

            Log::info("Batch ID {$batchId} and associated students (if any) deleted successfully");

            return response()->json([
                'success' => true,
                'message' => 'Batch Upload has been removed'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("Batch ID {$batchId} not found in student_batch_upload: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting batch ID {$batchId}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete batch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkupload()
    {
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('sdesc');
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('student.bulkupload')
            ->with('schoolclass', $schoolclass)
            ->with('schoolterm', $schoolterm)
            ->with('schoolsession', $schoolsession);
    }

    public function batchindex()
    {
         $pagetitle = "Student Batch Management";

        $batch = StudentBatchModel::leftJoin('schoolclass', 'schoolclass.id', '=', 'student_batch_upload.schoolclassid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_batch_upload.session')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'student_batch_upload.termid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->orderBy('upload_date', 'desc')
            ->get([
                'student_batch_upload.id as id',
                'student_batch_upload.title as title',
                'schoolclass.schoolclass as schoolclass',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'schoolarm.arm as arm',
                'student_batch_upload.status as status',
                'student_batch_upload.updated_at as upload_date',
            ]);

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('sdesc');
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('student.batchindex', compact('batch', 'schoolclass', 'schoolterm', 'schoolsession','pagetitle'));
    }

    public function bulkuploadsave(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'filesheet' => 'required|mimes:xlsx,csv,xls',
            'title' => 'required',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
            'schoolclassid' => 'required|exists:schoolclass,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $batchchk = StudentBatchModel::where('title', $request->title)->exists();
        if ($batchchk) {
            return redirect()
                ->back()
                ->with('success', 'Title is already chosen, Please choose another Title for this Batch Upload');
        }

        try {
            DB::beginTransaction();

            $batch = new StudentBatchModel();
            $batch->title = $request->title;
            $batch->schoolclassid = $request->schoolclassid;
            $batch->termid = $request->termid;
            $batch->session = $request->sessionid;
            $batch->status = '';
            $batch->save();

            session(['sclassid' => $request->schoolclassid, 'tid' => $request->termid, 'sid' => $request->sessionid, 'batchid' => $batch->id]);

            $file = $request->file('filesheet');
            $import = new StudentsImport();

            $import->import($file, null, \Maatwebsite\Excel\Excel::XLSX);
            StudentBatchModel::where('id', $batch->id)->update(['Status' => 'Success']);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Student Batch File Imported Successfully');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            StudentBatchModel::where('id', $batch->id)->update(['Status' => 'Failed']);
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return redirect()
                ->back()
                ->with('status', implode('; ', $errors));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error importing batch: {$e->getMessage()}");
            return redirect()
                ->back()
                ->with('status', 'Failed to import batch: ' . $e->getMessage());
        }
    }


    public function updateClass(Request $request)
{
    Log::debug('Updating class for batch', $request->all());

    try {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|exists:student_batch_upload,id',
            'schoolclass' => 'required|string|max:255',
            //'schoolclassid' => 'required|exists:schoolclass,id',
            'armid' => 'required|exists:schoolarm,id',
            'classcategoryid' => 'required|exists:classcategories,id',
           // 'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::debug('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        // Step 1: Update or create schoolclass
        $schoolClass = Schoolclass::updateOrCreate(
            ['id' => $request->schoolclassid],
            [
                'schoolclass' => $request->schoolclass,
                'arm' => $request->armid,
                'classcategoryid' => $request->classcategoryid,
                'description' => "testing",
            ]
        );
        Log::debug("Schoolclass updated/created: ID {$schoolClass->id}");

        // Step 2: Update batch
        $batch = StudentBatchModel::findOrFail($request->batch_id);
        $batch->update(['schoolclassid' => $schoolClass->id]);
       // Log::debug("Batch ID {$batch->id} updated");

        // Step 3: Get student IDs for batch
        $studentIds = Student::where('batchid', $batch->id)->pluck('id');
        Log::debug("Found " . count($studentIds) . " students for batch ID {$batch->id}");

        // Step 4: Bulk update broadsheet records
        BroadsheetRecord::whereIn('student_id', $studentIds)
            ->update(['schoolclass_id' => $schoolClass->id]);
        Log::debug("Bulk updated BroadsheetRecord for batch ID {$batch->id}");

        // Step 5: Bulk update broadsheet mock records
        BroadsheetRecordMock::whereIn('student_id', $studentIds)
            ->update(['schoolclass_id' => $schoolClass->id]);
        Log::debug("Bulk updated BroadsheetRecordMock for batch ID {$batch->id}");

        // Step 6: Bulk update subjectclass via broadsheet
        $broadsheetRecordIds = BroadsheetRecord::whereIn('student_id', $studentIds)->pluck('id');
        $subjectClassIdsFromBroadsheets = Broadsheets::whereIn('broadsheet_record_id', $broadsheetRecordIds)
            ->whereNotNull('subjectclass_id')
            ->pluck('subjectclass_id')
            ->unique();
        if ($subjectClassIdsFromBroadsheets->isNotEmpty()) {
            Subjectclass::whereIn('id', $subjectClassIdsFromBroadsheets)
                ->update(['schoolclassid' => $schoolClass->id]);
            Log::debug("Bulk updated Subjectclass for " . count($subjectClassIdsFromBroadsheets) . " broadsheet records");
        } else {
            Log::debug("No Subjectclass IDs found for broadsheet records");
        }

        // Step 7: Bulk update subjectclass for records linked to both broadsheet and broadsheet mock
        $broadsheetMockRecordIds = BroadsheetRecordMock::whereIn('student_id', $studentIds)->pluck('id');
        $subjectClassIdsFromBroadsheetsMock = BroadsheetsMock::whereIn('broadsheet_records_mock_id', $broadsheetMockRecordIds)
            ->whereNotNull('subjectclass_id')
            ->pluck('subjectclass_id')
            ->unique();
        $commonSubjectClassIds = $subjectClassIdsFromBroadsheets->intersect($subjectClassIdsFromBroadsheetsMock);
        if ($commonSubjectClassIds->isNotEmpty()) {
            Subjectclass::whereIn('id', $commonSubjectClassIds)
                ->update(['schoolclassid' => $schoolClass->id]);
            Log::debug("Bulk updated Subjectclass for " . count($commonSubjectClassIds) . " common subjectclass IDs");
        } else {
            Log::debug("No common Subjectclass IDs found for batch ID {$batch->id}");
        }

        DB::commit();

        Log::info("Class updated successfully for batch ID {$request->batch_id}");
        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully'
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();
        Log::error("Batch or related record not found: {$e->getMessage()}");
        return response()->json([
            'success' => false,
            'message' => 'Batch or related record not found'
        ], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error updating class for batch ID {$request->batch_id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
        return response()->json([
            'success' => false,
            'message' => 'Failed to update class: An unexpected error occurred'
        ], 500);
    }
}
}
