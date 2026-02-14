@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.transfer-score-btn {
    transition: all 0.3s ease;
}
.transfer-score-btn:hover {
    transform: scale(1.1);
}
.transfer-score-btn.btn-success {
    background-color: #28a745;
    color: white;
}
#assessmentLoader {
    min-height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.score-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
#successMessage small {
    display: block;
    margin-top: 5px;
    color: #6c757d;
    font-size: 0.85rem;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">
                                    Students <span class="badge bg-dark-subtle text-dark ms-1" id="students-count">{{ $students->total() }}</span>
                                    <span class="ms-3 text-muted">
                                        <i class="ph-info me-1"></i>
                                        Click <i class="ph-arrow-right text-success"></i> to transfer scores to assessment sheet
                                    </span>
                                </h5>
                            </div>
                            <div>
                                <span class="text-muted me-2">Total Questions: <strong>{{ $examTotals['total_questions'] ?? 0 }}</strong></span>
                                <span class="text-muted">Total Marks: <strong>{{ number_format($examTotals['total_marks'] ?? 0, 1) }}</strong></span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($assignedClasses->count() > 1)
                            <div class="mb-3">
                                <label class="form-label">Filter by Class:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('exams.students', $exam->id) }}"
                                       class="btn btn-sm {{ !$classId ? 'btn-primary' : 'btn-outline-primary' }}">
                                        All Classes ({{ $assignedClasses->count() }})
                                    </a>
                                    @foreach($assignedClasses as $class)
                                        @php
                                            $armName = '';
                                            if ($class->arm) {
                                                if (is_numeric($class->arm)) {
                                                    $armRecord = DB::table('schoolarm')->where('id', $class->arm)->first();
                                                    $armName = $armRecord ? ' - ' . $armRecord->arm : '';
                                                } else {
                                                    $armName = ' - ' . $class->arm;
                                                }
                                            }
                                        @endphp
                                        <a href="{{ route('exams.students', ['exam' => $exam->id, 'class_id' => $class->schoolclassID]) }}"
                                           class="btn btn-sm {{ $classId == $class->schoolclassID ? 'btn-primary' : 'btn-outline-primary' }}">
                                            {{ $class->schoolclass }}{{ $armName }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-50px">SN</th>
                                            <th class="min-w-80px">Photo</th>
                                            <th class="min-w-150px">Student Name</th>
                                            <th class="min-w-100px">Admission No</th>
                                            <th class="min-w-100px">Total Ques</th>
                                            <th class="min-w-80px">Attempted</th>
                                            <th class="min-w-70px">Correct</th>
                                            <th class="min-w-80px">Incorrect</th>
                                            <th class="min-w-100px">Not Attempted</th>
                                            <th class="min-w-120px">Score (Marks)</th>
                                            <th class="min-w-180px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="students-tbody">
                                        @php
                                            $i = ($students->currentPage() - 1) * $students->perPage();
                                            $hasStudents = false;
                                        @endphp
                                        @forelse ($students as $student)
                                            @if($student)
                                                @php
                                                    $totalQuestions = $examTotals['total_questions'] ?? 0;
                                                    $totalMarks = $examTotals['total_marks'] ?? 0;
                                                    $attempted = $student->attempted_questions ?? 0;
                                                    $correct = $student->correct_count ?? 0;
                                                    $incorrect = $student->incorrect ?? 0;
                                                    $notAttempted = $totalQuestions - $attempted;
                                                    $score = $student->marks_earned ?? $student->score ?? 0;
                                                    $studentTotalMarks = $student->total_marks ?? $totalMarks;
                                                    $hasStudents = true;
                                                @endphp
                                                <tr data-student-id="{{ $student->id }}">
                                                    <td class="sn-number">{{ ++$i }}</td>
                                                    <td>
                                                        <img src="{{ $student->picture ? asset('storage/student_avatars/' . basename($student->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                             alt="{{ $student->lastname }} {{ $student->firstname }}"
                                                             class="rounded-circle avatar-xs"
                                                             onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                                    </td>
                                                    <td>{{ $student->lastname }} {{ $student->firstname }}</td>
                                                    <td>{{ $student->admissionNo }}</td>
                                                    <td>
                                                        @if($student->attempt_status === 'in_progress')
                                                            <span class="badge bg-warning text-dark">In Progress</span>
                                                        @else
                                                            {{ $totalQuestions }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($student->attempt_status === 'in_progress')
                                                            <span class="badge bg-warning text-dark">In Progress</span>
                                                        @else
                                                            {{ $attempted }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($student->attempt_status === 'in_progress')
                                                            <span class="badge bg-warning text-dark">In Progress</span>
                                                        @else
                                                            <span class="badge bg-success">{{ $correct }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($student->attempt_status === 'in_progress')
                                                            <span class="badge bg-warning text-dark">In Progress</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $incorrect }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($student->attempt_status === 'in_progress')
                                                            <span class="badge bg-warning text-dark">In Progress</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $notAttempted }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($student->attempt_status === 'in_progress')
                                                            <span class="badge bg-info">Ongoing</span>
                                                        @else
                                                            <span class="badge bg-primary">
                                                                {{ number_format($score, 1) }} / {{ number_format($studentTotalMarks, 1) }}
                                                                @if($studentTotalMarks > 0)
                                                                    ({{ number_format(($score/$studentTotalMarks)*100, 1) }}%)
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            @if($student->attempt_status === 'completed')
                                                                <a href="{{ route('exams.student.answers', [$exam->id, $student->id]) }}"
                                                                   class="btn btn-subtle-info btn-icon btn-sm"
                                                                   data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   title="View Answers">
                                                                    <i class="ph-eye"></i>
                                                                </a>
                                                                <button type="button"
                                                                        class="btn btn-subtle-success btn-icon btn-sm transfer-score-btn"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        title="Transfer to Assessment Sheet"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-student-name="{{ $student->lastname }} {{ $student->firstname }}"
                                                                        data-student-admission="{{ $student->admissionNo }}"
                                                                        data-exam-score="{{ $score }}">
                                                                    <i class="ph-arrow-right"></i>
                                                                </button>
                                                            @endif
                                                            <button type="button"
                                                                    class="btn btn-subtle-danger btn-icon btn-sm delete-attempt"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Delete Attempt (allows retake)"
                                                                    data-exam-id="{{ $exam->id }}"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-student-name="{{ $student->lastname }} {{ $student->firstname }}"
                                                                    data-delete-url="{{ route('exams.student.attempt.delete', ['exam' => $exam->id, 'student' => $student->id]) }}">
                                                                <i class="ph-trash-simple"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr class="empty-row">
                                                <td colspan="11" class="text-center">No students found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($hasStudents)
                            <div class="row mt-3 align-items-center">
                                <div class="col-sm">
                                    <div class="text-muted text-center text-sm-start" id="pagination-text">
                                        Showing <span class="fw-semibold">{{ $students->firstItem() ?? 0 }}</span> to <span class="fw-semibold">{{ $students->lastItem() ?? 0 }}</span> of <span class="fw-semibold">{{ $students->total() }}</span> Results
                                    </div>
                                </div>
                                <div class="col-sm-auto mt-3 mt-sm-0">
                                    <div class="pagination-wrap hstack gap-2 justify-content-center">
                                        {{ $students->appends(request()->query())->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Transfer Modal -->
<div class="modal fade" id="assessmentTransferModal" tabindex="-1" aria-labelledby="assessmentTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assessmentTransferModalLabel">
                    <i class="ph-arrow-circle-right me-2"></i>Transfer Exam Score to Assessment Sheet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Exam Info -->
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="ph-info fs-4 me-2"></i>
                    <div>
                        <strong>Exam:</strong> {{ $exam->title }} ({{ $exam->subject->subject ?? 'N/A' }})<br>
                        <strong>Class:</strong> {{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }} |
                        <strong>Term:</strong> {{ $term->term ?? 'N/A' }} |
                        <strong>Session:</strong> {{ $session->session ?? 'N/A' }}
                    </div>
                </div>

                <!-- Student Info -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Student Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Name:</strong> <span id="studentName"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Admission No:</strong> <span id="studentAdmission"></span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Exam Score:</strong>
                                <span class="badge bg-primary" id="examScore"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="assessmentTransferForm">
                    @csrf
                    <input type="hidden" name="exam_id" id="examId" value="{{ $exam->id }}">
                    <input type="hidden" name="student_id" id="studentId">
                    <input type="hidden" name="exam_score" id="examScoreHidden">
                    <input type="hidden" name="assessment_id" id="assessmentId" value="">
                    <!-- subjectclass_id will be added via JavaScript -->

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="assessmentSelect" class="form-label fw-bold">Select Assessment <span class="text-danger">*</span></label>
                            <select class="form-select" id="assessmentSelect" name="assessment_id" required>
                                <option value="">Loading assessments...</option>
                            </select>
                            <div class="form-text">Choose the assessment to transfer this score to</div>
                        </div>

                        <div class="col-md-12 mb-3" id="subAssessmentContainer" style="display: none;">
                            <label for="subAssessmentSelect" class="form-label fw-bold">Select Sub-Assessment</label>
                            <select class="form-select" id="subAssessmentSelect" name="sub_assessment_id">
                                <option value="">-- Select Sub-Assessment (Optional) --</option>
                            </select>
                            <div class="form-text">If this assessment has sub-components, you can select a specific one</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="maxScore" class="form-label fw-bold">Maximum Score</label>
                            <input type="number" class="form-control" id="maxScore" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="transferScore" class="form-label fw-bold">Score to Transfer <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="transferScore" name="score"
                                   step="0.1" min="0" required>
                            <div class="form-text text-danger" id="scoreValidationMsg"></div>
                        </div>

                        <input type="hidden" name="is_sub" id="isSub" value="0">
                    </div>
                </form>

                <div id="assessmentInfo" class="mt-3 p-3 bg-light rounded" style="display: none;">
                    <h6 class="fw-bold">Assessment Details:</h6>
                    <div id="assessmentDetails" class="small"></div>
                </div>

                <!-- Loading Spinner -->
                <div id="assessmentLoader" style="display: none;" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading assessments...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ph-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="transferScoreBtn">
                    <i class="ph-check me-1"></i>Transfer Score
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="transferSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-success mb-3">
                    <i class="ph-check-circle" style="font-size: 48px;"></i>
                </div>
                <h5 class="mb-3">Score Transferred Successfully!</h5>
                <div id="successMessage" class="text-muted mb-3"></div>
                <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="ph-x-circle" style="font-size: 48px;"></i>
                </div>
                <h5 class="mb-3">Transfer Failed</h5>
                <div id="errorMessage" class="text-muted mb-3"></div>
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


@endsection
