<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Broadsheet;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetRecordMock;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\ParentRegistration;
use App\Models\PromotionStatus;
use App\Models\ReportHistory;
use App\Models\Schoolclass;
use App\Models\Schoolhouse;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\StudentBatchModel;
use App\Models\StudentBillInvoice;
use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentBook;
use App\Models\StudentBillPaymentRecord;
use App\Models\Studentclass;
use App\Models\StudentCurrentTerm;
use App\Models\Studenthouse;
use App\Models\Studenthouses;
use App\Models\Studentpersonalityprofile;
use App\Models\Studentpersonalityprofiles;
use App\Models\Studentpicture;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use App\Traits\ImageManager as TraitsImageManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
        $schoolhouses = Schoolhouse::all();

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

        $currentTerm = Schoolterm::where('status', 'Current')->first();
         // IMPORTANT: Get sessions with the actual column name 'session'
        // $schoolsessions = Schoolsession::select('id', 'session')->get(); // NOT 'session as name'

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
            'staff_count',
            'currentTerm',
        ));
    }



    /**
 * Get students optimized with server-side pagination and filtering
 * FIXED: Using subquery approach to avoid ONLY_FULL_GROUP_BY issues
 */
public function getStudentsOptimized(Request $request)
{
    try {
        $perPage = $request->get('per_page', 12);
        $search = $request->get('search', '');
        $classId = $request->get('class_id', 'all');
        $status = $request->get('status', 'all');
        $gender = $request->get('gender', 'all');
        $sessionId = $request->get('session_id', 'all');

        // Build the base query for student IDs with filters
        $idQuery = Student::query()
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->select('studentRegistration.id');

        // Apply search filter
        if (!empty($search)) {
            $idQuery->where(function($q) use ($search) {
                $q->where('studentRegistration.firstname', 'LIKE', "%{$search}%")
                  ->orWhere('studentRegistration.lastname', 'LIKE', "%{$search}%")
                  ->orWhere('studentRegistration.admissionNo', 'LIKE', "%{$search}%")
                  ->orWhere('studentRegistration.othername', 'LIKE', "%{$search}%");
            });
        }

        // Apply class filter
        if ($classId !== 'all' && !empty($classId)) {
            $idQuery->where('studentclass.schoolclassid', $classId);
        }

        // Apply status filter
        if ($status !== 'all' && !empty($status)) {
            if ($status === '1' || $status === '2') {
                $idQuery->where('studentRegistration.statusId', $status);
            } elseif ($status === 'Active' || $status === 'Inactive') {
                $idQuery->where('studentRegistration.student_status', $status);
            }
        }

        // Apply gender filter
        if ($gender !== 'all' && !empty($gender)) {
            $idQuery->where('studentRegistration.gender', $gender);
        }

        // Apply session filter
        if ($sessionId !== 'all' && !empty($sessionId)) {
            $idQuery->where('studentclass.sessionid', $sessionId);
        }

        // Group by to avoid duplicates
        $idQuery->groupBy('studentRegistration.id');

        // Get paginated IDs first
        $paginatedIds = $idQuery->paginate($perPage, ['studentRegistration.id'], 'page', $request->get('page', 1));

        $studentIds = $paginatedIds->pluck('id')->toArray();

        // If no students found, return empty pagination
        if (empty($studentIds)) {
            return response()->json([
                'success' => true,
                'data' => new \Illuminate\Pagination\LengthAwarePaginator(
                    [],
                    0,
                    $perPage,
                    $request->get('page', 1),
                    ['path' => $request->url(), 'query' => $request->query()]
                )
            ]);
        }

        // Now fetch full student data for these IDs
        $students = Student::query()
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('parentRegistration', 'parentRegistration.studentId', '=', 'studentRegistration.id')
            ->leftJoin('studenthouses', 'studenthouses.studentid', '=', 'studentRegistration.id')
            ->leftJoin('schoolhouses', 'schoolhouses.id', '=', 'studenthouses.schoolhouse')
            ->whereIn('studentRegistration.id', $studentIds)
            ->select([
                'studentRegistration.*',
                'studentpicture.picture',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'studentclass.schoolclassid',
                'studentclass.termid',
                'studentclass.sessionid',
                'parentRegistration.father',
                'parentRegistration.mother',
                'parentRegistration.father_phone',
                'parentRegistration.mother_phone',
                'parentRegistration.father_occupation',
                'parentRegistration.father_city',
                'parentRegistration.office_address',
                'parentRegistration.parent_email',
                'parentRegistration.parent_address',
                'parentRegistration.father_title',
                'parentRegistration.mother_title',
                'schoolhouses.house as school_house',
            ])
            ->orderBy('studentRegistration.created_at', 'desc')
            ->get();

        // Group by student ID to handle any remaining duplicates
        $groupedStudents = $students->groupBy('id')->map(function($group) {
            return $group->first();
        })->values();

        // Create pagination manually
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedStudents,
            $paginatedIds->total(),
            $paginatedIds->perPage(),
            $paginatedIds->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Process each student to add calculated fields
        $paginatedData->getCollection()->transform(function($student) {
            // Calculate age if dateofbirth exists
            $age = null;
            if ($student->dateofbirth) {
                $dob = new \Carbon\Carbon($student->dateofbirth);
                $age = $dob->age;
            }

            return [
                'id' => $student->id,
                'admissionNo' => $student->admissionNo,
                'admission_date' => $student->admission_date,
                'admissionYear' => $student->admissionYear,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'othername' => $student->othername,
                'fullname' => trim($student->lastname . ' ' . $student->firstname . ' ' . $student->othername),
                'gender' => $student->gender,
                'statusId' => $student->statusId,
                'student_status' => $student->student_status,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
                'picture' => $student->picture,
                'schoolclass' => $student->schoolclass,
                'arm' => $student->arm,
                'schoolclassid' => $student->schoolclassid,
                'age' => $age,
                'dateofbirth' => $student->dateofbirth,
                'title' => $student->title,
                'placeofbirth' => $student->placeofbirth,
                'phone_number' => $student->phone_number,
                'email' => $student->email,
                'permanent_address' => $student->home_address2,
                'future_ambition' => $student->future_ambition,
                'nationality' => $student->nationality,
                'state' => $student->state,
                'local' => $student->local,
                'city' => $student->city,
                'religion' => $student->religion,
                'blood_group' => $student->blood_group,
                'mother_tongue' => $student->mother_tongue,
                'nin_number' => $student->nin_number,
                'student_category' => $student->student_category,
                'termid' => $student->termid,
                'sessionid' => $student->sessionid,
                'last_school' => $student->last_school,
                'last_class' => $student->last_class,
                'reason_for_leaving' => $student->reason_for_leaving,
                // Parent fields
                'father_name' => $student->father,
                'father_title' => $student->father_title,
                'father_phone' => $student->father_phone,
                'father_occupation' => $student->father_occupation,
                'father_city' => $student->father_city,
                'mother_name' => $student->mother,
                'mother_title' => $student->mother_title,
                'mother_phone' => $student->mother_phone,
                'parent_email' => $student->parent_email,
                'parent_address' => $student->parent_address,
                'office_address' => $student->office_address,
                'school_house' => $student->school_house,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $paginatedData
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching optimized students: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch students: ' . $e->getMessage()
        ], 500);
    }
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
                        'regex:/^CSSK\/STD\/\d{4}\/\d{4}$/'
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
                'schoolhouseid' => 'required|exists:schoolhouses,id',
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
            $studenthouses->schoolhouse = $request->studenthouseid;
            $studenthouses->termid = $request->termid;
            $studenthouses->sessionid = $request->sessionid;
            $studenthouses->save();

            $studentpersonalityprofiles = new Studentpersonalityprofile();
            $studentpersonalityprofiles->studentid = $studentId;
            $studentpersonalityprofiles->schoolclassid = $request->schoolclassid;
            $studentpersonalityprofiles->termid = $request->termid;
            $studentpersonalityprofiles->sessionid = $request->sessionid;
            $studentpersonalityprofiles->save();

            // NEW: Create StudentCurrentTerm record
            $currentTerm = new StudentCurrentTerm();
            $currentTerm->studentId = $studentId;
            $currentTerm->schoolclassId = $request->schoolclassid;
            $currentTerm->termId = $request->termid;
            $currentTerm->sessionId = $request->sessionid;
            $currentTerm->is_current = true;
            $currentTerm->save();

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
        return sprintf('TCC/2025/%04d', $number);
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
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->leftJoin('studenthouses', 'studenthouses.studentId', '=', 'studentRegistration.id')
                ->leftJoin('schoolhouses', 'schoolhouses.id', '=', 'studenthouses.schoolhouse')
                ->leftJoin('studentpersonalityprofiles', 'studentpersonalityprofiles.studentId', '=', 'studentRegistration.id')
                ->select([
                    // Student Registration fields
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
                    'studentRegistration.future_ambition',
                    'studentRegistration.home_address2 as permanent_address',
                    'studentRegistration.student_category',
                    'studentRegistration.statusId',
                    'studentRegistration.student_status',
                    'studentRegistration.last_school',
                    'studentRegistration.last_class',
                    'studentRegistration.reason_for_leaving',

                    // Student Class fields
                    'studentclass.schoolclassid',
                    'studentclass.termid',
                    'studentclass.sessionid',

                    // School Class fields
                    'schoolclass.schoolclass',
                    'schoolarm.arm',

                    // Term and Session names
                    'schoolterm.term as term_name',
                    'schoolsession.session as session_name',

                    // Parent fields
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

                    // Picture and House
                    'studentpicture.picture',
                    'studenthouses.schoolhouse',
                    'schoolhouses.house as school_house',
                ])
                ->first();

            if (!$studentData) {
                Log::warning("Student ID {$student} not found");
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

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

            // Update StudentCurrentTerm
            // $currentTerm = StudentCurrentTerm::where('studentId', $id)->first();
            // if (!$currentTerm) {
            //     $currentTerm = new StudentCurrentTerm();
            //     $currentTerm->studentId = $id;
            // }
            // $currentTerm->schoolclassId = $request->schoolclassid;
            // $currentTerm->termId = $request->termid;
            // $currentTerm->sessionId = $request->sessionid;
            // $currentTerm->is_current = true;
            // $currentTerm->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'redirect' => route('student.index'),
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
                    'future_ambition' => $student->future_ambition,
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

            // Delete student picture and image file
            $picture = Studentpicture::where('studentid', $id)->first();
            if ($picture && $picture->picture) {
                $this->deleteImage($picture->picture);
            }

            // Delete student bill payment records and related data
            $billPayments = StudentBillPayment::where('student_id', $id)->get();
            foreach ($billPayments as $billPayment) {
                StudentBillPaymentRecord::where('student_bill_payment_id', $billPayment->id)->delete();
                $billPayment->delete();
            }

            // Delete student bill payment books
            StudentBillPaymentBook::where('student_id', $id)->delete();

            // Delete student bill invoices
            StudentBillInvoice::where('student_id', $id)->delete();

            // Delete other student related records
            Studentclass::where('studentId', $id)->delete();
            PromotionStatus::where('studentId', $id)->delete();
            ParentRegistration::where('studentId', $id)->delete();
            Studentpicture::where('studentid', $id)->delete();

            // Delete broadsheet records
            $broadsheetRecords = BroadsheetRecord::where('student_id', $id)->get();
            foreach ($broadsheetRecords as $record) {
                Broadsheets::where('broadsheet_record_id', $record->id)->delete();
                $record->delete();
            }

            SubjectRegistrationStatus::where('studentId', $id)->delete();
            Studenthouse::where('studentid', $id)->delete();
            Studentpersonalityprofile::where('studentid', $id)->delete();

            // Delete StudentCurrentTerm records
            StudentCurrentTerm::where('studentId', $id)->delete();

            // Finally delete the student
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
                // Delete student picture and image file
                $picture = Studentpicture::where('studentid', $id)->first();
                if ($picture && $picture->picture) {
                    $this->deleteImage($picture->picture);
                }

                // Delete student bill payment records and related data
                $billPayments = StudentBillPayment::where('student_id', $id)->get();
                foreach ($billPayments as $billPayment) {
                    StudentBillPaymentRecord::where('student_bill_payment_id', $billPayment->id)->delete();
                    $billPayment->delete();
                }

                // Delete student bill payment books
                StudentBillPaymentBook::where('student_id', $id)->delete();

                // Delete student bill invoices
                StudentBillInvoice::where('student_id', $id)->delete();

                // Delete other student related records
                Studentclass::where('studentId', $id)->delete();
                PromotionStatus::where('studentId', $id)->delete();
                ParentRegistration::where('studentId', $id)->delete();
                Studentpicture::where('studentid', $id)->delete();
                Broadsheet::where('studentId', $id)->delete();
                SubjectRegistrationStatus::where('studentId', $id)->delete();
                Studenthouses::where('studentid', $id)->delete();
                Studentpersonalityprofiles::where('studentid', $id)->delete();

                // Delete StudentCurrentTerm records
                StudentCurrentTerm::where('studentId', $id)->delete();
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

                // Delete student bill payment records and related data
                $billPayments = StudentBillPayment::where('student_id', $studentId)->get();
                foreach ($billPayments as $billPayment) {
                    StudentBillPaymentRecord::where('student_bill_payment_id', $billPayment->id)->delete();
                    $billPayment->delete();
                }

                // Delete student bill payment books
                StudentBillPaymentBook::where('student_id', $studentId)->delete();

                // Delete student bill invoices
                StudentBillInvoice::where('student_id', $studentId)->delete();

                // Delete broadsheet records
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
                Studenthouse::where('studentid', $studentId)->delete();
                Studentpersonalityprofile::where('studentid', $studentId)->delete();

                // Delete StudentCurrentTerm records
                StudentCurrentTerm::where('studentId', $studentId)->delete();
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

            $lastStudent = Student::where('admissionNo', 'LIKE', "TCC/{$year}/%")
                ->orderBy('id', 'desc')
                ->first();

            $lastNumber = 870;
            if ($lastStudent && $lastStudent->admissionNo) {
                $parts = explode('/', $lastStudent->admissionNo);
                if (count($parts) === 4 && $parts[0] === 'TCC' && $parts[2] === $year && is_numeric($parts[3])) {
                    $lastNumber = max(870, (int)$parts[3]);
                } else {
                    Log::warning("Invalid admission number format: {$lastStudent->admissionNo}");
                }
            }

            $nextNumber = $lastNumber + 1;
            $admissionNo = sprintf('TCC/%s/%04d', $year, $nextNumber);

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



/**
 * Generate student report - Complete version with all improvements
 */
public function generateReport(Request $request)
{
    ini_set('memory_limit', '512M');
    set_time_limit(300);

    Log::info('=== GENERATE REPORT STARTED ===');
    Log::info('Request parameters:', $request->all());

    // Declare variables at the beginning of the function
    $reportId = null;
    $currentTerms = null;
    $reportStudents = null;

    try {
        $request->validate([
            'class_id'    => 'nullable|exists:schoolclass,id',
            'term_id'     => 'nullable|exists:schoolterm,id',
            'session_id'  => 'nullable|exists:schoolsession,id',
            'status'      => 'nullable|in:1,2,Active,Inactive',
            'columns'     => 'required|string',
            'columns_order' => 'nullable|string',
            'format'      => 'required|in:pdf,excel',
            'orientation' => 'nullable|in:portrait,landscape',
            'include_header' => 'nullable|boolean',
            'include_logo' => 'nullable|boolean',
            'exclude_photos' => 'nullable|boolean',
            'template'    => 'nullable|in:default,detailed,simple',
            'confidential' => 'nullable|boolean',
            'preview'     => 'nullable|boolean',
            'optimize_large_reports' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        if (!$user) {
            Log::warning('Unauthorized access attempt to generate report');
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login to generate reports.'
            ], 401);
        }

        if (!$user->hasAnyRole(['Staff', 'Admin', 'Super Admin'])) {
            Log::warning('Non-staff user attempted to generate report', ['user_id' => $user->id, 'user_name' => $user->name]);
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only authorized staff members can generate reports.'
            ], 403);
        }

        Log::info('User generating report:', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames()
        ]);

        $columns = array_filter(explode(',', $request->columns));
        Log::info('Columns selected:', $columns);

        $columnOrder = [];
        if ($request->filled('columns_order')) {
            $columnOrder = array_filter(explode(',', $request->columns_order));
            Log::info('Column order:', $columnOrder);
            $columns = array_values(array_intersect($columnOrder, $columns));
        }

        // Apply template-based column adjustments
        $template = $request->input('template', 'default');
        if ($template === 'detailed') {
            $defaultColumns = ['photo', 'admissionNo', 'firstname', 'lastname', 'othername', 'gender', 'dateofbirth', 'age', 'class', 'status'];
            $columns = array_unique(array_merge($columns, $defaultColumns));
        } elseif ($template === 'simple') {
            $simpleColumns = ['photo', 'admissionNo', 'firstname', 'lastname', 'class', 'status'];
            $columns = array_values(array_intersect($columns, $simpleColumns));
        }

        // Handle photo exclusion
        if ($request->boolean('exclude_photos')) {
            $columns = array_filter($columns, function($col) {
                return $col !== 'photo';
            });
        }

        if (empty($columns)) {
            Log::warning('No columns selected');
            return response()->json(['success' => false, 'message' => 'No columns selected'], 422);
        }

        $termName = 'All Terms';
        $sessionName = 'All Sessions';
        $selectedTerm = null;
        $selectedSession = null;

        // Get term and session names if selected
        if ($request->filled('term_id')) {
            $selectedTerm = Schoolterm::find($request->term_id);
            $termName = $selectedTerm ? $selectedTerm->term : 'Unknown Term';
        }

        if ($request->filled('session_id')) {
            $selectedSession = Schoolsession::find($request->session_id);
            $sessionName = $selectedSession ? $selectedSession->session : 'Unknown Session';
        }

        // Query using StudentCurrentTerm
        $query = StudentCurrentTerm::query()
            ->with([
                'student.picture',
                'student.parent',
                'schoolClass.armRelation',
                'term',
                'session'
            ])
            ->select('student_current_term.*');

        if ($request->filled('class_id')) {
            $query->where('schoolclassId', $request->class_id);
        }

        if ($request->filled('term_id')) {
            $query->where('termId', $request->term_id);
        }

        if ($request->filled('session_id')) {
            $query->where('sessionId', $request->session_id);
        }

        if ($request->filled('status')) {
            $query->whereHas('student', function($q) use ($request) {
                if (in_array($request->status, ['1', '2'])) {
                    $q->where('statusId', $request->status);
                } else {
                    $q->where('student_status', $request->status);
                }
            });
        }

        $currentTerms = $query->get();
        Log::info('Current term records found:', ['count' => $currentTerms->count()]);

        if ($currentTerms->isEmpty()) {
            Log::warning('No students found with selected term/session filters');
            return response()->json([
                'success' => false,
                'message' => 'No students found in the selected term and session.'
            ], 404);
        }

        // Check if report is large and optimize if needed
        $isLargeReport = $currentTerms->count() > 100;
        $optimizeLarge = $request->boolean('optimize_large_reports', true);

        if ($isLargeReport && $optimizeLarge && !$request->boolean('exclude_photos')) {
            Log::info('Large report detected, optimizing photo processing');
            $columns = array_filter($columns, function($col) {
                return $col !== 'photo';
            });
        }

        // Start progress tracking
        $reportId = uniqid('report_');
        Cache::put($reportId, [
            'status' => 'processing',
            'progress' => 0,
            'total' => $currentTerms->count(),
            'message' => 'Processing students...'
        ], now()->addMinutes(10));

        $reportStudents = $currentTerms->map(function($currentTerm, $index) use ($reportId, $isLargeReport) {
            $student = $currentTerm->student;
            $picture = $student->picture;
            $parent = $student->parent;

            // Process photo for PDF
            $photoBase64 = null;
            $hasPhoto = false;

            if ($picture && $picture->picture && $picture->picture !== 'unnamed.jpg') {
                $hasPhoto = true;
                if (!$isLargeReport) {
                    $photoBase64 = $this->getOptimizedImageForPDF($picture->picture);
                }
            }

            $currentClass = null;
            $currentArm = null;
            if ($currentTerm->schoolClass) {
                $currentClass = $currentTerm->schoolClass->schoolclass;
                if ($currentTerm->schoolClass->armRelation) {
                    $currentArm = $currentTerm->schoolClass->armRelation->arm;
                }
            }

            $currentTermName = null;
            if ($currentTerm->term) {
                $currentTermName = $currentTerm->term->term;
            }

            $currentSessionName = null;
            if ($currentTerm->session) {
                $currentSessionName = $currentTerm->session->session;
            }

            $studentData = [
                'id' => $student->id,
                'admissionNo' => $student->admissionNo,
                'admissionYear' => $student->admissionYear,
                'admission_date' => $student->admission_date,
                'title' => $student->title,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'othername' => $student->othername,
                'gender' => $student->gender,
                'dateofbirth' => $student->dateofbirth,
                'age' => $student->age,
                'blood_group' => $student->blood_group,
                'mother_tongue' => $student->mother_tongue,
                'religion' => $student->religion,
                'schoolhouseid' => $student->sport_house,
                'phone_number' => $student->phone_number,
                'email' => $student->email,
                'nin_number' => $student->nin_number,
                'city' => $student->city,
                'state' => $student->state,
                'local' => $student->local,
                'nationality' => $student->nationality,
                'placeofbirth' => $student->placeofbirth,
                'future_ambition' => $student->future_ambition,
                'permanent_address' => $student->home_address2,
                'student_category' => $student->student_category,
                'statusId' => $student->statusId,
                'student_status' => $student->student_status,
                'last_school' => $student->last_school,
                'last_class' => $student->last_class,
                'reason_for_leaving' => $student->reason_for_leaving,
                'created_at' => $student->created_at,

                // Current term info from StudentCurrentTerm
                'current_term_id' => $currentTerm->termId,
                'current_session_id' => $currentTerm->sessionId,
                'current_class_id' => $currentTerm->schoolclassId,
                'is_current' => $currentTerm->is_current,
                'current_class_name' => $currentClass,
                'current_arm' => $currentArm,
                'current_term_name' => $currentTermName,
                'current_session_name' => $currentSessionName,

                // Legacy fields for compatibility
                'schoolclass' => $currentClass,
                'arm_name' => $currentArm,
                'termid' => $currentTerm->termId,
                'sessionid' => $currentTerm->sessionId,

                // Photo information
                'picture' => $picture ? $picture->picture : null,
                'picture_base64' => $photoBase64,
                'has_photo' => $hasPhoto,
                'photo_initials' => substr($student->firstname ?? '', 0, 1) . substr($student->lastname ?? '', 0, 1),

                'father_name' => $parent ? $parent->father : null,
                'mother_name' => $parent ? $parent->mother : null,
                'father_phone' => $parent ? $parent->father_phone : null,
                'mother_phone' => $parent ? $parent->mother_phone : null,
                'parent_email' => $parent ? $parent->parent_email : null,
                'parent_address' => $parent ? $parent->parent_address : null,
                'father_occupation' => $parent ? $parent->father_occupation : null,
                'father_city' => $parent ? $parent->father_city : null,
            ];

            // Update progress every 10 records
            // if ($index % 10 === 0) {
            //     Cache::put($reportId, [
            //         'status' => 'processing',
            //         'progress' => $index + 1,
            //         'total' => $currentTerms->count(),
            //         'message' => 'Processing student ' . ($index + 1) . ' of ' . $currentTerms->count()
            //     ], now()->addMinutes(10));
            // }

            return (object) $studentData;
        });

        // Mark progress as complete
        Cache::put($reportId, [
            'status' => 'complete',
            'progress' => $currentTerms->count(),
            'total' => $currentTerms->count(),
            'message' => 'Report generation complete'
        ], now()->addMinutes(10));

        $className = 'All Classes';
        if ($request->filled('class_id')) {
            $class = Schoolclass::with('armRelation')
                ->where('schoolclass.id', $request->class_id)
                ->first();

            if ($class) {
                $className = $class->schoolclass . ($class->armRelation ? ' - ' . $class->armRelation->arm : '');
            }
        }

        $format = $request->input('format');
        $orientation = $request->query('orientation', 'portrait');
        $includeHeader = $request->boolean('include_header', true);
        $includeLogo = $request->boolean('include_logo', true);
        $confidential = $request->boolean('confidential', false);

        Log::info('Report parameters:', [
            'format' => $format,
            'orientation' => $orientation,
            'className' => $className,
            'term' => $termName,
            'session' => $sessionName,
            'total_students' => $reportStudents->count(),
            'include_header' => $includeHeader,
            'include_logo' => $includeLogo,
            'template' => $template,
            'confidential' => $confidential,
            'generated_by' => $user->name
        ]);

        $schoolInfo = SchoolInformation::where('is_active', true)->first();

        $data = [
            'students'          => $reportStudents,
            'columns'           => $columns,
            'title'             => $confidential ? 'CONFIDENTIAL - Student Master List Report' : 'Student Master List Report',
            'className'         => $className,
            'termName'          => $termName,
            'sessionName'       => $sessionName,
            'generated'         => now()->format('d M Y h:i A'),
            'generated_by'      => $user->name,
            'total'             => $reportStudents->count(),
            'males'             => $reportStudents->where('gender', 'Male')->count(),
            'females'           => $reportStudents->where('gender', 'Female')->count(),
            'orientation'       => $orientation,
            'include_header'    => $includeHeader,
            'include_logo'      => $includeLogo,
            'school_info'       => $schoolInfo,
            'school_logo_base64' => null,
            'selected_term'     => $selectedTerm,
            'selected_session'  => $selectedSession,
            'template'          => $template,
            'confidential'      => $confidential,
            'report_id'         => $reportId,
            'is_large_report'   => $isLargeReport,
            'warning'           => $isLargeReport ? 'Large report detected. Photos may be excluded for performance.' : null,
        ];

        if ($includeLogo && $schoolInfo && $format === 'pdf') {
            $schoolLogoBase64 = $this->getSchoolLogoBase64($schoolInfo);
            if ($schoolLogoBase64) {
                $data['school_logo_base64'] = $schoolLogoBase64;
            }
        }

        // Log report generation for audit trail - Check if ReportHistory model exists
        if (class_exists('App\Models\ReportHistory')) {
            try {
                ReportHistory::create([
                    'user_id' => $user->id,
                    'report_type' => 'student_list',
                    'parameters' => json_encode($request->all()),
                    'student_count' => $reportStudents->count(),
                    'format' => $format,
                    'template' => $template,
                    'generated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create report history: ' . $e->getMessage());
                // Continue even if report history fails
            }
        }

        $filename = 'student-report-' . now()->format('Y-m-d-His') . ($confidential ? '-CONFIDENTIAL' : '');
        Log::info('Generating report with filename:', ['filename' => $filename]);

        // Handle preview request
        if ($request->boolean('preview')) {
            Log::info('Generating preview');
            $previewStudents = $reportStudents->take(5);
            $data['students'] = $previewStudents;
            $data['is_preview'] = true;
            $data['warning'] = 'PREVIEW - Showing first 5 records only';

            $pdf = Pdf::loadView('student.reports.student_report_pdf', $data)
                ->setPaper('A4', $orientation)
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'chroot' => [public_path(), storage_path()],
                ]);

            return $pdf->stream('preview-report.pdf');
        }

        if ($format === 'excel') {
            Log::info('Generating Excel export');
            // Check if the export class exists
            if (!class_exists('App\Exports\StudentReportExport')) {
                throw new \Exception('StudentReportExport class not found. Please create it first.');
            }
            return Excel::download(new \App\Exports\StudentReportExport($data), $filename . '.xlsx');
        }

        Log::info('Generating PDF export');

        // Check if view exists
        $view = 'student.reports.student_report_pdf';
        if ($template === 'detailed' && view()->exists('student.reports.detailed_report_pdf')) {
            $view = 'student.reports.detailed_report_pdf';
        } elseif ($template === 'simple' && view()->exists('student.reports.simple_report_pdf')) {
            $view = 'student.reports.simple_report_pdf';
        }

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('A4', $orientation)
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => [public_path(), storage_path()],
            ]);

        Log::info('=== GENERATE REPORT COMPLETED SUCCESSFULLY ===');
        return $pdf->download($filename . '.pdf');

    } catch (\Exception $e) {
        Log::error('Error generating report:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        // Mark report as failed - check if $reportId exists
        if (isset($reportId) && $reportId !== null) {
            try {
                Cache::put($reportId, [
                    'status' => 'failed',
                    'progress' => 0,
                    'total' => $currentTerms ? $currentTerms->count() : 0,
                    'message' => 'Report generation failed: ' . $e->getMessage()
                ], now()->addMinutes(10));
            } catch (\Exception $cacheError) {
                Log::error('Failed to update cache: ' . $cacheError->getMessage());
            }
        }

        // Check if request expects JSON response
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : 'Internal server error'
            ], 500);
        }

        // Redirect back with error for non-AJAX requests
        return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage());
    }
}

    /**
     * Get report generation progress
     */
    public function getReportProgress(Request $request)
    {
        $request->validate([
            'report_id' => 'required|string'
        ]);

        $progress = Cache::get($request->report_id, [
            'status' => 'unknown',
            'progress' => 0,
            'total' => 0,
            'message' => 'Report not found'
        ]);

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Get optimized image as base64 for PDF
     */
    private function getOptimizedImageForPDF($imagePath, $maxWidth = 100)
    {
        if (!$imagePath) {
            return null;
        }

        // Generate cache key
        $cacheKey = 'optimized_image_' . md5($imagePath . '_' . $maxWidth);

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $possiblePaths = [
            storage_path('app/public/images/student_avatars/' . $imagePath),
            public_path('storage/images/student_avatars/' . $imagePath),
            storage_path('app/public/student_avatars/' . $imagePath),
            public_path('storage/student_avatars/' . $imagePath),
            $imagePath,
        ];

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $foundPath = $path;
                break;
            }
        }

        if (!$foundPath) {
            return null;
        }

        try {
            // Check if GD is available
            if (extension_loaded('gd') && function_exists('imagecreatefromjpeg')) {
                $imageInfo = @getimagesize($foundPath);
                if (!$imageInfo) {
                    throw new \Exception('Invalid image file');
                }

                $mimeType = $imageInfo['mime'];

                switch ($mimeType) {
                    case 'image/jpeg':
                        $image = @imagecreatefromjpeg($foundPath);
                        break;
                    case 'image/png':
                        $image = @imagecreatefrompng($foundPath);
                        // Preserve transparency
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($foundPath);
                        break;
                    default:
                        // For unsupported types, return base64 without optimization
                        $imageData = base64_encode(file_get_contents($foundPath));
                        $result = 'data:' . $mimeType . ';base64,' . $imageData;
                        Cache::put($cacheKey, $result, now()->addHours(24));
                        return $result;
                }

                if (!$image) {
                    throw new \Exception('Failed to create image from file');
                }

                // Resize if too large
                $width = imagesx($image);
                if ($width > $maxWidth) {
                    $ratio = $maxWidth / $width;
                    $newWidth = $maxWidth;
                    $newHeight = (int)(imagesy($image) * $ratio);

                    $resized = imagecreatetruecolor($newWidth, $newHeight);

                    // Preserve transparency for PNG
                    if ($mimeType === 'image/png') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                        imagefill($resized, 0, 0, $transparent);
                    }

                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, imagesy($image));
                    imagedestroy($image);
                    $image = $resized;
                }

                // Output to buffer
                ob_start();
                if ($mimeType === 'image/png') {
                    imagepng($image, null, 9); // Maximum compression for PNG
                } else {
                    imagejpeg($image, null, 85); // 85% quality for JPEG
                }
                $imageData = ob_get_clean();
                imagedestroy($image);

                $result = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            } else {
                // GD not available, use simple base64 encoding
                $imageData = base64_encode(file_get_contents($foundPath));
                $mimeType = mime_content_type($foundPath);
                $result = 'data:' . $mimeType . ';base64,' . $imageData;
            }

            // Cache the result
            Cache::put($cacheKey, $result, now()->addHours(24));
            return $result;

        } catch (\Exception $e) {
            Log::warning('Failed to optimize image at ' . $foundPath . ': ' . $e->getMessage());

            // Fallback to simple base64 encoding
            try {
                $imageData = base64_encode(file_get_contents($foundPath));
                $mimeType = mime_content_type($foundPath);
                $result = 'data:' . $mimeType . ';base64,' . $imageData;
                Cache::put($cacheKey, $result, now()->addHours(24));
                return $result;
            } catch (\Exception $e2) {
                Log::error('Failed even with fallback: ' . $e2->getMessage());
                return null;
            }
        }
    }

    /**
     * Get school logo as base64
     */
    private function getSchoolLogoBase64($schoolInfo)
    {
        if (!$schoolInfo || !$schoolInfo->school_logo) {
            return null;
        }

        $cacheKey = 'school_logo_' . md5($schoolInfo->school_logo);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $possiblePaths = [
            storage_path('app/public/' . $schoolInfo->school_logo),
            public_path('storage/' . $schoolInfo->school_logo),
            storage_path('app/public/school_logos/' . $schoolInfo->school_logo),
            public_path('storage/school_logos/' . $schoolInfo->school_logo),
            $schoolInfo->school_logo,
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                try {
                    $imageData = base64_encode(file_get_contents($path));
                    $mimeType = mime_content_type($path);
                    $result = 'data:' . $mimeType . ';base64,' . $imageData;
                    Cache::put($cacheKey, $result, now()->addHours(24));
                    return $result;
                } catch (\Exception $e) {
                    Log::warning('Failed to encode school logo at ' . $path . ': ' . $e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Get simple image as base64 (for non-GD environments)
     */
    private function getImageBase64($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        $cacheKey = 'image_base64_' . md5($imagePath);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $possiblePaths = [
            storage_path('app/public/images/student_avatars/' . $imagePath),
            public_path('storage/images/student_avatars/' . $imagePath),
            storage_path('app/public/student_avatars/' . $imagePath),
            public_path('storage/student_avatars/' . $imagePath),
            $imagePath,
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                try {
                    $imageData = base64_encode(file_get_contents($path));
                    $mimeType = mime_content_type($path);
                    $result = 'data:' . $mimeType . ';base64,' . $imageData;
                    Cache::put($cacheKey, $result, now()->addHours(24));
                    return $result;
                } catch (\Exception $e) {
                    Log::warning('Failed to encode image at ' . $path . ': ' . $e->getMessage());
                }
            }
        }

        return null;
    }





    /**
 * Get student's current term (marked as current in database)
 */
public function getCurrentTerm($studentId)
{
    try {
        $currentTerm = StudentCurrentTerm::getCurrentForStudent($studentId);

        if (!$currentTerm) {
            return response()->json([
                'success' => false,
                'message' => 'No current term found for student'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currentTerm
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching current term: ' . $e->getMessage()
        ], 500);
    }
}




/**
 * Get student's active term based on system active term
 */
public function getActiveTerm($studentId)
{
    try {
        // Get system active term and session
        $activeTerm = Schoolterm::where('status', true)->first();
        $activeSession = Schoolsession::where('status', 'Current')->first();

        if (!$activeTerm || !$activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'No active term or session found in system'
            ], 404);
        }

        // Find the student's term record for the active system term
        $activeTermRecord = StudentCurrentTerm::with(['schoolClass.armRelation', 'term', 'session'])
            ->where('studentId', $studentId)
            ->where('termId', $activeTerm->id)
            ->where('sessionId', $activeSession->id)
            ->first();

        if (!$activeTermRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Student is not registered in the current active term'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activeTermRecord->id,
                'studentId' => $activeTermRecord->studentId,
                'schoolclassId' => $activeTermRecord->schoolclassId,
                'termId' => $activeTermRecord->termId,
                'sessionId' => $activeTermRecord->sessionId,
                'is_current' => $activeTermRecord->is_current,
                'schoolClass' => $activeTermRecord->schoolClass ? [
                    'id' => $activeTermRecord->schoolClass->id,
                    'schoolclass' => $activeTermRecord->schoolClass->schoolclass,
                    'armRelation' => $activeTermRecord->schoolClass->armRelation ? [
                        'id' => $activeTermRecord->schoolClass->armRelation->id,
                        'arm' => $activeTermRecord->schoolClass->armRelation->arm
                    ] : null
                ] : null,
                'term' => $activeTermRecord->term ? [
                    'id' => $activeTermRecord->term->id,
                    'term' => $activeTermRecord->term->term, // Field is 'term' not 'name'
                    'status' => $activeTermRecord->term->status
                ] : null,
                'session' => $activeTermRecord->session ? [
                    'id' => $activeTermRecord->session->id,
                    'session' => $activeTermRecord->session->session, // Field is 'session' not 'name'
                    'status' => $activeTermRecord->session->status
                ] : null
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching active term: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Get current term info for a student
 */
public function getCurrentInfo($id)
{
    try {
        $student = Student::with(['currentTerm.schoolClass.armRelation', 'currentTerm.term', 'currentTerm.session'])
            ->findOrFail($id);

        if (!$student->currentTerm) {
            return response()->json([
                'success' => false,
                'message' => 'No current term assigned to this student'
            ], 404);
        }

        $currentTerm = $student->currentTerm;

        return response()->json([
            'success' => true,
            'data' => [
                'student_id' => $student->id,
                'admission_no' => $student->admissionNo,
                'name' => $student->firstname . ' ' . $student->lastname,
                'current_class_id' => $currentTerm->schoolclassId,
                'current_class' => $currentTerm->schoolClass ? $currentTerm->schoolClass->schoolclass : 'N/A',
                'current_class_arm' => $currentTerm->schoolClass && $currentTerm->schoolClass->armRelation
                    ? $currentTerm->schoolClass->armRelation->arm
                    : 'N/A',
                'current_term_id' => $currentTerm->termId,
                'current_term' => $currentTerm->term ? $currentTerm->term->term : 'N/A', // Field is 'term' not 'name'
                'current_session_id' => $currentTerm->sessionId,
                'current_session' => $currentTerm->session ? $currentTerm->session->session : 'N/A', // Field is 'session' not 'name'
                'is_current' => $currentTerm->is_current
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching current info: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get all registered terms for a student
 */
public function getAllRegisteredTerms($id)
{
    try {
        $terms = StudentCurrentTerm::where('studentId', $id)
            ->with(['schoolClass.armRelation', 'term', 'session'])
            ->orderBy('sessionId', 'desc')
            ->orderBy('termId', 'desc')
            ->get()
            ->map(function($term) {
                return [
                    'id' => $term->id,
                    'term_id' => $term->termId,
                    'term_name' => $term->term ? $term->term->term : 'N/A', // Field is 'term' not 'name'
                    'session_id' => $term->sessionId,
                    'session_name' => $term->session ? $term->session->session : 'N/A', // Field is 'session' not 'name'
                    'class_id' => $term->schoolclassId,
                    'class_name' => $term->schoolClass ? $term->schoolClass->schoolclass : 'N/A',
                    'arm_name' => $term->schoolClass && $term->schoolClass->armRelation
                        ? $term->schoolClass->armRelation->arm
                        : 'N/A',
                    'is_current' => $term->is_current,
                    'created_at' => $term->created_at,
                    'updated_at' => $term->updated_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $terms
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching registered terms: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Get students by current class, term, and session
 */
public function getStudentsByCurrentFilters(Request $request)
{
    $request->validate([
        'classId' => 'nullable|exists:schoolclass,id',
        'termId' => 'nullable|exists:schoolterm,id',
        'sessionId' => 'nullable|exists:schoolsession,id'
    ]);

    try {
        $query = StudentCurrentTerm::with(['student', 'schoolClass', 'term', 'session'])
            ->where('is_current', true);

        if ($request->filled('classId')) {
            $query->where('schoolclassId', $request->classId);
        }

        if ($request->filled('termId')) {
            $query->where('termId', $request->termId);
        }

        if ($request->filled('sessionId')) {
            $query->where('sessionId', $request->sessionId);
        }

        $students = $query->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching students: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update student's current term
 */
public function updateCurrentTerm(Request $request, $studentId)
{
    $request->validate([
        'schoolclassId' => 'required|exists:schoolclass,id',
        'termId' => 'required|exists:schoolterm,id',
        'sessionId' => 'required|exists:schoolsession,id',
        'is_current' => 'sometimes|boolean'
    ]);

    try {
        // Check if student exists
        $student = Student::find($studentId);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Use the new registerTerm method
        $currentTerm = StudentCurrentTerm::registerTerm(
            $studentId,
            $request->schoolclassId,
            $request->termId,
            $request->sessionId,
            $request->input('is_current', true)
        );

        return response()->json([
            'success' => true,
            'message' => 'Term registered successfully',
            'data' => $currentTerm
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error registering term: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Bulk update current term for multiple students
 */
public function bulkUpdateCurrentTerm(Request $request)
{
    $request->validate([
        'student_ids' => 'required|array',
        'student_ids.*' => 'exists:studentRegistration,id',
        'schoolclassId' => 'required|exists:schoolclass,id',
        'termId' => 'required|exists:schoolterm,id',
        'sessionId' => 'required|exists:schoolsession,id',
        'is_current' => 'sometimes|boolean'
    ]);

    try {
        DB::beginTransaction();

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($request->student_ids as $studentId) {
            try {
                // Check if student exists
                $student = Student::find($studentId);
                if (!$student) {
                    $results[$studentId] = 'Student not found';
                    $failedCount++;
                    continue;
                }

                // Use registerTerm for each student
                $currentTerm = StudentCurrentTerm::registerTerm(
                    $studentId,
                    $request->schoolclassId,
                    $request->termId,
                    $request->sessionId,
                    $request->input('is_current', true)
                );

                $results[$studentId] = 'Success';
                $successCount++;

            } catch (\Exception $e) {
                Log::error("Error registering term for student {$studentId}: " . $e->getMessage());
                $results[$studentId] = 'Failed: ' . $e->getMessage();
                $failedCount++;
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Registered term for {$successCount} student(s). Failed: {$failedCount}.",
            'data' => $results,
            'summary' => [
                'total' => count($request->student_ids),
                'success' => $successCount,
                'failed' => $failedCount
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Bulk register term error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to register term: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Get students by class and session for status update
 */
/**
 * Get students by class and session for status update
 */
public function getStudentsByClassAndSession(Request $request)
{
    try {
        $request->validate([
            'class_id' => 'required|exists:schoolclass,id',
            'session_id' => 'required|exists:schoolsession,id',
        ]);

        $query = Student::query()
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->where('studentclass.schoolclassid', $request->class_id)
            ->where('studentclass.sessionid', $request->session_id);

        $students = $query->select([
            'studentRegistration.id',
            'studentRegistration.admissionNo',
            'studentRegistration.firstname',
            'studentRegistration.lastname',
            'studentRegistration.othername',
            'studentRegistration.gender',
            'studentRegistration.statusId',
            'studentRegistration.student_status',
            'studentRegistration.dateofbirth',
            'studentRegistration.created_at',
            'studentpicture.picture',
            'schoolclass.schoolclass',
            'schoolarm.arm',
        ])->get();

        // Format the students data to handle null values
        $formattedStudents = $students->map(function($student) {
            // Handle date of birth safely
            $formattedDob = null;
            if ($student->dateofbirth) {
                try {
                    $formattedDob = $student->dateofbirth instanceof \Carbon\Carbon
                        ? $student->dateofbirth->format('Y-m-d')
                        : date('Y-m-d', strtotime($student->dateofbirth));
                } catch (\Exception $e) {
                    $formattedDob = null;
                }
            }

            return [
                'id' => $student->id,
                'admissionNo' => $student->admissionNo ?? 'N/A',
                'firstname' => $student->firstname ?? '',
                'lastname' => $student->lastname ?? '',
                'othername' => $student->othername ?? '',
                'fullname' => trim(($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? '')),
                'gender' => $student->gender ?? 'N/A',
                'statusId' => $student->statusId,
                'student_status' => $student->student_status ?? 'Inactive',
                'dateofbirth' => $formattedDob,
                'picture' => $student->picture ?? null,
                'schoolclass' => $student->schoolclass ?? '',
                'arm' => $student->arm ?? '',
                'created_at' => $student->created_at ? $student->created_at->format('Y-m-d') : null,
            ];
        });

        // Calculate stats
        $stats = [
            'total' => $formattedStudents->count(),
            'active' => $formattedStudents->where('student_status', 'Active')->count(),
            'inactive' => $formattedStudents->where('student_status', 'Inactive')->count(),
            'old_students' => $formattedStudents->where('statusId', 1)->count(),
            'new_students' => $formattedStudents->where('statusId', 2)->count(),
        ];

        return response()->json([
            'success' => true,
            'students' => $formattedStudents,
            'stats' => $stats
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching students by class/session: ' . $e->getMessage());
        Log::error($e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch students: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Bulk update student status (active/inactive and old/new)
 */
public function bulkUpdateStatus(Request $request)
{
    try {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:studentRegistration,id',
            'update_type' => 'required|in:activity_status,student_type',
            'value' => 'required'
        ]);

        DB::beginTransaction();

        $studentIds = $request->student_ids;
        $updateType = $request->update_type;
        $value = $request->value;

        $updated = 0;

        if ($updateType === 'activity_status') {
            // Update student_status (Active/Inactive)
            $updated = Student::whereIn('id', $studentIds)
                ->update(['student_status' => $value]);
        } else {
            // Update statusId (1=Old, 2=New)
            $statusId = $value === 'old' ? 1 : 2;
            $updated = Student::whereIn('id', $studentIds)
                ->update(['statusId' => $statusId]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updated} student(s)",
            'updated_count' => $updated
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error bulk updating student status: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to update students: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get students registered in a specific term/session (via StudentCurrentTerm)
 */
/**
 * Get students registered in a specific term/session (via StudentCurrentTerm)
 */
public function getStudentsInTerm(Request $request)
{
    try {
        $request->validate([
            'term_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
            'class_id' => 'nullable|exists:schoolclass,id'
        ]);

        $query = StudentCurrentTerm::with([
            'student.picture',
            'schoolClass.armRelation',
            'term',
            'session'
        ])
        ->where('termId', $request->term_id)
        ->where('sessionId', $request->session_id);

        if ($request->filled('class_id')) {
            $query->where('schoolclassId', $request->class_id);
        }

        $registrations = $query->get();

        $formattedStudents = $registrations->map(function($reg) {
            $student = $reg->student;

            // Handle potential null student
            if (!$student) {
                return null;
            }

            return [
                'registration_id' => $reg->id,
                'student_id' => $student->id,
                'admissionNo' => $student->admissionNo ?? 'N/A',
                'firstname' => $student->firstname ?? '',
                'lastname' => $student->lastname ?? '',
                'othername' => $student->othername ?? '',
                'fullname' => trim(($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? '')),
                'gender' => $student->gender ?? 'N/A',
                'class' => $reg->schoolClass ? $reg->schoolClass->schoolclass : 'N/A',
                'arm' => $reg->schoolClass && $reg->schoolClass->armRelation ? $reg->schoolClass->armRelation->arm : '',
                'term' => $reg->term ? $reg->term->term : 'N/A',
                'session' => $reg->session ? $reg->session->session : 'N/A',
                'is_current' => $reg->is_current,
                'picture' => $student->picture ? $student->picture->picture : null,
                'registered_at' => $reg->created_at ? $reg->created_at->format('d M Y') : 'N/A',
            ];
        })->filter(); // Remove null entries

        return response()->json([
            'success' => true,
            'students' => $formattedStudents->values(),
            'total' => $formattedStudents->count()
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching students in term: ' . $e->getMessage());
        Log::error($e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch students: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Remove a student from term registration (StudentCurrentTerm)
 */
public function removeFromTerm(Request $request)
{
    try {
        $request->validate([
            'registration_id' => 'required|exists:student_current_term,id'
        ]);

        DB::beginTransaction();

        $registration = StudentCurrentTerm::findOrFail($request->registration_id);
        $studentName = $registration->student ?
            $registration->student->firstname . ' ' . $registration->student->lastname : 'Unknown';

        $registration->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Student removed from term registration successfully",
            'student_name' => $studentName
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error removing student from term: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to remove student: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Bulk remove students from term registration
 */
public function bulkRemoveFromTerm(Request $request)
{
    try {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:student_current_term,id'
        ]);

        DB::beginTransaction();

        $count = StudentCurrentTerm::whereIn('id', $request->registration_ids)->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Successfully removed {$count} student(s) from term registration",
            'removed_count' => $count
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error bulk removing students from term: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to remove students: ' . $e->getMessage()
        ], 500);
    }
}
}
