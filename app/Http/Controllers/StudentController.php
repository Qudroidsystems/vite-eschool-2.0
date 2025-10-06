<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Broadsheet;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Schoolhouse;
use App\Models\Studentclass;
use App\Models\Studenthouse;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Studenthouses;
use App\Models\Studentpicture;
use App\Imports\StudentsImport;
use App\Models\BroadsheetsMock;
use App\Models\PromotionStatus;
use Illuminate\Validation\Rule;
use App\Models\BroadsheetRecord;
use App\Models\StudentBatchModel;
use Illuminate\Http\JsonResponse;
use App\Models\ParentRegistration;
use App\Models\StudentBillPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BroadsheetRecordMock;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StudentBillPaymentBook;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Studentpersonalityprofile;
use App\Models\SubjectRegistrationStatus;
use Illuminate\Support\Facades\Validator;
use App\Models\Studentpersonalityprofiles;
use App\Traits\ImageManager as TraitsImageManager;

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
    }

    public function index(Request $request)
    {
        $pagetitle = "Student Management";

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display, schoolclass.schoolclass, schoolarm.arm")
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms = Schoolterm::select('id', 'term as name')->get();
        $schoolsessions = Schoolsession::select('id', 'session as name')->get();
        $currentSession = Schoolsession::where('status', 'Current')->first();
        $schoolhouses = Schoolhouse::all(); // Fetch all school houses

        // Status counts
        $status_counts = Student::groupBy('statusId')
            ->selectRaw("CASE WHEN statusId = 1 THEN 'Old Student' ELSE 'New Student' END as student_status, COUNT(*) as student_count")
            ->pluck('student_count', 'student_status')
            ->toArray();
        $status_counts = [
            'Old Student' => $status_counts['Old Student'] ?? 0,
            'New Student' => $status_counts['New Student'] ?? 0
        ];

        // Active/Inactive counts
        $student_status_counts = Student::groupBy('student_status')
            ->selectRaw('student_status, COUNT(*) as status_count')
            ->pluck('status_count', 'student_status')
            ->toArray();
        $student_status_counts = [
            'Active' => $student_status_counts['Active'] ?? 0,
            'Inactive' => $student_status_counts['Inactive'] ?? 0
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

        // Total population
        $total_population = Student::count();

        // Staff count
        $staff_count = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereNotIn('name', ['Student']);
        })->count();

        return view('student.index', compact(
            'schoolclasses',
            'schoolterms',
            'schoolsessions',
            'schoolhouses',
            'currentSession',
            'status_counts',
            'student_status_counts',
            'gender_counts',
            'religion_counts',
            'pagetitle',
            'total_population',
            'staff_count'
        ));
    }

   public function store(Request $request)
    {
        Log::debug('Creating new student', $request->all());

        try {
            $statesLgas = json_decode(file_get_contents(public_path('states_lgas.json')), true);
            $states = array_column($statesLgas, 'state');
            $lgas = collect($statesLgas)->pluck('lgas', 'state')->toArray();

            $validator = Validator::make($request->all(), [
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'admissionMode' => 'required|in:auto,manual',
                'title' => 'nullable|in:Master,Miss',
                'admissionNo' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:studentRegistration,admissionNo',
                    Rule::when($request->admissionMode === 'auto', [
                        'regex:/^CSSK\/STD\/\d{4}\/\d{4}$/' // Ensure format like CSSK/STD/YYYY/0871
                    ])
                ],
                'admissionYear' => 'required|integer|min:1900|max:' . date('Y'),
                'admissionDate' => 'required|date|before_or_equal:today',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'othername' => 'nullable|string|max:255',
                'gender' => 'required|in:Male,Female',
                'dateofbirth' => 'required|date|before:today',
                'placeofbirth' => 'required|string|max:255',
                'nationality' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:100',
                'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'mother_tongue' => 'nullable|string|max:255',
                'religion' => 'required|in:Christianity,Islam,Others',
                'sport_house' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'nin_number' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:255',
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
                'future_ambition' => 'required|string|max:500',
                'permanent_address' => 'required|string|max:255',
                'student_category' => 'required|in:Day,Boarding',
                'schoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'statusId' => 'required|in:1,2',
                'student_status' => 'required|in:Active,Inactive',
                'father_title' => 'nullable|in:Mr,Dr,Prof',
                'mother_title' => 'nullable|in:Mrs,Dr,Prof',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'father_occupation' => 'nullable|string|max:255',
                'father_city' => 'nullable|string|max:255',
                'office_address' => 'nullable|string|max:255',
                'father_phone' => 'nullable|string|max:20',
                'mother_phone' => 'nullable|string|max:20',
                'parent_email' => 'nullable|email|max:255',
                'parent_address' => 'nullable|string|max:255',
                'last_school' => 'nullable|string|max:255',
                'last_class' => 'nullable|string|max:255',
                'reason_for_leaving' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for student creation', ['errors' => $validator->errors()->toArray()]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                return redirect()->route('student.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $student = new Student();
            if ($request->admissionMode === 'auto') {
                // Call getLastAdmissionNumber to generate admission number
                $admissionResponse = $this->getLastAdmissionNumber(new Request(['year' => $request->admissionYear]));
                $admissionData = json_decode($admissionResponse->getContent(), true);
                if (!$admissionData['success']) {
                    throw new \Exception('Failed to generate admission number: ' . $admissionData['message']);
                }
                $student->admissionNo = $admissionData['admissionNo'];
            } else {
                $student->admissionNo = $request->admissionNo;
            }
            $student->admission_date = $request->admissionDate;
            $student->title = $request->title;
            $student->admissionYear = $request->admissionYear;
            $student->firstname = $request->firstname;
            $student->lastname = $request->lastname;
            $student->othername = $request->othername;
            $student->gender = $request->gender;
            $student->dateofbirth = $request->dateofbirth;
            $student->age = $request->age;
            $student->blood_group = $request->blood_group;
            $student->mother_tongue = $request->mother_tongue;
            $student->religion = $request->religion;
            $student->sport_house = $request->sport_house;
            $student->phone_number = $request->phone_number;
            $student->email = $request->email;
            $student->nin_number = $request->nin_number;
            $student->city = $request->city;
            $student->state = $request->state;
            $student->local = $request->local;
            $student->nationality = $request->nationality;
            $student->placeofbirth = $request->placeofbirth;
            $student->future_ambition = $request->future_ambition;
            $student->home_address2 = $request->permanent_address;
            $student->student_category = $request->student_category;
            $student->statusId = $request->statusId;
            $student->student_status = $request->student_status;
            $student->last_school = $request->last_school;
            $student->last_class = $request->last_class;
            $student->reason_for_leaving = $request->reason_for_leaving;
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
            $parent->father_title = $request->father_title;
            $parent->mother_title = $request->mother_title;
            $parent->father = $request->father_name;
            $parent->mother = $request->mother_name;
            $parent->father_phone = $request->father_phone;
            $parent->mother_phone = $request->mother_phone;
            $parent->father_occupation = $request->father_occupation;
            $parent->father_city = $request->father_city;
            $parent->office_address = $request->office_address;
            $parent->parent_email = $request->parent_email;
            $parent->parent_address = $request->parent_address;
            $parent->save();

            $picture = new Studentpicture();
            $picture->studentid = $studentId;
            if ($request->hasFile('avatar')) {
                $path = $this->storeImage($request->file('avatar'), 'images/student_avatars');
                $picture->picture = basename($path);
            } else {
                $picture->picture = 'unnamed.jpg';
            }
            $picture->save();

            $studenthouses = new Studenthouse();
            $studenthouses->studentid = $studentId;
            $studenthouses->termid = $request->termid;
            $studenthouses->sessionid = $request->sessionid;
            $studenthouses->save();

            $studentpersonalityprofiles = new Studentpersonalityprofile();
            $studentpersonalityprofiles->studentid = $studentId;
            $studentpersonalityprofiles->schoolclassid = $request->schoolclassid;
            $studentpersonalityprofiles->termid = $request->termid;
            $studentpersonalityprofiles->sessionid = $request->sessionid;
            $studentpersonalityprofiles->save();

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student created successfully',
                    'student' => [
                        'id' => $student->id,
                        'admissionNo' => $student->admissionNo,
                        'admissionYear' => $student->admissionYear,
                        'title' => $student->title,
                        'firstname' => $student->firstname,
                        'lastname' => $student->lastname,
                        'othername' => $student->othername,
                        'gender' => $student->gender,
                        'dateofbirth' => $student->dateofbirth,
                        'placeofbirth' => $student->placeofbirth,
                        'nationality' => $student->nationality,
                        'religion' => $student->religion,
                        'last_school' => $student->last_school,
                        'last_class' => $student->last_class,
                        'schoolclassid' => $student->schoolclassid,
                        'termid' => $student->termid,
                        'sessionid' => $student->sessionid,
                        'phone_number' => $student->phone_number,
                        'nin_number' => $student->nin_number,
                        'blood_group' => $student->blood_group,
                        'mother_tongue' => $student->mother_tongue,
                        'father_name' => $request->father_name,
                        'father_phone' => $request->father_phone,
                        'father_occupation' => $request->father_occupation,
                        'mother_name' => $request->mother_name,
                        'mother_phone' => $request->mother_phone,
                        'parent_address' => $request->parent_address,
                        'student_category' => $student->student_category,
                        'reason_for_leaving' => $student->reason_for_leaving,
                        'picture' => $picture->picture,
                        'state' => $student->state,
                        'local' => $student->local,
                        'statusId' => $student->statusId,
                        'student_status' => $student->student_status,
                        'future_ambition' => $student->future_ambition,
                        'permanent_address' => $student->home_address2,
                        'schoolclass' => $studentClass->schoolclass->name ?? '',
                        'arm' => $studentClass->schoolclass->arm ?? ''
                    ]
                ], 201);
            }

            return redirect()->route('student.index')
                ->with('success', 'Student created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating student: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create student: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('student.index')
                ->with('error', 'Failed to create student: ' . $e->getMessage());
        }
    }

    protected function storeImage($file, $directory)
    {
        try {
            $path = $file->store($directory, 'public');
            Log::debug('Image stored', ['path' => $path]);
            return $path;
        } catch (\Exception $e) {
            Log::error("Error storing image: {$e->getMessage()}");
            throw $e;
        }
    }

    public function data(Request $request): JsonResponse
    {
        try {
            Log::debug('Fetching students data');
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
                    'studentRegistration.student_status',
                    'studentRegistration.created_at',
                    'studentpicture.picture',
                    'schoolclass.schoolclass',
                    'schoolarm.arm',
                    'studentclass.schoolclassid',
                ])
                ->latest()
                ->get();

            Log::debug('Students fetched', ['count' => $students->count()]);

            return response()->json([
                'success' => true,
                'students' => $students,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching students: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage(),
            ], 500);
        }
    }

   

    protected function generateAdmissionNumber()
    {
        $lastAdmission = Student::max('admissionNo');
        $year = date('Y');
        $number = $lastAdmission ? (int)substr($lastAdmission, -4) + 1 : 1;
        return sprintf('CSSK/STD/2025/%04d', $number);
    }

    public function show($id)
    {
        try {
            $student = Student::where('studentRegistration.id', $id)
                ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
                ->leftJoin('parentRegistration', 'parentRegistration.studentId', '=', 'studentRegistration.id')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->leftJoin('studenthouses', 'studenthouses.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolhouses', 'schoolhouses.id', '=', 'studenthouses.schoolhouse')
                ->leftJoin('studentpersonalityprofiles', 'studentpersonalityprofiles.studentId', '=', 'studentRegistration.id')
                ->where('schoolsession.status', 'Current')
                ->select([
                    'studentRegistration.id as id',
                    'studentRegistration.admissionNo as student_id',
                    'studentRegistration.firstname as first_name',
                    'studentRegistration.lastname as last_name',
                    'studentRegistration.othername as middle_name',
                    'studentRegistration.gender as gender',
                    'studentRegistration.dateofbirth as date_of_birth',
                    'studentRegistration.blood_group as blood_group',
                    'studentRegistration.admission_date as admission_date',
                    'studentRegistration.student_category as student_category',
                    'studentRegistration.mother_tongue as mother_tongue',
                    'studentRegistration.sport_house as sport_house',
                    'studentRegistration.phone_number as phone_number',
                    'studentRegistration.email as email',
                    'studentRegistration.nin_number as nin_number',
                    'studentRegistration.city as city',
                    'studentRegistration.present_address as present_address',
                    'studentRegistration.permanent_address as permanent_address',
                    'studentRegistration.statusId as statusId',
                    'studentRegistration.student_status as student_status',
                    'parentRegistration.father_name as father_name',
                    'parentRegistration.mother_name as mother_name',
                    'parentRegistration.father_occupation as father_occupation',
                    'parentRegistration.father_city as father_city',
                    'parentRegistration.father_phone as father_phone',
                    'parentRegistration.mother_phone as mother_phone',
                    'parentRegistration.email as parent_email',
                    'parentRegistration.address as parent_address',
                    'studentRegistration.last_school as last_school',
                    'studentRegistration.last_class as last_class',
                    'studentRegistration.reason_for_leaving as reason_for_leaving',
                    'studentpicture.picture as picture',
                    'schoolclass.schoolclass as schoolclass',
                    'schoolarm.arm as arm',
                    'schoolterm.term as term',
                    'schoolsession.session as session',
                    'schoolhouses.house as schoolhouse',
       
                ])
                ->firstOrFail();

            $billPayments = StudentBillPayment::where('student_id', $id)
                ->with(['schoolBill', 'studentBillPaymentRecords'])
                ->get();
            $billPaymentBooks = StudentBillPaymentBook::where('student_id', $id)->get();

            return view('student.show', compact('student', 'billPayments', 'billPaymentBooks'));
        } catch (\Exception $e) {
            return redirect()->route('student.index')->with('error', 'Student not found.');
        }
    }

    public function create()
    {
        $pagetitle = "Create Student";
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display, schoolclass.schoolclass, schoolarm.arm")
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms = Schoolterm::select('id', 'term as name')->get();
        $schoolsessions = Schoolsession::select('id', 'session as name')->get();
        $currentSession = Schoolsession::where('status', 'Current')->first();

        return view('student.create', compact('schoolclasses', 'schoolterms', 'schoolsessions', 'currentSession', 'pagetitle'));
    }

    public function edit($student)
    {
        try {
            $studentData = Student::where('studentRegistration.id', $student)
                ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
                ->leftJoin('studentclass', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->leftJoin('parentRegistration', 'studentRegistration.id', '=', 'parentRegistration.studentId')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->leftJoin('studenthouses', 'studenthouses.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolhouses', 'schoolhouses.id', '=', 'studenthouses.schoolhouse')
                ->leftJoin('studentpersonalityprofiles', 'studentpersonalityprofiles.studentId', '=', 'studentRegistration.id')
                ->select([
                    'studentRegistration.id',
                    'studentRegistration.admissionNo',
                    'studentRegistration.admissionYear',
                    'studentRegistration.admission_date as admissionDate',
                    'studentRegistration.title',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.othername',
                    'studentRegistration.gender',
                    'studentRegistration.dateofbirth',
                    'studentRegistration.age',
                    'studentRegistration.blood_group',
                    'studentRegistration.mother_tongue',
                    'studentRegistration.religion',
                    'studentRegistration.sport_house',
                    'studentRegistration.phone_number',
                    'studentRegistration.email',
                    'studentRegistration.nin_number',
                    'studentRegistration.city',
                    'studentRegistration.state',
                    'studentRegistration.local',
                    'studentRegistration.nationality',
                    'studentRegistration.placeofbirth',
                    'studentRegistration.future_ambition', // Changed from home_address as present_address
                    'studentRegistration.home_address2 as permanent_address',
                    'studentRegistration.student_category',
                    'studentRegistration.statusId',
                    'studentRegistration.student_status',
                    'studentRegistration.last_school',
                    'studentRegistration.last_class',
                    'studentRegistration.reason_for_leaving',
                    'studentclass.schoolclassid',
                    'studentclass.termid',
                    'studentclass.sessionid',
                    'parentRegistration.father_title',
                    'parentRegistration.mother_title',
                    'parentRegistration.father as father_name',
                    'parentRegistration.mother as mother_name',
                    'parentRegistration.father_occupation',
                    'parentRegistration.father_city',
                    'parentRegistration.office_address',
                    'parentRegistration.father_phone',
                    'parentRegistration.mother_phone',
                    'parentRegistration.parent_email',
                    'parentRegistration.parent_address',
                    'studentpicture.picture',
                    'studenthouses.schoolhouse',
              
                ])
                ->first();

            if (!$studentData) {
                Log::warning("Student ID {$student} not found");
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            // Convert picture path to URL if exists
            $studentData->picture = $studentData->picture ? asset('storage/' . $studentData->picture) : null;

            return response()->json([
                'success' => true,
                'student' => $studentData
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching student ID {$student}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function update(Request $request, $id): JsonResponse
    {
        Log::debug('Updating student', ['id' => $id, 'data' => $request->all()]);

        try {
            $statesLgas = json_decode(file_get_contents(public_path('states_lgas.json')), true);
            $states = array_column($statesLgas, 'state');
            $lgas = collect($statesLgas)->pluck('lgas', 'state')->toArray();

            $validator = Validator::make($request->all(), [
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'admissionMode' => 'required|in:auto,manual',
                'admissionNo' => 'required|string|max:255|unique:studentRegistration,admissionNo,' . $id,
                'admissionYear' => 'required|integer|min:1900|max:' . date('Y'),
                'admissionDate' => 'required|date|before_or_equal:today',
                'title' => 'nullable|in:Master,Miss',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'othername' => 'nullable|string|max:255',
                'gender' => 'required|in:Male,Female',
                'dateofbirth' => 'required|date|before:today',
                'placeofbirth' => 'required|string|max:255',
                'nationality' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:100',
                'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'mother_tongue' => 'nullable|string|max:255',
                'religion' => 'required|in:Christianity,Islam,Others',
                'sport_house' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'nin_number' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:255',
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
                'future_ambition' => 'required|string|max:500', // Changed from present_address
                'permanent_address' => 'required|string|max:255',
                'student_category' => 'required|in:Day,Boarding',
                'schoolclassid' => 'required|exists:schoolclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
                'statusId' => 'required|in:1,2',
                'student_status' => 'required|in:Active,Inactive',
                'father_title' => 'nullable|in:Mr,Dr,Prof',
                'mother_title' => 'nullable|in:Mrs,Dr,Prof',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'father_occupation' => 'nullable|string|max:255',
                'father_city' => 'nullable|string|max:255',
                'office_address' => 'nullable|string|max:255',
                'father_phone' => 'nullable|string|max:20',
                'mother_phone' => 'nullable|string|max:20',
                'parent_email' => 'nullable|email|max:255',
                'parent_address' => 'nullable|string|max:255',
                'last_school' => 'nullable|string|max:255',
                'last_class' => 'nullable|string|max:255',
                'reason_for_leaving' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for student update', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $student = Student::findOrFail($id);
            $student->admissionNo = $request->admissionMode === 'auto' ? $this->generateAdmissionNumber() : $request->admissionNo;
            $student->admission_date = $request->admissionDate;
            $student->title = $request->title;
            $student->admissionYear = $request->admissionYear;
            $student->firstname = $request->firstname;
            $student->lastname = $request->lastname;
            $student->othername = $request->othername;
            $student->gender = $request->gender;
            $student->dateofbirth = $request->dateofbirth;
            $student->age = $request->age;
            $student->blood_group = $request->blood_group;
            $student->mother_tongue = $request->mother_tongue;
            $student->religion = $request->religion;
            $student->sport_house = $request->sport_house;
            $student->phone_number = $request->phone_number;
            $student->email = $request->email;
            $student->nin_number = $request->nin_number;
            $student->city = $request->city;
            $student->state = $request->state;
            $student->local = $request->local;
            $student->nationality = $request->nationality;
            $student->placeofbirth = $request->placeofbirth;
            $student->future_ambition = $request->future_ambition; // Changed from home_address
            $student->home_address2 = $request->permanent_address;
            $student->student_category = $request->student_category;
            $student->statusId = $request->statusId;
            $student->student_status = $request->student_status;
            $student->last_school = $request->last_school;
            $student->last_class = $request->last_class;
            $student->reason_for_leaving = $request->reason_for_leaving;
            $student->registeredBy = auth()->user()->id;
            $student->save();

            $studentClass = Studentclass::where('studentId', $id)->firstOrFail();
            $studentClass->schoolclassid = $request->schoolclassid;
            $studentClass->termid = $request->termid;
            $studentClass->sessionid = $request->sessionid;
            $studentClass->save();

            $promotion = PromotionStatus::where('studentId', $id)->firstOrFail();
            $promotion->schoolclassid = $request->schoolclassid;
            $promotion->termid = $request->termid;
            $promotion->sessionid = $request->sessionid;
            $promotion->promotionStatus = 'PROMOTED';
            $promotion->classstatus = 'CURRENT';
            $promotion->save();

            $parent = ParentRegistration::where('studentId', $id)->firstOrFail();
            $parent->father_title = $request->father_title;
            $parent->mother_title = $request->mother_title;
            $parent->father = $request->father_name;
            $parent->mother = $request->mother_name;
            $parent->father_phone = $request->father_phone;
            $parent->mother_phone = $request->mother_phone;
            $parent->father_occupation = $request->father_occupation;
            $parent->father_city = $request->father_city;
            $parent->office_address = $request->office_address;
            $parent->parent_email = $request->parent_email;
            $parent->parent_address = $request->parent_address;
            $parent->save();

            $picture = Studentpicture::where('studentid', $id)->firstOrFail();
            if ($request->hasFile('avatar')) {
                if ($picture->picture && $picture->picture !== 'unnamed.jpg' && Storage::exists('public/images/student_avatars/' . $picture->picture)) {
                    Storage::delete('public/images/student_avatars/' . $picture->picture);
                }
                $path = $this->storeImage($request->file('avatar'), 'images/student_avatars');
                $picture->picture = basename($path);
            }
            $picture->save();

            $studenthouses = Studenthouse::where('studentid', $id)->firstOrFail();
            $studenthouses->termid = $request->termid;
            $studenthouses->sessionid = $request->sessionid;
            $studenthouses->schoolhouse = $request->sport_house ? DB::table('schoolhouses')->where('schoolhouses', $request->sport_house)->value('id') : null;
            $studenthouses->save();

            $studentpersonalityprofiles = Studentpersonalityprofile::where('studentid', $id)->firstOrFail();
            $studentpersonalityprofiles->schoolclassid = $request->schoolclassid;
            $studentpersonalityprofiles->termid = $request->termid;
            $studentpersonalityprofiles->sessionid = $request->sessionid;
            $studentpersonalityprofiles->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'student' => [
                    'id' => $student->id,
                    'admissionNo' => $student->admissionNo,
                    'admissionYear' => $student->admissionYear,
                    'title' => $student->title,
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'othername' => $student->othername,
                    'gender' => $student->gender,
                    'dateofbirth' => $student->dateofbirth,
                    'placeofbirth' => $student->placeofbirth,
                    'nationality' => $student->nationality,
                    'religion' => $student->religion,
                    'last_school' => $student->last_school,
                    'last_class' => $student->last_class,
                    'schoolclassid' => $student->schoolclassid,
                    'termid' => $student->termid,
                    'sessionid' => $student->sessionid,
                    'phone_number' => $student->phone_number,
                    'nin_number' => $student->nin_number,
                    'blood_group' => $student->blood_group,
                    'mother_tongue' => $student->mother_tongue,
                    'father_name' => $parent->father ?? '',
                    'father_phone' => $parent->father_phone ?? '',
                    'father_occupation' => $parent->father_occupation ?? '',
                    'mother_name' => $parent->mother ?? '',
                    'mother_phone' => $parent->mother_phone ?? '',
                    'parent_address' => $parent->parent_address ?? '',
                    'student_category' => $student->student_category,
                    'reason_for_leaving' => $student->reason_for_leaving,
                    'picture' => $picture->picture ?? 'unnamed.jpg',
                    'state' => $student->state,
                    'local' => $student->local,
                    'statusId' => $student->statusId,
                    'student_status' => $student->student_status,
                    'future_ambition' => $student->future_ambition, // Changed from present_address
                    'permanent_address' => $student->home_address2,
                    'schoolclass' => $studentClass->schoolclass->name ?? '',
                    'arm' => $studentClass->schoolclass->arm ?? ''
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating student ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage(),
            ], 500);
        }
    }
     
    
    protected function deleteImage($filename)
    {
        try {
            if ($filename && $filename !== 'unnamed.jpg' && Storage::exists('public/images/student_avatars/' . $filename)) {
                Storage::delete('public/images/student_avatars/' . $filename);
                Log::debug('Image deleted', ['filename' => $filename]);
            }
        } catch (\Exception $e) {
            Log::error("Error deleting image: {$e->getMessage()}");
            throw $e;
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
                $this->deleteImage($picture->picture);
            }

            Studentclass::where('studentId', $id)->delete();
            PromotionStatus::where('studentId', $id)->delete();
            ParentRegistration::where('studentId', $id)->delete();
            Studentpicture::where('studentid', $id)->delete();
            $broadsheetRecords = BroadsheetRecord::where('student_id', $id)->get();
            foreach ($broadsheetRecords as $record) {
                Broadsheets::where('broadsheet_record_id', $record->id)->delete();
                $record->delete();
            }
            SubjectRegistrationStatus::where('studentId', $id)->delete();
            Studenthouse::where('studentid', $id)->delete();
            Studentpersonalityprofile::where('studentid', $id)->delete();
            $student->delete();

            DB::commit();

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
                    $this->deleteImage($picture->picture);
                }

                Studentclass::where('studentId', $id)->delete();
                PromotionStatus::where('studentId', $id)->delete();
                ParentRegistration::where('studentId', $id)->delete();
                Studentpicture::where('studentid', $id)->delete();
                Broadsheet::where('studentId', $id)->delete();
                SubjectRegistrationStatus::where('studentId', $id)->delete();
                Studenthouses::where('studentid', $id)->delete();
                Studentpersonalityprofiles::where('studentid', $id)->delete();
            }

            Student::whereIn('id', $ids)->delete();

            DB::commit();

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
        $id = $request->input('id');
        return $this->destroy($id);
    }

    public function deletestudentbatch(Request $request): JsonResponse
    {
        $batchId = $request->input('studentbatchid');
        Log::debug("Attempting to delete batch ID {$batchId}");

        try {
            if (!Schema::hasTable('student_batch_upload')) {
                throw new \Exception('student_batch_upload table does not exist');
            }
            if (!Schema::hasColumn('studentRegistration', 'batchid')) {
                throw new \Exception('batchid column missing in studentRegistration table');
            }

            $batch = StudentBatchModel::findOrFail($batchId);
            DB::beginTransaction();

            $studentIds = Student::where('batchid', $batch->id)->pluck('id');
            foreach ($studentIds as $studentId) {
                $picture = Studentpicture::where('studentid', $studentId)->first();
                if ($picture && $picture->picture) {
                    $this->deleteImage($picture->picture);
                }

                $broadsheetRecords = BroadsheetRecord::where('student_id', $studentId)->get();
                foreach ($broadsheetRecords as $record) {
                    Broadsheets::where('broadsheet_record_id', $record->id)->delete();
                    $record->delete();
                }

                $broadsheetMockRecords = BroadsheetRecordMock::where('student_id', $studentId)->get();
                foreach ($broadsheetMockRecords as $record) {
                    BroadsheetsMock::where('broadsheet_records_mock_id', $record->id)->delete();
                    $record->delete();
                }

                Studentclass::where('studentId', $studentId)->delete();
                PromotionStatus::where('studentId', $studentId)->delete();
                ParentRegistration::where('studentId', $studentId)->delete();
                Studentpicture::where('studentid', $studentId)->delete();
                SubjectRegistrationStatus::where('studentId', $studentId)->delete();
                Studenthouses::where('studentid', $studentId)->delete();
                Studentpersonalityprofiles::where('studentid', $studentId)->delete();
            }

            Student::where('batchid', $batch->id)->delete();
            $batch->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Batch Upload has been removed'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("Batch ID {$batchId} not found: {$e->getMessage()}");
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
        $pagetitle = "Bulk Upload Students";
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display, schoolclass.schoolclass, schoolarm.arm")
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms = Schoolterm::select('id', 'term as name')->get();
        $schoolsessions = Schoolsession::select('id', 'session as name')->get();

        return view('student.bulkupload', compact('schoolclasses', 'schoolterms', 'schoolsessions', 'pagetitle'));
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

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display, schoolclass.schoolclass, schoolarm.arm")
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms = Schoolterm::select('id', 'term as name')->get();
        $schoolsessions = Schoolsession::select('id', 'session as name')->get();

        return view('student.batchindex', compact('batch', 'schoolclasses', 'schoolterms', 'schoolsessions', 'pagetitle'));
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
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $batchchk = StudentBatchModel::where('title', $request->title)->exists();
        if ($batchchk) {
            return redirect()->back()->with('success', 'Title is already chosen, Please choose another Title for this Batch Upload');
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
            StudentBatchModel::where('id', $batch->id)->update(['status' => 'Success']);

            DB::commit();

            return redirect()->back()->with('success', 'Student Batch File Imported Successfully');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            StudentBatchModel::where('id', $batch->id)->update(['status' => 'Failed']);
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return redirect()->back()->with('status', implode('; ', $errors));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error importing batch: {$e->getMessage()}");
            return redirect()->back()->with('status', 'Failed to import batch: ' . $e->getMessage());
        }
    }

    public function updateClass(Request $request)
    {
        Log::debug('Updating class for batch', $request->all());

        try {
            $validator = Validator::make($request->all(), [
                'batch_id' => 'required|exists:student_batch_upload,id',
                'schoolclass' => 'required|string|max:255',
                'armid' => 'required|exists:schoolarm,id',
                'classcategoryid' => 'required|exists:classcategories,id',
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

            $schoolClass = Schoolclass::updateOrCreate(
                ['id' => $request->schoolclassid],
                [
                    'schoolclass' => $request->schoolclass,
                    'arm' => $request->armid,
                    'classcategoryid' => $request->classcategoryid,
                    'description' => $request->description ?? 'Updated class',
                ]
            );

            $batch = StudentBatchModel::findOrFail($request->batch_id);
            $batch->update(['schoolclassid' => $schoolClass->id]);

            $studentIds = Student::where('batchid', $batch->id)->pluck('id');

            BroadsheetRecord::whereIn('student_id', $studentIds)
                ->update(['schoolclass_id' => $schoolClass->id]);

            BroadsheetRecordMock::whereIn('student_id', $studentIds)
                ->update(['schoolclass_id' => $schoolClass->id]);

            $broadsheetRecordIds = BroadsheetRecord::whereIn('student_id', $studentIds)->pluck('id');
            $subjectClassIdsFromBroadsheets = Broadsheets::whereIn('broadsheet_record_id', $broadsheetRecordIds)
                ->whereNotNull('subjectclass_id')
                ->pluck('subjectclass_id')
                ->unique();
            if ($subjectClassIdsFromBroadsheets->isNotEmpty()) {
                Subjectclass::whereIn('id', $subjectClassIdsFromBroadsheets)
                    ->update(['schoolclassid' => $schoolClass->id]);
            }

            $broadsheetMockRecordIds = BroadsheetRecordMock::whereIn('student_id', $studentIds)->pluck('id');
            $subjectClassIdsFromBroadsheetsMock = BroadsheetsMock::whereIn('broadsheet_records_mock_id', $broadsheetMockRecordIds)
                ->whereNotNull('subjectclass_id')
                ->pluck('subjectclass_id')
                ->unique();
            $commonSubjectClassIds = $subjectClassIdsFromBroadsheets->intersect($subjectClassIdsFromBroadsheetsMock);
            if ($commonSubjectClassIds->isNotEmpty()) {
                Subjectclass::whereIn('id', $commonSubjectClassIds)
                    ->update(['schoolclassid' => $schoolClass->id]);
            }

            DB::commit();

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

   
    public function getLastAdmissionNumber(Request $request)
    {
        try {
            $year = $request->query('year', date('Y'));
            if (!preg_match('/^\d{4}$/', $year)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid year format'
                ], 400);
            }

            $lastStudent = Student::where('admissionNo', 'LIKE', "CSSK/STD/{$year}/%")
                ->orderBy('id', 'desc')
                ->first();

            $lastNumber = 870; // Start from 870 so next number is 871
            if ($lastStudent && $lastStudent->admissionNo) {
                $parts = explode('/', $lastStudent->admissionNo);
                if (count($parts) === 4 && $parts[0] === 'CSSK' && $parts[1] === 'STD' && $parts[2] === $year && is_numeric($parts[3])) {
                    $lastNumber = max(870, (int)$parts[3]);
                } else {
                    Log::warning("Invalid admission number format: {$lastStudent->admissionNo}");
                }
            }

            $nextNumber = $lastNumber + 1;
            $admissionNo = sprintf('CSSK/STD/%s/%04d', $year, $nextNumber);

            return response()->json([
                'success' => true,
                'admissionNo' => $admissionNo
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error generating admission number: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate admission number'
            ], 500);
        }
    }
}