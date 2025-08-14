<?php

namespace App\Http\Controllers;


use App\Models\BroadsheetRecord;
use App\Models\BroadsheetRecordMock;
use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\Schoolclass;
use App\Models\Student;
use App\Models\Studentpicture;
use App\Models\StudentSubjectRecord;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubjectOperationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject-operation|Create subject-operation|Update subject-operation|Delete subject-operation', ['only' => ['index', 'subjectinfo', 'getRegisteredClasses']]);
        $this->middleware('permission:Create subject-operation', ['only' => ['store']]);
        $this->middleware('permission:Delete subject-operation', ['only' => ['destroy']]);
    }

    /**
     * Display a list of students for subject registration with filters.
     */
    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\Response
    {
        $pagetitle = "Subject Operation Management";

        // Fetch dropdown data
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name', 'users.avatar as avatar']);

        $students = null;
        $subjectTeachers = null;

        // Check if filtering is requested
        if ($request->filled(['class_id', 'session_id']) && 
            $request->input('class_id') !== 'ALL' && 
            $request->input('session_id') !== 'ALL') {
            
            // Fetch subject teachers for the selected class and session
            $subjectTeachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->where('subjectteacher.sessionid', $request->input('session_id'))
                ->where('subjectclass.schoolclassid', $request->input('class_id'))
                ->select([
                    'subjectteacher.id as id',
                    'subjectclass.id as subjectclassid',
                    'users.id as userid',
                    'users.name as staffname',
                    'users.avatar as avatar',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname',
                    'schoolclass.schoolclass as class_name',
                    'schoolarm.arm as arm_name',
                    'subjectteacher.updated_at'
                ])
                ->get();

            // Fetch students
            $query = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass', 'studentclass.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm');

            // Apply filters
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionno', 'like', "%{$search}%")
                    ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                    ->orWhere('studentRegistration.lastname', 'like', "%{$search}%");
                });
            }
            
            if ($gender = $request->input('gender')) {
                if ($gender !== 'ALL') {
                    $query->where('studentRegistration.gender', $gender);
                }
            }
            
            if ($admissionNo = $request->input('admissionno')) {
                if ($admissionNo !== 'ALL') {
                    $query->where('studentRegistration.admissionno', $admissionNo);
                }
            }
            
            // Required filters
            $query->where('studentclass.schoolclassid', $request->input('class_id'))
                ->where('studentclass.sessionid', $request->input('session_id'));

            $students = $query->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionno as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'studentRegistration.gender',
                'studentRegistration.updated_at',
                'studentpicture.picture',
                'studentclass.studentid as studentid',
                'studentclass.schoolclassid as schoolclassid',
                'studentclass.sessionid',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name'
            ])->paginate(100)->appends($request->query());

            if (config('app.debug')) {
                Log::info('Students fetched', [
                    'count' => $students->count(),
                    'student_ids' => $students->pluck('id')->toArray(),
                    'filters' => $request->only(['class_id', 'session_id', 'search', 'gender', 'admissionno']),
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return view('subjectoperation.index', compact('students', 'subjectTeachers', 'pagetitle', 'schoolclass', 'schoolterms', 'schoolsessions'));
        }

        return view('subjectoperation.index', compact('students', 'subjectTeachers', 'pagetitle', 'schoolclass', 'schoolterms', 'schoolsessions'));
    }
        
    /**
     * Fetch subject teachers for AJAX request.
     */
    public function getSubjectTeachers(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');
    
        if (!$classId || !$termId || !$sessionId || 
            $classId === 'ALL' || $termId === 'ALL' || $sessionId === 'ALL') {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
    
        $subjectTeachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->where('subjectteacher.termid', $termId)
            ->where('subjectteacher.sessionid', $sessionId)
            ->where('subjectclass.schoolclassid', $classId)
            ->select([
                'subjectteacher.id as id',
                'subjectclass.id as subjectclassid',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name'
            ])
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $subjectTeachers,
            'count' => $subjectTeachers->count()
        ]);
    }

    /**
     * Display subject information for a specific student.
     */
    public function subjectinfo(Request $request, $id, $schoolclassid, $termid, $sessionid): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        $current = "Current";

        try {
            $pagetitle = "Subject Operation Management";

            Log::info('Fetching subject info for student', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
            ]);

            $studentdata = Student::where('id', $id)->get();
            if ($studentdata->isEmpty()) {
                Log::error('Student not found', ['student_id' => $id]);
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $studentpic = Studentpicture::where('studentid', $id)->select(['studentid', 'picture as avatar'])->get();

            $subjectclass = Subjectclass::query()
                ->where('subjectclass.schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->where('schoolterm.id', 2)
                ->where('schoolsession.id', $sessionid)
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('staffbioinfo', 'staffbioinfo.userid', '=', 'users.id')
                ->leftJoin('staffpicture', 'staffpicture.staffid', '=', 'users.id')
                ->groupBy([
                    'subject.id',
                    'users.id',
                    'staffbioinfo.title',
                    'users.name',
                    'staffpicture.picture',
                    'subject.subject',
                    'subject.subject_code',
                    'subjectclass.id',
                    'schoolterm.term',
                    'schoolterm.id',
                    'schoolsession.session',
                    'schoolsession.id'
                ])
                ->select([
                    'subject.id as subjectid',
                    'staffbioinfo.title',
                    'users.name',
                    'staffpicture.picture as picture',
                    'subject.subject',
                    'users.id as staffid',
                    'subject.subject_code as subjectcode',
                    'subjectclass.id as subjectclassid',
                    'schoolterm.term',
                    'schoolterm.id as termid',
                    'schoolsession.session',
                    'schoolsession.id as sessionid'
                ])
                ->get();

            if ($subjectclass->isEmpty()) {
                Log::warning('No subjects found for the given class, term, and session', [
                    'schoolclassid' => $schoolclassid,
                    'termid' => $termid,
                    'sessionid' => $sessionid,
                ]);
            }

            $subjectRegistrations = [];
            foreach ($subjectclass as $sc) {
                $subjectRegistrations[$sc->subjectid][$sc->staffid] = [
                    'subjectclassid' => $sc->subjectclassid,
                    'status' => StudentSubjectRecord::where([
                        'studentId' => $id,
                        'subjectclassid' => $sc->subjectclassid,
                        'staffid' => $sc->staffid,
                        'session' => $sessionid,
                    ])->exists() ? ['status' => 'Registered', 'broadsheetid' => SubjectRegistrationStatus::where([
                        'studentid' => $id,
                        'subjectclassid' => $sc->subjectclassid,
                        'staffid' => $sc->staffid,
                    ])->value('broadsheetid')] : ['status' => 'Not Registered', 'broadsheetid' => null],
                ];
            }

            $totalreg = Subjectclass::where('subjectclass.schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->where('schoolterm.id', 2)
                ->where('schoolsession.id', $sessionid)
                ->distinct('subjectteacher.subjectid')
                ->count('subjectteacher.subjectid');

            $regcount = StudentSubjectRecord::where('student_subject_register_record.studentId', $id)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                ->where('schoolterm.id', 2)
                ->where('schoolsession.status', $current)
                ->count();

            $noregcount = $totalreg - $regcount;

            $classname = Schoolclass::where('schoolclass.id', $schoolclassid)
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->select(['schoolclass.id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
                ->get();

            $terms = Schoolterm::all();

            if (config('app.debug')) {
                Log::info('Subject info for student ID: ' . $id, ['subjects' => $subjectclass->toArray()]);
            }

            return view('subjectoperation.subjectinfo', compact(
                'studentpic',
                'classname',
                'subjectclass',
                'subjectRegistrations',
                'studentdata',
                'id',
                'termid',
                'sessionid',
                'totalreg',
                'regcount',
                'noregcount',
                'pagetitle',
                'terms'
            ));
        } catch (\Exception $error) {
            Log::error('Error fetching subject info', [
                'student_id' => $id,
                'schoolclassid' => $schoolclassid,
                'termid' => $termid,
                'sessionid' => $sessionid,
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject information: ' . $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created subject registration for one or multiple students.
     */
    public function store(Request $request): array
    {
        $validated = $request->validate([
            'studentid' => ['required', 'array'],
            'studentid.*' => ['required', 'exists:studentRegistration,id'],
            'subjectclassid' => ['required', 'exists:subjectclass,id'],
            'staffid' => ['required', 'exists:users,id'],
            'termid' => ['required', 'exists:schoolterm,id'],
            'sessionid' => ['required', 'exists:schoolsession,id'],
        ]);

        $studentCount = count($validated['studentid']);
        
        // Configuration thresholds
        $batchThreshold = 50; // Use batch processing if more than 50 students
        $largeDatasetThreshold = 500; // Special handling for very large datasets
        
        Log::info('Subject Registration Started', [
            'student_count' => $studentCount,
            'processing_method' => $studentCount > $batchThreshold ? 'batch' : 'individual',
            'subjectclassid' => $validated['subjectclassid'],
            'termid' => $validated['termid'],
        ]);

        // Choose processing method based on dataset size
        if ($studentCount <= $batchThreshold) {
            return $this->processIndividually($validated);
        } elseif ($studentCount <= $largeDatasetThreshold) {
            return $this->processBatch($validated);
        } else {
            return $this->processLargeDataset($validated);
        }
    }

    /**
     * Batch registration for students and subjects.
     */
    public function batchRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'studentids' => ['required', 'array'],
            'studentids.*' => ['required', 'exists:studentRegistration,id'],
            'subjectclasses' => ['required', 'array'],
            'subjectclasses.*.subjectclassid' => ['required', 'exists:subjectclass,id'],
            'subjectclasses.*.staffid' => ['required', 'exists:users,id'],
            'subjectclasses.*.termid' => ['required', 'exists:schoolterm,id'],
            'sessionid' => ['required', 'exists:schoolsession,id'],
        ]);

        $results = [];
        $errors = [];
        $successCount = 0;

        try {
            DB::beginTransaction();

            foreach ($validated['subjectclasses'] as $subject) {
                $subjectclassid = $subject['subjectclassid'];
                $staffid = $subject['staffid'];
                $termid = $subject['termid'];
                $sessionid = $validated['sessionid'];

                $response = $this->processIndividually([
                    'studentid'      => $validated['studentids'],
                    'subjectclassid' => $subjectclassid,
                    'staffid'        => $staffid,
                    'termid'         => $termid,
                    'sessionid'      => $sessionid,
                ]);

                if ($response['success']) {
                    $successCount += $response['success_count'];
                } else {
                    $errors[] = [
                        'subjectclassid' => $subjectclassid,
                        'termid'         => $termid,
                        'message'        => $response['message'] ?? 'Error',
                        'details'        => $response['errors'] ?? [],
                    ];
                }
                $results[] = $response;
            }

            DB::commit();

            return response()->json([
                'success'       => empty($errors),
                'message'       => 'Batch registration completed.',
                'results'       => $results,
                'error_details' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch registration failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Batch registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process students individually - Best for small datasets (≤50 students)
     * Provides detailed error handling and precise duplicate detection
     */
    private function processIndividually(array $validated): array
    {
        $results = [];
        $successCount = 0;
        $errors = [];
        $skippedCount = 0;

        try {
            DB::beginTransaction();

            $subjectclass = Subjectclass::findOrFail($validated['subjectclassid']);
            $subjectId = $subjectclass->subjectid;
            $schoolclassId = $subjectclass->schoolclassid;

            // Pre-check for existing registrations
            $existingRegistrations = SubjectRegistrationStatus::where([
                'subjectclassid' => $validated['subjectclassid'],
                'termid' => $validated['termid'],
                'sessionid' => $validated['sessionid'],
            ])->whereIn('studentid', $validated['studentid'])
              ->pluck('studentid')
              ->toArray();

            $studentsToProcess = array_diff($validated['studentid'], $existingRegistrations);
            $skippedCount = count($existingRegistrations);

            foreach ($existingRegistrations as $existingStudentId) {
                $errors[] = "Student ID {$existingStudentId} is already registered";
            }

            if (empty($studentsToProcess)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'All students are already registered for this subject.',
                    'errors' => $errors,
                    'skipped_count' => $skippedCount,
                ];
            }

            foreach ($studentsToProcess as $studentId) {
                try {
                    // Create or find BroadsheetRecord
                    $record = BroadsheetRecord::firstOrCreate([
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'schoolclass_id' => $schoolclassId,
                        'session_id' => $validated['sessionid'],
                    ]);

                    $recordmock = BroadsheetRecordMock::firstOrCreate([
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'schoolclass_id' => $schoolclassId,
                        'session_id' => $validated['sessionid'],
                    ]);

                    // Create dependent records if they don't exist
                    $this->createDependentRecords($record->id, $recordmock->id, $studentId, $validated);

                    $successCount++;
                    $results[] = "Successfully registered student ID {$studentId}";

                } catch (\Exception $e) {
                    Log::error("Error processing student {$studentId}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[] = "Failed to register student ID {$studentId}: " . $e->getMessage();
                    continue;
                }
            }

            if ($successCount > 0) {
                DB::commit();
                return [
                    'success' => true,
                    'message' => "Individual processing: {$successCount} students registered successfully",
                    'method' => 'individual',
                    'results' => $results,
                    'errors' => $errors,
                    'success_count' => $successCount,
                    'skipped_count' => $skippedCount,
                ];
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No students were registered.',
                    'errors' => $errors,
                    'skipped_count' => $skippedCount,
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Individual processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Individual processing failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Process students in batch - Best for medium datasets (51-500 students)
     * Balances performance with error handling
     */
    private function processBatch(array $validated): array
    {
        try {
            DB::beginTransaction();

            $subjectclass = Subjectclass::findOrFail($validated['subjectclassid']);
            $subjectId = $subjectclass->subjectid;
            $schoolclassId = $subjectclass->schoolclassid;
            $now = now();

            // Filter out already registered students
            $existingRegistrations = SubjectRegistrationStatus::where([
                'subjectclassid' => $validated['subjectclassid'],
                'termid' => $validated['termid'],
                'sessionid' => $validated['sessionid'],
            ])->whereIn('studentid', $validated['studentid'])
              ->pluck('studentid')
              ->toArray();

            $studentsToProcess = array_diff($validated['studentid'], $existingRegistrations);
            $skippedCount = count($existingRegistrations);

            if (empty($studentsToProcess)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'All students are already registered.',
                    'skipped_count' => $skippedCount,
                ];
            }

            // Prepare bulk insert data for BroadsheetRecords
            $broadsheetRecords = [];
            $broadsheetRecordsMock = [];
            
            foreach ($studentsToProcess as $studentId) {
                $broadsheetRecords[] = [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'schoolclass_id' => $schoolclassId,
                    'session_id' => $validated['sessionid'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $broadsheetRecordsMock[] = [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'schoolclass_id' => $schoolclassId,
                    'session_id' => $validated['sessionid'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Bulk insert BroadsheetRecords
            BroadsheetRecord::insertOrIgnore($broadsheetRecords);
            BroadsheetRecordMock::insertOrIgnore($broadsheetRecordsMock);

            // Get the created records with their IDs
            $createdRecords = BroadsheetRecord::where([
                'subject_id' => $subjectId,
                'schoolclass_id' => $schoolclassId,
                'session_id' => $validated['sessionid'],
            ])->whereIn('student_id', $studentsToProcess)
              ->get()
              ->keyBy('student_id');

            $createdRecordsMock = BroadsheetRecordMock::where([
                'subject_id' => $subjectId,
                'schoolclass_id' => $schoolclassId,
                'session_id' => $validated['sessionid'],
            ])->whereIn('student_id', $studentsToProcess)
              ->get()
              ->keyBy('student_id');

            // Prepare and insert dependent records
            $this->bulkCreateDependentRecords($createdRecords, $createdRecordsMock, $studentsToProcess, $validated, $now);

            DB::commit();

            return [
                'success' => true,
                'message' => "Batch processing: " . count($studentsToProcess) . " students registered successfully",
                'method' => 'batch',
                'success_count' => count($studentsToProcess),
                'skipped_count' => $skippedCount,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Batch processing failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Process very large datasets in chunks - Best for large datasets (>500 students)
     * Optimized for memory efficiency and performance
     */
    private function processLargeDataset(array $validated): array
    {
        try {
            DB::beginTransaction();

            $subjectclass = Subjectclass::findOrFail($validated['subjectclassid']);
            $subjectId = $subjectclass->subjectid;
            $schoolclassId = $subjectclass->schoolclassid;
            
            $chunkSize = 200; // Process in chunks of 200 students
            $totalStudents = count($validated['studentid']);
            $totalProcessed = 0;
            $totalSkipped = 0;
            $chunks = array_chunk($validated['studentid'], $chunkSize);

            Log::info("Large dataset processing started", [
                'total_students' => $totalStudents,
                'chunks' => count($chunks),
                'chunk_size' => $chunkSize,
            ]);

            foreach ($chunks as $chunkIndex => $studentChunk) {
                Log::info("Processing chunk " . ($chunkIndex + 1) . "/" . count($chunks));

                // Filter already registered students for this chunk
                $existingInChunk = SubjectRegistrationStatus::where([
                    'subjectclassid' => $validated['subjectclassid'],
                    'termid' => $validated['termid'],
                    'sessionid' => $validated['sessionid'],
                ])->whereIn('studentid', $studentChunk)
                  ->pluck('studentid')
                  ->toArray();

                $studentsToProcess = array_diff($studentChunk, $existingInChunk);
                $totalSkipped += count($existingInChunk);

                if (empty($studentsToProcess)) {
                    continue; // Skip this chunk if all students are already registered
                }

                // Process this chunk
                $this->processChunk($studentsToProcess, $validated, $subjectId, $schoolclassId);
                $totalProcessed += count($studentsToProcess);

                // Clear memory periodically
                if (($chunkIndex + 1) % 5 == 0) {
                    gc_collect_cycles();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Large dataset processing: {$totalProcessed} students registered successfully",
                'method' => 'large_dataset_chunks',
                'success_count' => $totalProcessed,
                'skipped_count' => $totalSkipped,
                'total_chunks' => count($chunks),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Large dataset processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Large dataset processing failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Process a single chunk of students
     */
    private function processChunk(array $students, array $validated, int $subjectId, int $schoolclassId): void
    {
        $now = now();

        // Bulk insert BroadsheetRecords for this chunk
        $broadsheetRecords = [];
        $broadsheetRecordsMock = [];
        
        foreach ($students as $studentId) {
            $broadsheetRecords[] = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'schoolclass_id' => $schoolclassId,
                'session_id' => $validated['sessionid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $broadsheetRecordsMock[] = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'schoolclass_id' => $schoolclassId,
                'session_id' => $validated['sessionid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        BroadsheetRecord::insertOrIgnore($broadsheetRecords);
        BroadsheetRecordMock::insertOrIgnore($broadsheetRecordsMock);

        // Get created records and create dependent records
        $createdRecords = BroadsheetRecord::where([
            'subject_id' => $subjectId,
            'schoolclass_id' => $schoolclassId,
            'session_id' => $validated['sessionid'],
        ])->whereIn('student_id', $students)
          ->get()
          ->keyBy('student_id');

        $createdRecordsMock = BroadsheetRecordMock::where([
            'subject_id' => $subjectId,
            'schoolclass_id' => $schoolclassId,
            'session_id' => $validated['sessionid'],
        ])->whereIn('student_id', $students)
          ->get()
          ->keyBy('student_id');

        $this->bulkCreateDependentRecords($createdRecords, $createdRecordsMock, $students, $validated, $now);
    }

    /**
     * Create dependent records for individual processing
     */
    private function createDependentRecords(int $recordId, int $recordMockId, int $studentId, array $validated): void
    {
        // Create Broadsheet if it doesn't exist
        Broadsheets::firstOrCreate([
            'broadsheet_record_id' => $recordId,
            'term_id' => $validated['termid'],
            'subjectclass_id' => $validated['subjectclassid'],
        ], [
            'staff_id' => $validated['staffid'],
        ]);

        // Create BroadsheetMock if it doesn't exist
        BroadsheetsMock::firstOrCreate([
            'broadsheet_records_mock_id' => $recordMockId,
            'term_id' => $validated['termid'],
            'subjectclass_id' => $validated['subjectclassid'],
        ], [
            'staff_id' => $validated['staffid'],
        ]);

        // Create SubjectRegistrationStatus if it doesn't exist
        SubjectRegistrationStatus::firstOrCreate([
            'studentid' => $studentId,
            'subjectclassid' => $validated['subjectclassid'],
            'termid' => $validated['termid'],
            'sessionid' => $validated['sessionid'],
            'staffid' => $validated['staffid'],
        ], [
            'broadsheetid' => $recordId,
            'Status' => 1,
        ]);

        // Create StudentSubjectRecord if it doesn't exist
        StudentSubjectRecord::firstOrCreate([
            'studentId' => $studentId,
            'subjectclassid' => $validated['subjectclassid'],
            'staffid' => $validated['staffid'],
            'session' => $validated['sessionid'],
        ]);
    }

    /**
     * Bulk create dependent records for batch processing
     */
    private function bulkCreateDependentRecords($createdRecords, $createdRecordsMock, array $students, array $validated, $now): void
    {
        $broadsheets = [];
        $broadsheetsMock = [];
        $subjectRegistrations = [];
        $studentSubjectRecords = [];

        foreach ($students as $studentId) {
            $record = $createdRecords->get($studentId);
            $recordMock = $createdRecordsMock->get($studentId);

            if (!$record || !$recordMock) {
                Log::error("Could not find broadsheet record for student {$studentId}");
                continue;
            }

            $broadsheets[] = [
                'broadsheet_record_id' => $record->id,
                'term_id' => $validated['termid'],
                'subjectclass_id' => $validated['subjectclassid'],
                'staff_id' => $validated['staffid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $broadsheetsMock[] = [
                'broadsheet_records_mock_id' => $recordMock->id,
                'term_id' => $validated['termid'],
                'subjectclass_id' => $validated['subjectclassid'],
                'staff_id' => $validated['staffid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $subjectRegistrations[] = [
                'studentid' => $studentId,
                'subjectclassid' => $validated['subjectclassid'],
                'staffid' => $validated['staffid'],
                'termid' => $validated['termid'],
                'sessionid' => $validated['sessionid'],
                'broadsheetid' => $record->id,
                'Status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $studentSubjectRecords[] = [
                'studentId' => $studentId,
                'subjectclassid' => $validated['subjectclassid'],
                'staffid' => $validated['staffid'],
                'session' => $validated['sessionid'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert all dependent records
        if (!empty($broadsheets)) {
            Broadsheets::insertOrIgnore($broadsheets);
        }
        if (!empty($broadsheetsMock)) {
            BroadsheetsMock::insertOrIgnore($broadsheetsMock);
        }
        if (!empty($subjectRegistrations)) {
            SubjectRegistrationStatus::insertOrIgnore($subjectRegistrations);
        }
        if (!empty($studentSubjectRecords)) {
            StudentSubjectRecord::insertOrIgnore($studentSubjectRecords);
        }
    }

    /**
     * Remove subject registrations for selected students and subjects.
     */
   

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'studentids' => ['required', 'array'],
            'studentids.*' => ['required', 'exists:studentRegistration,id'],
            'subjectclasses' => ['required', 'array'],
            'subjectclasses.*.subjectclassid' => ['required', 'exists:subjectclass,id'],
            'subjectclasses.*.staffid' => ['required', 'exists:users,id'],
            'subjectclasses.*.termid' => ['required', 'exists:schoolterm,id'],
            'sessionid' => ['required', 'exists:schoolsession,id'],
        ]);

        $results = [];
        $errors = [];
        $unregisteredStudents = []; // Track unique students unregistered
        $skippedCount = 0;

        try {
            DB::beginTransaction();

            foreach ($validated['subjectclasses'] as $subject) {
                $subjectclassid = $subject['subjectclassid'];
                $staffid = $subject['staffid'];
                $termid = $subject['termid'];
                $sessionid = $validated['sessionid'];

                // Fetch subject details to get subject_id and schoolclass_id
                $subjectclass = Subjectclass::findOrFail($subjectclassid);
                $subjectId = $subjectclass->subjectid;
                $schoolclassId = $subjectclass->schoolclassid;

                // Check for existing registrations for this specific subject, term, session, and staff
                $existingRegistrations = SubjectRegistrationStatus::where([
                    'subjectclassid' => $subjectclassid,
                    'termid' => $termid,
                    'sessionid' => $sessionid,
                    'staffid' => $staffid,
                ])->whereIn('studentid', $validated['studentids'])
                ->get()
                ->keyBy('studentid');

                $studentsToProcess = array_intersect($validated['studentids'], array_keys($existingRegistrations->toArray()));
                $skippedCount += count(array_diff($validated['studentids'], $studentsToProcess));

                if (empty($studentsToProcess)) {
                    $errors[] = [
                        'subjectclassid' => $subjectclassid,
                        'termid' => $termid,
                        'message' => 'No students are registered for this subject.',
                    ];
                    continue;
                }

                // Track unique students being unregistered
                $unregisteredStudents = array_unique(array_merge($unregisteredStudents, $studentsToProcess));

                // Get broadsheet IDs for related table deletions
                $broadsheetIds = $existingRegistrations->pluck('broadsheetid')->filter()->toArray();

                // Delete from BroadsheetRecordMock
                $broadsheetRecordMockDeleted = BroadsheetRecordMock::whereIn('student_id', $studentsToProcess)
                    ->where('subject_id', $subjectId)
                    ->where('schoolclass_id', $schoolclassId)
                    ->where('session_id', $sessionid)
                    ->delete();

                // Delete from BroadsheetsMock
                $broadsheetsMockDeleted = BroadsheetsMock::whereIn('broadsheet_records_mock_id', $broadsheetIds)
                    ->where('subjectclass_id', $subjectclassid)
                    ->where('term_id', $termid)
                    ->where('staff_id', $staffid)
                    ->delete();

                // Delete from Broadsheets
                $broadsheetsDeleted = Broadsheets::whereIn('broadsheet_record_id', $broadsheetIds)
                    ->where('term_id', $termid)
                    ->where('subjectclass_id', $subjectclassid)
                    ->delete();

                // Delete from BroadsheetRecord
                $broadsheetRecordDeleted = BroadsheetRecord::whereIn('id', $broadsheetIds)->delete();

                // Delete from StudentSubjectRecord
                $studentSubjectRecordDeleted = StudentSubjectRecord::whereIn('studentId', $studentsToProcess)
                    ->where('subjectclassid', $subjectclassid)
                    ->where('staffid', $staffid)
                    ->where('session', $sessionid)
                    ->delete();

                // Delete from SubjectRegistrationStatus
                $subjectRegistrationStatusDeleted = SubjectRegistrationStatus::whereIn('studentid', $studentsToProcess)
                    ->where('subjectclassid', $subjectclassid)
                    ->where('termid', $termid)
                    ->where('sessionid', $sessionid)
                    ->where('staffid', $staffid)
                    ->delete();

                // Log deletion details
                Log::info('Unregistered subjects for students', [
                    'subjectclassid' => $subjectclassid,
                    'termid' => $termid,
                    'sessionid' => $sessionid,
                    'subject_id' => $subjectId,
                    'schoolclass_id' => $schoolclassId,
                    'staff_id' => $staffid,
                    'student_count' => count($studentsToProcess),
                    'student_ids' => $studentsToProcess,
                    'broadsheet_ids' => $broadsheetIds,
                    'broadsheet_record_mock_deleted' => $broadsheetRecordMockDeleted,
                    'broadsheets_mock_deleted' => $broadsheetsMockDeleted,
                    'broadsheets_deleted' => $broadsheetsDeleted,
                    'broadsheet_record_deleted' => $broadsheetRecordDeleted,
                    'student_subject_record_deleted' => $studentSubjectRecordDeleted,
                    'subject_registration_status_deleted' => $subjectRegistrationStatusDeleted,
                ]);

                $results[] = [
                    'subjectclassid' => $subjectclassid,
                    'termid' => $termid,
                    'message' => "Successfully unregistered " . count($studentsToProcess) . " students for subject",
                    'students_unregistered' => $studentsToProcess,
                ];
            }

            $successCount = count($unregisteredStudents); // Count unique students

            if ($successCount === 0 && !empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No students were unregistered.',
                    'error_details' => $errors,
                    'success_count' => 0,
                    'skipped_count' => $skippedCount,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => empty($errors),
                'message' => "Successfully unregistered {$successCount} student(s) from " . count($validated['subjectclasses']) . " subject(s).",
                'results' => $results,
                'error_details' => $errors,
                'success_count' => $successCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch unregistration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Batch unregistration failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Fetch registered classes for reporting.
     */
    public function registeredClasses(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate parameters
            $validated = $request->validate([
                'class_id' => ['required', 'integer', 'exists:schoolclass,id'],
                'session_id' => ['required', 'integer', 'exists:schoolsession,id'],
                'term_id' => ['nullable', 'integer', 'exists:schoolterm,id'],
            ]);

            Log::info('Fetching registered classes', [
                'class_id' => $validated['class_id'],
                'session_id' => $validated['session_id'],
                'term_id' => $validated['term_id'],
            ]);

            DB::statement('SET SESSION group_concat_max_len = 1000000');

            $query = SubjectRegistrationStatus::query()
                ->join('subjectclass', 'subjectclass.id', '=', 'subject_registration_status.subjectclassid')
                ->join('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->join('schoolsession', 'schoolsession.id', '=', 'subject_registration_status.sessionid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subject_registration_status.termid')
                ->leftJoin('broadsheet', 'broadsheet.id', '=', 'subject_registration_status.broadsheetid')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet.subjectid')
                ->leftJoin('subjectteacher', 'subjectteacher.subjectid', '=', 'subject.id')
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->where('subjectclass.schoolclassid', $validated['class_id'])
                ->where('subject_registration_status.sessionid', $validated['session_id'])
                ->when($validated['term_id'], function ($query, $termId) {
                    return $query->where('subject_registration_status.termid', $termId);
                }, function ($query) {
                    return $query->whereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                                ->from('schoolterm')
                                ->whereColumn('schoolterm.id', 'subject_registration_status.termid')
                                ->where('schoolterm.currentterm', 1);
                    });
                })
                ->groupBy([
                    'schoolclass.id',
                    'schoolarm.id',
                    'schoolsession.id',
                    'schoolterm.id',
                    'schoolclass.schoolclass',
                    'schoolarm.arm',
                    'schoolsession.session',
                    'schoolterm.term',
                ])
                ->select([
                    'schoolclass.id as class_id',
                    'schoolclass.schoolclass as class_name',
                    \DB::raw('COALESCE(schoolarm.arm, "None") as arm_name'),
                    \DB::raw('COALESCE(schoolsession.session, "Unknown") as session_name'),
                    \DB::raw('COALESCE(schoolterm.term, "Unknown") as term_name'),
                    \DB::raw('COUNT(DISTINCT subject_registration_status.studentid) as student_count'),
                    \DB::raw('COUNT(DISTINCT subject_registration_status.subjectclassid) as subject_count'),
                    \DB::raw('COALESCE(GROUP_CONCAT(DISTINCT subject.subject ORDER BY subject.subject SEPARATOR ", "), "None") as subjects'),
                    \DB::raw('COALESCE(GROUP_CONCAT(DISTINCT users.name ORDER BY users.name SEPARATOR ", "), "None") as teachers'),
                ]);

            Log::debug('Registered classes query', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

            $rawData = DB::select($query->toSql(), $query->getBindings());
            Log::debug('Registered classes raw data', ['raw_data' => json_encode($rawData)]);

            $classes = $query->get();

            Log::debug('Registered classes results', ['data' => $classes->toArray()]);

            return response()->json([
                'success' => true,
                'data' => $classes,
            ]);
        } catch (\ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid class or session.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching registered classes', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registered classes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch registered classes for the modal.
     */
    public function getRegisteredClasses(Request $request): JsonResponse
    {
        try {
            $registeredClasses = Subjectclass::query()
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('student_subject_register_record', 'student_subject_register_record.subjectclassid', '=', 'subjectclass.id')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->select([
                    'schoolclass.id as class_id',
                    'schoolclass.schoolclass as class_name',
                    'schoolarm.arm as arm_name',
                    'schoolsession.session as session_name',
                    'schoolterm.term as term_name',
                    DB::raw('COUNT(DISTINCT student_subject_register_record.studentId) as student_count'),
                    DB::raw('COUNT(DISTINCT subject.id) as subject_count')
                ])
                ->groupBy(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm', 'schoolsession.session', 'schoolterm.term'])
                ->whereNotNull('student_subject_register_record.studentId')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $registeredClasses
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching registered classes: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registered classes: ' . $e->getMessage()
            ], 500);
        }
    }
}
