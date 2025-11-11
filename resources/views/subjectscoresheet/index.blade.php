@extends('layouts.master')
@section('content')
<style>
/* Existing styles */
@media (max-width: 768px) {
    .score-input {
        height: 48px;
        font-size: 1.1rem;
        padding: 8px;
        width: 80px;
        min-width: 80px;
        box-sizing: border-box;
        touch-action: manipulation;
        text-align: right;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .avatar-sm {
        width: 40px !important;
        height: 40px !important;
    }
    td.assessment-col, td.vetted-status {
        padding: 4px !important;
    }
    td.vetted-status {
        font-size: 0.9rem;
    }
}
/* Vetted status background colors */
.bg-success-subtle { background-color: #d4edda !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }
.bg-warning-subtle { background-color: #fff3cd !important; }
/* Assessment dropdown styles */
.assessment-dropdown .dropdown-menu {
    max-height: 200px;
    overflow-y: auto;
}
.assessment-dropdown .dropdown-item {
    white-space: nowrap;
}
/* Invalid input styling */
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
/* Column visibility modal styles */
.column-group {
    margin-bottom: 1rem;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}
.column-group h6 {
    margin-bottom: 0.5rem;
    color: #495057;
}
</style>
<!-- Main content container -->
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error!</strong> There were some problems with your input.<br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!-- Display success/status messages -->
            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?: session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <!-- Subject Information Cards -->
            @if ($broadsheets->isNotEmpty())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="d-flex flex-wrap flex-stack mb-4">
                                        <div class="d-flex flex-column flex-grow-1 pe-8">
                                            <div class="d-flex flex-wrap">
                                                <!-- Subject Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-book fs-3 text-primary me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->subject }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Subject</div>
                                                </div>
                                                <!-- Subject Code Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-code fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->subject_code }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Subject Code</div>
                                                </div>
                                                <!-- Class Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-building fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->schoolclass }} {{ $broadsheets->first()->arm }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                                </div>
                                                <!-- Term | Session Card -->
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $broadsheets->first()->term }} | {{ $broadsheets->first()->session }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Term | Session</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            
                                    <!-- Assessment Buttons Section -->
                                    @if($assessments->isNotEmpty())
                                        <div class="row g-2 mb-4">
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Assessments</h6>
                                            </div>
                                            @foreach($assessments as $assessment)
                                                <div class="col-md-3 col-sm-6">
                                                    <a href="{{ route('assessment.scoresheet', [
                                                        'schoolclassid' => session('schoolclass_id'),
                                                        'subjectclassid' => session('subjectclass_id'),
                                                        'staffid' => session('staff_id'),
                                                        'termid' => session('term_id'),
                                                        'sessionid' => session('session_id'),
                                                        'assessmentid' => $assessment->id
                                                    ]) }}" class="btn btn-outline-info w-100 text-start">
                                                        <i class="bi bi-clipboard-check me-1"></i>
                                                        {{ $assessment->name }}<br>
                                                        <small class="text-muted">({{ $assessment->max_score }})</small>
                                                        @if($assessment->subAssessments->isNotEmpty())
                                                            <small class="d-block text-muted mt-1">
                                                                <i class="bi bi-chevron-right"></i> {{ $assessment->subAssessments->count() }} sub-assessments
                                                            </small>
                                                        @else
                                                            <small class="d-block text-muted mt-1">
                                                                <i class="bi bi-info-circle"></i> No sub-assessments
                                                            </small>
                                                        @endif
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    No assessments defined for this class.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Scoresheet Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">
                                    {{ $pagetitle }}
                                    @if ($broadsheets->isNotEmpty())
                                        <span class="badge bg-info-subtle text-info ms-2" id="scoreCount">{{ $broadsheets->count() }}</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search by admission no or name..." style="min-width: 200px;" {{ $broadsheets->isEmpty() ? 'disabled' : '' }}>
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                                @if ($broadsheets->isNotEmpty())
                                    <button type="button" class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal">
                                        <i class="bi bi-eye me-1"></i> Columns
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <a href="{{ route('myresultroom.index') }}" class="btn btn-primary">
                                    <i class="ri-arrow-left-line"></i> Back
                                </a>
                                {{-- <div>
                                    @if(session('subjectclass_id'))
                                        <a href="{{ route('scoresheet.download-marks-sheet') }}" class="btn btn-warning" id="downloadMarksSheet">
                                            <i class="fas fa-file-pdf"></i> Download Marks Sheet
                                        </a>
                                    @endif
                                    <a href="{{ route('subjectscoresheet.export') }}" class="btn btn-info me-2" id="downloadExcel">
                                        <i class="ri-download-line me-1"></i> Download Excel
                                    </a>
                                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal" {{ !session('schoolclass_id') || !session('subjectclass_id') || !session('staff_id') || !session('term_id') || !session('session_id') ? 'disabled title="Please select a class, subject, term, and session first"' : '' }}>
                                        <i class="ri-upload-line me-1"></i> Bulk Excel Upload
                                    </button>
                                    @if ($broadsheets->isNotEmpty())
                                        <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#scoresModal">
                                            <i class="bi bi-table me-1"></i> View Scores
                                        </button>
                                    @endif
                                </div> --}}
                            </div>
                            <!-- Download Progress Indicator -->
                            <div class="row mt-2" id="downloadProgressContainer" style="display: none;">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="visually-hidden">Downloading...</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">Downloading Excel...</h6>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="downloadProgressBar"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- No Data Alert -->
                            <div class="alert alert-info text-center" id="noDataAlert" style="display: {{ $broadsheets->isEmpty() ? 'block' : 'none' }};">
                                <i class="ri-information-line me-2"></i>
                                No scores available for the selected subject. Please check your filters or import scores.
                            </div>
                            <!-- Scoresheet Table -->
                            <div class="table-responsive">
                                <table class="table table-centered align-middle table-nowrap mb-0" id="scoresheetTable">
                                    <thead class="table-active">
                                        <tr>
                                            <th class="col-checkbox" style="width: 50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                                    <label class="form-check-label" for="checkAll"></label>
                                                </div>
                                            </th>
                                            <th class="col-sn sort cursor-pointer" data-sort="sn">SN</th>
                                            <th class="col-admissionno sort cursor-pointer" data-sort="admissionno">Admission No</th>
                                            <th class="col-name sort cursor-pointer" data-sort="name">Name</th>
                                            @forelse ($assessments as $assessment)
                                                <th class="col-assessment-{{ $assessment->id }}">{{ $assessment->name }}<br><small>({{ $assessment->max_score }})</small></th>
                                            @empty
                                                <th colspan="4" class="col-no-assessments">No Assessments Defined</th>
                                            @endforelse
                                            <th class="col-total">Total</th>
                                            <th class="col-bf">BF</th>
                                            <th class="col-cum">Cum</th>
                                            <th class="col-num-subjects">Num Subjects</th>
                                            <th class="col-total-gp">Total GP</th>
                                            <th class="col-gpa">GPA</th>
                                            <th class="col-calc-gpa">Calc GPA</th>
                                            <th class="col-gpa-grade">GPA Grade</th>
                                            <th class="col-cgpa">CGPA</th>
                                            <th class="col-grade">Grade</th>
                                            <th class="col-position">Position</th>
                                            <th class="col-vetted">Vetted Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scoresheetTableBody" class="list form-check-all">
                                        @php $i = 0; @endphp
                                        @forelse ($broadsheets as $broadsheet)
                                            @php
                                                $initialTotal = 0;
                                                foreach ($assessments as $assessment) {
                                                    $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                                    $initialTotal += $scoreObj ? $scoreObj->score : 0;
                                                }
                                                $calculated_gpa = ($broadsheet->num_subjects ?? 1) > 0 ? number_format(($broadsheet->total_grade_points ?? 0) / ($broadsheet->num_subjects ?? 1), 1) : '0.0';
                                            @endphp
                                            <tr class="{{ $broadsheet->vettedstatus === '1' ? 'bg-success-subtle' : ($broadsheet->vettedstatus === '0' ? 'bg-danger-subtle' : 'bg-warning-subtle') }}"
                                                data-id="{{ $broadsheet->id }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="{{ $broadsheet->vettedstatus === '1' ? 'Scores vetted' : ($broadsheet->vettedstatus === '0' ? 'Scores not vetted' : 'Scores not vetted yet') }}">
                                                <td class="col-checkbox">
                                                    <div class="form-check">
                                                        <input class="form-check-input score-checkbox" type="checkbox" name="chk_child" data-id="{{ $broadsheet->id }}">
                                                        <label class="form-check-label"></label>
                                                    </div>
                                                </td>
                                                <td class="col-sn sn">{{ ++$i }}</td>
                                                <td class="col-admissionno admissionno" data-admissionno="{{ $broadsheet->admissionno }}">{{ $broadsheet->admissionno ?? '-' }}</td>
                                                <td class="col-name name" data-name="{{ ($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '') }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <img src="{{ $broadsheet->picture ? asset('storage/student_avatars/' . basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg') }}" alt="{{ ($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '') }}" class="rounded-circle w-100 student-image" data-bs-toggle="modal" data-bs-target="#imageViewModal" data-image="{{ $broadsheet->picture ? asset('storage/student_avatars/' . basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg') }}" data-picture="{{ $broadsheet->picture ?? 'none' }}" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold">{{ $broadsheet->lname ?? '' }}</span> {{ $broadsheet->fname ?? '' }} {{ $broadsheet->mname ?? '' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                @forelse ($assessments as $assessment)
                                                    @php
                                                        $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                                        $scoreValue = $scoreObj ? $scoreObj->score : '';
                                                    @endphp
                                                    <td class="col-assessment-{{ $assessment->id }} assessment-col">
                                                        <input type="number" class="form-control score-input" data-field="{{ $assessment->id }}" data-max="{{ $assessment->max_score }}" data-id="{{ $broadsheet->id }}" data-original="{{ $scoreValue }}" value="{{ $scoreValue }}" min="0" max="{{ $assessment->max_score }}" step="0.1" placeholder="">
                                                    </td>
                                                @empty
                                                    <td colspan="4" class="col-no-assessments">-</td>
                                                @endforelse
                                                <td class="col-total total-display text-center">
                                                    <span class="badge bg-primary" data-total="{{ $initialTotal }}">{{ number_format($initialTotal, 1) }}</span>
                                                </td>
                                                <td class="col-bf bf-display text-center">
                                                    <span class="badge bg-secondary">{{ $broadsheet->bf ? number_format($broadsheet->bf, 2) : '0.00' }}</span>
                                                </td>
                                                <td class="col-cum cum-display text-center">
                                                    <span class="badge bg-info">{{ $broadsheet->cum ? number_format($broadsheet->cum, 2) : '0.00' }}</span>
                                                </td>
                                                <td class="col-num-subjects num-subjects-display text-center">
                                                    <span class="badge bg-light text-dark">{{ $broadsheet->num_subjects ?? '-' }}</span>
                                                </td>
                                                <td class="col-total-gp total-gp-display text-center">
                                                    <span class="badge bg-light text-dark">{{ number_format($broadsheet->total_grade_points ?? 0, 1) }}</span>
                                                </td>
                                                <td class="col-gpa gpa-display text-center">
                                                    <span class="badge bg-warning">{{ $broadsheet->gpa ? number_format($broadsheet->gpa, 1) : '0.0' }}</span>
                                                </td>
                                                <td class="col-calc-gpa calc-gpa-display text-center">
                                                    <span class="badge bg-secondary">{{ $calculated_gpa }}</span>
                                                </td>
                                                <td class="col-gpa-grade gpa-grade-display text-center">
                                                    <span class="badge bg-success">{{ $broadsheet->gpa_grade ?? '-' }}</span>
                                                </td>
                                                <td class="col-cgpa cgpa-display text-center">
                                                    <span class="badge bg-dark">{{ $broadsheet->cgpa ? number_format($broadsheet->cgpa, 2) : '0.00' }}</span>
                                                </td>
                                                <td class="col-grade grade-display text-center">
                                                    <span class="badge bg-secondary">{{ $broadsheet->grade ?? '-' }}</span>
                                                </td>
                                                <td class="col-position position-display text-center">
                                                    <span class="badge bg-info">{{ $broadsheet->position ? $broadsheet->position . \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}</span>
                                                </td>
                                                <td class="col-vetted vetted-status text-center">
                                                    <span class="badge {{ $broadsheet->vettedstatus === '1' ? 'bg-success' : ($broadsheet->vettedstatus === '0' ? 'bg-danger' : 'bg-warning') }}">
                                                        {{ $broadsheet->vettedstatus === '1' ? 'Scores vetted' : ($broadsheet->vettedstatus === '0' ? 'Scores not vetted' : 'Scores not vetted yet') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="noDataRow">
                                                <td colspan="{{ (count($assessments) > 0 ? count($assessments) : 4) + 16 }}" class="text-center">No scores available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Enhanced Control Panel -->
                            @if ($broadsheets->isNotEmpty())
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="card-title mb-0 me-3">Bulk Actions:</h6>
                                                        <div class="btn-group me-2" role="group">
                                                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllScores">
                                                                <i class="ri-check-double-line me-1"></i> Select All
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllScores">
                                                                <i class="ri-close-line me-1"></i> Clear All
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" id="deleteSelectedScoresBtn">
                                                                <i class="ri-delete-bin-line me-1"></i> Delete Selected
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted me-3">
                                                            <i class="ri-information-line"></i> Press Ctrl+S to save quickly
                                                        </small>
                                                        <button class="btn btn-success" id="bulkUpdateScores">
                                                            <i class="ri-save-line me-1"></i> Save All Scores
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Progress Indicator for Saving Scores -->
                                <div class="row mt-2" id="progressContainer" style="display: none;">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span class="visually-hidden">Saving...</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Updating Scores...</h6>
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="saveProgressBar"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Column Visibility Modal -->
            @if ($broadsheets->isNotEmpty())
            <div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Column Visibility</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="column-group">
                                <h6>Student Info</h6>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-checkbox" data-col="col-checkbox" checked>
                                    <label class="form-check-label" for="col-checkbox">Select</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-sn" data-col="col-sn" checked>
                                    <label class="form-check-label" for="col-sn">SN</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-admissionno" data-col="col-admissionno" checked>
                                    <label class="form-check-label" for="col-admissionno">Admission No</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-name" data-col="col-name" checked>
                                    <label class="form-check-label" for="col-name">Name</label>
                                </div>
                            </div>
                            @if($assessments->isNotEmpty())
                            <div class="column-group">
                                <h6>Assessments</h6>
                                @foreach($assessments as $assessment)
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-assessment-{{ $assessment->id }}" data-col="col-assessment-{{ $assessment->id }}" checked>
                                    <label class="form-check-label" for="col-assessment-{{ $assessment->id }}">{{ $assessment->name }}</label>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            <div class="column-group">
                                <h6>Scores & Metrics</h6>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-total" data-col="col-total" checked>
                                    <label class="form-check-label" for="col-total">Total</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-bf" data-col="col-bf" checked>
                                    <label class="form-check-label" for="col-bf">BF</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-cum" data-col="col-cum" checked>
                                    <label class="form-check-label" for="col-cum">Cum</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-num-subjects" data-col="col-num-subjects" checked>
                                    <label class="form-check-label" for="col-num-subjects">Num Subjects</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-total-gp" data-col="col-total-gp" checked>
                                    <label class="form-check-label" for="col-total-gp">Total GP</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-gpa" data-col="col-gpa" checked>
                                    <label class="form-check-label" for="col-gpa">GPA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-calc-gpa" data-col="col-calc-gpa" checked>
                                    <label class="form-check-label" for="col-calc-gpa">Calc GPA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-gpa-grade" data-col="col-gpa-grade" checked>
                                    <label class="form-check-label" for="col-gpa-grade">GPA Grade</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-cgpa" data-col="col-cgpa" checked>
                                    <label class="form-check-label" for="col-cgpa">CGPA</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-grade" data-col="col-grade" checked>
                                    <label class="form-check-label" for="col-grade">Grade</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-position" data-col="col-position" checked>
                                    <label class="form-check-label" for="col-position">Position</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input col-toggle" type="checkbox" id="col-vetted" data-col="col-vetted" checked>
                                    <label class="form-check-label" for="col-vetted">Vetted Status</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- Import Modal -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Bulk Upload Scores</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-10 my-7">
                            <form action="{{ route('subjectscoresheet.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <input type="hidden" name="schoolclass_id" value="{{ session('schoolclass_id') }}">
                                <input type="hidden" name="subjectclass_id" value="{{ session('subjectclass_id') }}">
                                <input type="hidden" name="staff_id" value="{{ session('staff_id') }}">
                                <input type="hidden" name="term_id" value="{{ session('term_id') }}">
                                <input type="hidden" name="session_id" value="{{ session('session_id') }}">
                                <div class="form-group mb-6">
                                    <label class="required fw-semibold fs-6 mb-2">Excel File</label>
                                    <input type="file" name="file" class="form-control form-control-sm mb-3" accept=".xlsx,.xls" required>
                                </div>
                                <div class="form-group mb-6" id="importLoader" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm text-primary me-3" role="status">
                                            <span class="visually-hidden">Uploading...</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Uploading File...</h6>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="uploadProgressBar"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center pt-10">
                                    <button type="reset" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Discard</button>
                                    <button type="submit" class="btn btn-primary" id="importSubmit">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Scores Modal -->
            <div class="modal fade" id="scoresModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Scores Overview</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-centered align-middle table-nowrap mb-0">
                                    <thead class="table-active">
                                        <tr>
                                            <th class="col-sn">SN</th>
                                            <th class="col-admissionno">Admission No</th>
                                            <th class="col-name">Name</th>
                                            @forelse ($assessments as $assessment)
                                                <th class="col-assessment-{{ $assessment->id }}">{{ $assessment->name }}</th>
                                            @empty
                                                <th colspan="4" class="col-no-assessments">No Assessments</th>
                                            @endforelse
                                            <th class="col-total">Total</th>
                                            <th class="col-bf">BF</th>
                                            <th class="col-cum">Cum</th>
                                            <th class="col-num-subjects">Num Subjects</th>
                                            <th class="col-total-gp">Total GP</th>
                                            <th class="col-gpa">GPA</th>
                                            <th class="col-calc-gpa">Calc GPA</th>
                                            <th class="col-gpa-grade">GPA Grade</th>
                                            <th class="col-cgpa">CGPA</th>
                                            <th class="col-grade">Grade</th>
                                            <th class="col-position">Position</th>
                                            <th class="col-vetted">Vetted Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; @endphp
                                        @forelse ($broadsheets as $broadsheet)
                                            @php
                                                $modalTotal = 0;
                                                foreach ($assessments as $assessment) {
                                                    $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                                    $modalTotal += $scoreObj ? $scoreObj->score : 0;
                                                }
                                                $modal_calculated_gpa = ($broadsheet->num_subjects ?? 1) > 0 ? number_format(($broadsheet->total_grade_points ?? 0) / ($broadsheet->num_subjects ?? 1), 1) : '0.0';
                                            @endphp
                                            <tr class="{{ $broadsheet->vettedstatus === '1' ? 'bg-success-subtle' : ($broadsheet->vettedstatus === '0' ? 'bg-danger-subtle' : 'bg-warning-subtle') }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="{{ $broadsheet->vettedstatus === '1' ? 'Scores vetted' : ($broadsheet->vettedstatus === '0' ? 'Scores not vetted' : 'Scores not vetted yet') }}">
                                                <td class="col-sn">{{ ++$i }}</td>
                                                <td class="col-admissionno admissionno">{{ $broadsheet->admissionno ?? '-' }}</td>
                                                <td class="col-name name">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <img src="{{ $broadsheet->picture ? asset('storage/student_avatars/' . basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg') }}" alt="{{ ($broadsheet->lname ?? '') . ' ' . ($broadsheet->fname ?? '') . ' ' . ($broadsheet->mname ?? '') }}" class="rounded-circle w-100 student-image" data-bs-toggle="modal" data-bs-target="#imageViewModal" data-image="{{ $broadsheet->picture ? asset('storage/student_avatars/' . basename($broadsheet->picture)) : asset('storage/student_avatars/unnamed.jpg') }}" data-picture="{{ $broadsheet->picture ?? 'none' }}" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold">{{ $broadsheet->lname ?? '' }}</span> {{ $broadsheet->fname ?? '' }} {{ $broadsheet->mname ?? '' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                @forelse ($assessments as $assessment)
                                                    @php
                                                        $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                                                    @endphp
                                                    <td class="col-assessment-{{ $assessment->id }}">{{ $scoreObj ? number_format($scoreObj->score, 1) : '0.0' }}</td>
                                                @empty
                                                    <td colspan="4" class="col-no-assessments">-</td>
                                                @endforelse
                                                <td class="col-total">{{ number_format($modalTotal, 1) }}</td>
                                                <td class="col-bf">{{ $broadsheet->bf ? number_format($broadsheet->bf, 2) : '0.00' }}</td>
                                                <td class="col-cum">{{ $broadsheet->cum ? number_format($broadsheet->cum, 2) : '0.00' }}</td>
                                                <td class="col-num-subjects">{{ $broadsheet->num_subjects ?? '-' }}</td>
                                                <td class="col-total-gp">{{ number_format($broadsheet->total_grade_points ?? 0, 1) }}</td>
                                                <td class="col-gpa">{{ $broadsheet->gpa ? number_format($broadsheet->gpa, 1) : '0.0' }}</td>
                                                <td class="col-calc-gpa">{{ $modal_calculated_gpa }}</td>
                                                <td class="col-gpa-grade">{{ $broadsheet->gpa_grade ?? '-' }}</td>
                                                <td class="col-cgpa">{{ $broadsheet->cgpa ? number_format($broadsheet->cgpa, 2) : '0.00' }}</td>
                                                <td class="col-grade">{{ $broadsheet->grade ?? '-' }}</td>
                                                <td class="col-position">
                                                    {{ $broadsheet->position ? $broadsheet->position . \App\Helpers\OrdinalHelper::getOrdinalSuffix($broadsheet->position) : '-' }}
                                                </td>
                                                <td class="col-vetted vetted-status text-center">
                                                    <span class="badge {{ $broadsheet->vettedstatus === '1' ? 'bg-success' : ($broadsheet->vettedstatus === '0' ? 'bg-danger' : 'bg-warning') }}">
                                                        {{ $broadsheet->vettedstatus === '1' ? 'Scores vetted' : ($broadsheet->vettedstatus === '0' ? 'Scores not vetted' : 'Scores not vetted yet') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ (count($assessments) > 0 ? count($assessments) : 4) + 15 }}" class="text-center col-no-assessments">No scores available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Image View Modal -->
            <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Student Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="enlargedImage" src="" alt="Student Image" class="img-fluid" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Ensure CSRF token is available
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
    // Global variables
    console.log('Raw broadsheets before normalization:', @json($broadsheets));
    window.broadsheets = @json($broadsheets);
    window.assessments = @json($assessments);
    window.term_id = {{ session('term_id') }};
    window.session_id = {{ session('session_id') }};
    window.subjectclass_id = {{ session('subjectclass_id') }};
    window.schoolclass_id = {{ session('schoolclass_id') }};
    window.staff_id = {{ session('staff_id') }};
    window.is_senior = {{ $is_senior ? 'true' : 'false' }};
    window.routes = {
        results: '{{ route('subjectscoresheet.results') }}',
        bulkUpdate: '{{ route('subjectscoresheet.bulk-update') }}',
        singleUpdate: '{{ route('subjectscoresheet.single-update') }}',
        destroy: '{{ route('subjectscoresheet.destroy', ['id' => '__ID__']) }}',
        import: '{{ route('subjectscoresheet.import') }}',
        export: '{{ route('subjectscoresheet.export') }}',
        downloadMarksSheet: '{{ route('scoresheet.download-marks-sheet') }}',
        gradePreview: '{{ route('subjectscoresheet.grade-preview') }}'
    };
    // Initialize everything on DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        // Column visibility toggle
        document.querySelectorAll('.col-toggle').forEach(cb => {
            cb.addEventListener('change', function() {
                const colClass = this.dataset.col;
                const elements = document.querySelectorAll(`#scoresheetTable th.${colClass}, #scoresheetTable td.${colClass}`);
                elements.forEach(el => {
                    el.style.display = this.checked ? '' : 'none';
                });
                // Also apply to scores modal if open, but for simplicity, reload or apply separately
                const modalTable = document.querySelector('#scoresModal table');
                if (modalTable) {
                    const modalElements = modalTable.querySelectorAll(`th.${colClass}, td.${colClass}`);
                    modalElements.forEach(el => {
                        el.style.display = this.checked ? '' : 'none';
                    });
                }
            });
        });
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        let tableRows = document.querySelectorAll('#scoresheetTableBody tr[data-id]');
        const noDataAlert = document.getElementById('noDataAlert');
        const scoreCount = document.getElementById('scoreCount');
        function updateSearchAndCount() {
            const searchQuery = searchInput.value.trim().toLowerCase();
            let visibleRows = 0;
            tableRows.forEach(row => {
                const admissionNo = row.querySelector('.col-admissionno').dataset.admissionno.toLowerCase();
                const name = row.querySelector('.col-name').dataset.name.toLowerCase();
                if (searchQuery === '' || admissionNo.includes(searchQuery) || name.includes(searchQuery)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (noDataAlert) noDataAlert.style.display = visibleRows === 0 ? 'block' : 'none';
            if (scoreCount) scoreCount.textContent = visibleRows;
        }
        if (searchInput) {
            searchInput.addEventListener('input', updateSearchAndCount);
        }
        if (clearSearch) {
            clearSearch.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                tableRows.forEach(row => row.style.display = '');
                if (noDataAlert) noDataAlert.style.display = tableRows.length === 0 ? 'block' : 'none';
                if (scoreCount) scoreCount.textContent = tableRows.length;
            });
        }
        // Checkbox functionality
        const checkAll = document.getElementById('checkAll');
        const scoreCheckboxes = document.querySelectorAll('.score-checkbox');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                scoreCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        }
        scoreCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const checkedCount = document.querySelectorAll('.score-checkbox:checked').length;
                if (checkAll) {
                    checkAll.checked = checkedCount === scoreCheckboxes.length && scoreCheckboxes.length > 0;
                    checkAll.indeterminate = checkedCount > 0 && checkedCount < scoreCheckboxes.length;
                }
            });
        });
        // Select All button
        const selectAllBtn = document.getElementById('selectAllScores');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                if (checkAll) checkAll.checked = true;
                scoreCheckboxes.forEach(cb => cb.checked = true);
            });
        }
        // Clear All button
        const clearAllBtn = document.getElementById('clearAllScores');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                if (checkAll) checkAll.checked = false;
                scoreCheckboxes.forEach(cb => cb.checked = false);
            });
        }
        // Dynamic total update: sum of all assessment scores (raw)
        function updateRowTotal(row) {
            let sum = 0;
            row.querySelectorAll('.score-input').forEach(inp => {
                const val = parseFloat(inp.value) || 0;
                sum += val;
            });
            const totalSpan = row.querySelector('.col-total span');
            if (totalSpan) {
                totalSpan.textContent = number_format(sum, 1);
                totalSpan.dataset.total = sum;
            }
        }
        // Validation check for a single input
        function validateInput(input) {
            const max = parseFloat(input.dataset.max) || 0;
            const val = parseFloat(input.value) || 0;
            if (val > max) {
                input.classList.add('is-invalid');
                input.title = `Maximum score is ${max}. Please correct before saving.`;
                return false;
            } else {
                input.classList.remove('is-invalid');
                input.title = '';
                return true;
            }
        }
        // Validation check for a row
        function validateRow(row) {
            let isValid = true;
            row.querySelectorAll('.score-input').forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });
            return isValid;
        }
        // Global validation check for all rows
        function validateAllRows() {
            let isValid = true;
            let invalidCount = 0;
            document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
                row.querySelectorAll('.score-input').forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                        invalidCount++;
                    }
                });
            });
            return { isValid, invalidCount };
        }
        // Event listeners for score inputs
        document.querySelectorAll('.score-input').forEach(input => {
            input.addEventListener('input', function () {
                validateInput(this);
                updateRowTotal(this.closest('tr'));
            });
            input.addEventListener('blur', function () {
                validateInput(this);
                updateRowTotal(this.closest('tr'));
                // Check if value changed and save if so
                const original = parseFloat(this.dataset.original) || 0;
                const current = parseFloat(this.value) || 0;
                if (Math.abs(current - original) > 0.01) { // Tolerance for floating point
                    saveIndividualScore(this);
                    this.dataset.original = this.value;
                }
            });
            // Quick save on Enter
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const row = this.closest('tr');
                    if (!validateRow(row)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Score!',
                            text: 'Please correct scores exceeding the maximum before saving.',
                        });
                        return;
                    }
                    updateRowTotal(row);
                    saveIndividualScore(this);
                }
            });
        });
        // Updated Individual score save (sends current row total)
        function saveIndividualScore(input) {
            const rowId = input.dataset.id;
            const fieldId = parseInt(input.dataset.field);
            const score = parseFloat(input.value) || 0;
            const row = input.closest('tr');
         
            // Get current total from row
            const totalSpan = row.querySelector('.col-total span');
            const currentTotal = totalSpan ? parseFloat(totalSpan.dataset.total) || 0 : 0;
            fetch(window.routes.singleUpdate, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    broadsheet_id: rowId,
                    assessment_id: fieldId,
                    score: score,
                    is_sub: false,
                    sub_assessment_id: null,
                    raw_total: currentTotal, // Send the calculated total
                    term_id: window.term_id,
                    session_id: window.session_id,
                    subjectclass_id: window.subjectclass_id,
                    schoolclass_id: window.schoolclass_id,
                    staff_id: window.staff_id
                })
            }).then(response => response.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Unknown error saving score.',
                    });
                } else {
                    // Update row displays
                    const row = document.querySelector(`tr[data-id="${rowId}"]`);
                    if (row) {
                        const cumSpan = row.querySelector('.col-cum span');
                        if (cumSpan) cumSpan.textContent = number_format(data.cum, 2);
                        const gpaSpan = row.querySelector('.col-gpa span');
                        if (gpaSpan) gpaSpan.textContent = number_format(data.gpa, 1);
                        const gpaGradeSpan = row.querySelector('.col-gpa-grade span');
                        if (gpaGradeSpan) gpaGradeSpan.textContent = data.gpa_grade;
                        const cgpaSpan = row.querySelector('.col-cgpa span');
                        if (cgpaSpan) cgpaSpan.textContent = number_format(data.cgpa, 2);
                        const gradeSpan = row.querySelector('.col-grade span');
                        if (gradeSpan) gradeSpan.textContent = data.grade;
                        const bfSpan = row.querySelector('.col-bf span');
                        if (bfSpan) bfSpan.textContent = number_format(data.bf, 2);
                        const numSubjectsSpan = row.querySelector('.col-num-subjects span');
                        if (numSubjectsSpan) numSubjectsSpan.textContent = data.num_subjects;
                        const totalGpSpan = row.querySelector('.col-total-gp span');
                        if (totalGpSpan) totalGpSpan.textContent = number_format(data.total_grade_points, 1);
                        // Recalculate and update Calc GPA
                        const calcGpaSpan = row.querySelector('.col-calc-gpa span');
                        if (calcGpaSpan && data.num_subjects > 0) {
                            calcGpaSpan.textContent = number_format(data.total_grade_points / data.num_subjects, 1);
                        }
                    }
                    console.log('Score saved:', data.data);
                }
            }).catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error!',
                    text: 'An error occurred while saving the score.',
                });
            });
        }
        // Bulk update scores
        const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
        if (bulkUpdateBtn) {
            bulkUpdateBtn.addEventListener('click', function () {
                const validation = validateAllRows();
                if (!validation.isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Scores!',
                        text: `${validation.invalidCount} score(s) exceed the maximum. Please correct them before saving.`,
                    });
                    return;
                }
                // Update all totals before saving
                document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
                    updateRowTotal(row);
                });
                const scores = [];
                document.querySelectorAll('#scoresheetTableBody tr[data-id]').forEach(row => {
                    const broadsheetId = row.dataset.id;
                    const assessments = {};
                    row.querySelectorAll('.score-input').forEach(input => {
                        assessments[input.dataset.field] = parseFloat(input.value) || 0;
                    });
                 
                    // Get row total
                    const totalSpan = row.querySelector('.col-total span');
                    const rowTotal = totalSpan ? parseFloat(totalSpan.dataset.total) || 0 : 0;
                 
                    scores.push({
                        id: broadsheetId,
                        assessments: assessments,
                        raw_total: rowTotal // Send the calculated total
                    });
                });
                if (scores.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Scores!',
                        text: 'No scores to update.',
                    });
                    return;
                }
                const progressContainer = document.getElementById('progressContainer');
                if (progressContainer) progressContainer.style.display = 'block';
                const progressBar = document.getElementById('saveProgressBar');
                let width = 0;
                const interval = setInterval(() => {
                    width += 10;
                    if (progressBar) progressBar.style.width = width + '%';
                    if (width >= 100) {
                        clearInterval(interval);
                    }
                }, 200);
                fetch(window.routes.bulkUpdate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        scores: scores,
                        term_id: window.term_id,
                        session_id: window.session_id,
                        subjectclass_id: window.subjectclass_id,
                        staff_id: window.staff_id,
                        schoolclass_id: window.schoolclass_id,
                        is_sub: false
                    })
                }).then(response => response.json())
                .then(data => {
                    clearInterval(interval);
                    if (progressContainer) progressContainer.style.display = 'none';
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Scores updated successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Unknown error updating scores.',
                        });
                    }
                }).catch(error => {
                    clearInterval(interval);
                    if (progressContainer) progressContainer.style.display = 'none';
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error!',
                        text: 'An error occurred while updating scores.',
                    });
                });
            });
        }
        // Delete selected
        const deleteBtn = document.getElementById('deleteSelectedScoresBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                const ids = Array.from(document.querySelectorAll('.score-checkbox:checked')).map(cb => cb.dataset.id);
                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection!',
                        text: 'Please select items to delete.',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You want to delete the selected scores? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const progressContainer = document.getElementById('progressContainer');
                        if (progressContainer) progressContainer.style.display = 'block';
                        Promise.all(ids.map(id =>
                            fetch(window.routes.destroy.replace('__ID__', id), {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            }).then(res => res.json())
                        )).then(results => {
                            if (progressContainer) progressContainer.style.display = 'none';
                            let deletedCount = 0;
                            results.forEach((result, index) => {
                                if (result.success) {
                                    const row = document.querySelector(`tr[data-id="${ids[index]}"]`);
                                    if (row) row.remove();
                                    deletedCount++;
                                }
                            });
                            if (deletedCount > 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: `${deletedCount} score(s) deleted successfully.`,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                tableRows = document.querySelectorAll('#scoresheetTableBody tr[data-id]');
                                updateSearchAndCount();
                                if (tableRows.length === 0) {
                                    location.reload();
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No Deletion!',
                                    text: 'No scores were deleted.',
                                });
                            }
                        }).catch(error => {
                            if (progressContainer) progressContainer.style.display = 'none';
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Network Error!',
                                text: 'An error occurred while deleting scores.',
                            });
                        });
                    }
                });
            });
        }
        // Import form handling
        const importSubmit = document.getElementById('importSubmit');
        if (importSubmit) {
            importSubmit.addEventListener('click', function (e) {
                e.preventDefault();
                const importForm = document.getElementById('importForm');
                const fileInput = importForm.querySelector('input[type="file"]');
                if (!fileInput.files.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No File!',
                        text: 'Please select a file.',
                    });
                    return;
                }
                const importLoader = document.getElementById('importLoader');
                if (importLoader) importLoader.style.display = 'block';
                const uploadProgressBar = document.getElementById('uploadProgressBar');
                let width = 0;
                const interval = setInterval(() => {
                    width += 20;
                    if (uploadProgressBar) uploadProgressBar.style.width = width + '%';
                    if (width >= 100) {
                        clearInterval(interval);
                    }
                }, 300);
                setTimeout(() => {
                    importForm.submit();
                }, 1000);
            });
        }
        // Image modal handler
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('student-image')) {
                const imgSrc = e.target.dataset.image;
                const enlargedImage = document.getElementById('enlargedImage');
                if (enlargedImage) enlargedImage.src = imgSrc;
            }
        });
        // Sorting functionality (only on visible columns)
        const sortHeaders = document.querySelectorAll('th.sort');
        sortHeaders.forEach(header => {
            header.addEventListener('click', function () {
                if (this.style.display === 'none') return; // Skip if hidden
                const sortBy = this.dataset.sort;
                const tbody = document.getElementById('scoresheetTableBody');
                let rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
                const noDataRow = document.getElementById('noDataRow');
                if (noDataRow) noDataRow.style.display = 'none';
                rows.sort((a, b) => {
                    let aVal, bVal;
                    if (sortBy === 'sn') {
                        aVal = parseInt(a.querySelector('.col-sn').textContent) || 0;
                        bVal = parseInt(b.querySelector('.col-sn').textContent) || 0;
                        return aVal - bVal;
                    } else if (sortBy === 'admissionno') {
                        aVal = a.querySelector('.col-admissionno').textContent.trim();
                        bVal = b.querySelector('.col-admissionno').textContent.trim();
                        return aVal.localeCompare(bVal, undefined, { numeric: true });
                    } else if (sortBy === 'name') {
                        aVal = a.querySelector('.col-name').dataset.name.toLowerCase();
                        bVal = b.querySelector('.col-name').dataset.name.toLowerCase();
                        return aVal.localeCompare(bVal);
                    } else if (sortBy === 'total') {
                        aVal = parseFloat(a.querySelector('.col-total').dataset.total) || 0;
                        bVal = parseFloat(b.querySelector('.col-total').dataset.total) || 0;
                        return bVal - aVal; // Descending for total
                    }
                    return 0;
                });
                // Renumber SN
                rows.forEach((row, index) => {
                    row.querySelector('.col-sn').textContent = index + 1;
                });
                rows.forEach(row => tbody.appendChild(row));
                tableRows = rows; // Update reference
                updateSearchAndCount();
            });
        });
    });
    // Helper function for number formatting (since PHP number_format isn't in JS)
    function number_format(number, decimals) {
        return parseFloat(number).toFixed(decimals || 1);
    }
    // Keyboard shortcut for save all
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const bulkUpdateBtn = document.getElementById('bulkUpdateScores');
            if (bulkUpdateBtn) bulkUpdateBtn.click();
        }
    });
    // Include SweetAlert2 if not already in layout
    if (typeof Swal === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.head.appendChild(script);
    }
</script>
@endsection