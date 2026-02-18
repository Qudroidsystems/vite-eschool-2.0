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
.score-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('exams.transfer.subjects') }}">Transfer Subjects</a></li>
                                <li class="breadcrumb-item active">Transfer Scores</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Info Cards -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="d-flex flex-wrap flex-stack mb-2">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap">
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-book fs-3 text-primary me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $subjectclass->subject->subject }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Subject</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-code fs-3 text-success me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $subjectclass->subject->subject_code }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Subject Code</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-building fs-3 text-success me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $schoolclass->schoolclass }} {{ $schoolclass->arm ?? '' }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                    <div class="fs-2 fw-bold text-success">{{ $subjectclass->term->term ?? 'Term' }} | {{ $subjectclass->session->session ?? 'Session' }}</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Term | Session</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">
                                    Students Who Attempted Exam
                                    <span class="badge bg-info-subtle text-info ms-2" id="studentsCount">{{ $students->count() }}</span>
                                </h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search students..." style="min-width: 200px;">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($students->isEmpty())
                                <div class="alert alert-info text-center">
                                    <i class="ri-information-line me-2"></i>
                                    No students have attempted exams for this subject.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0">
                                        <thead class="table-active">
                                            <tr>
                                                <th>SN</th>
                                                <th>Photo</th>
                                                <th>Student Name</th>
                                                <th>Admission No</th>
                                                <th>Exam Score</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentsTableBody">
                                            @foreach($students as $index => $student)
                                                <tr data-student-id="{{ $student->id }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <img src="{{ $student->picture ? asset('storage/student_avatars/' . basename($student->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                             alt="{{ $student->lastname }} {{ $student->firstname }}"
                                                             class="rounded-circle avatar-xs"
                                                             onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                                    </td>
                                                    <td>{{ $student->lastname }} {{ $student->firstname }}</td>
                                                    <td>{{ $student->admissionNo }}</td>
                                                    <td>
                                                        <span class="badge bg-primary">{{ number_format($student->score ?? 0, 1) }}</span>
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-success btn-sm transfer-score-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#assessmentTransferModal"
                                                                data-student-id="{{ $student->id }}"
                                                                data-student-name="{{ $student->lastname }} {{ $student->firstname }}"
                                                                data-student-admission="{{ $student->admissionNo }}"
                                                                data-exam-score="{{ $student->score ?? 0 }}">
                                                            <i class="ph-arrow-right me-1"></i> Transfer to Assessment
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Transfer Modal (same as before) -->
@include('exam.partials.transfer-modal')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const tableRows = document.querySelectorAll('#studentsTableBody tr');
    const studentsCount = document.getElementById('studentsCount');

    function updateSearch() {
        const searchQuery = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const name = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const admission = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';

            if (searchQuery === '' || name.includes(searchQuery) || admission.includes(searchQuery)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (studentsCount) {
            studentsCount.textContent = visibleCount;
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', updateSearch);
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            updateSearch();
        });
    }

    // Transfer button click handler
    document.querySelectorAll('.transfer-score-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            const studentName = this.dataset.studentName;
            const studentAdmission = this.dataset.studentAdmission;
            const examScore = parseFloat(this.dataset.examScore) || 0;

            document.getElementById('studentId').value = studentId;
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('studentAdmission').textContent = studentAdmission;
            document.getElementById('examScore').textContent = examScore.toFixed(1) + ' marks';
            document.getElementById('examScoreHidden').value = examScore;
            document.getElementById('transferScore').value = examScore.toFixed(1);

            // Load assessments
            loadAssessments('{{ $subjectclassid }}', '{{ $termid }}', '{{ $sessionid }}');
        });
    });

    // Load assessments function
    function loadAssessments(subjectclassId, termId, sessionId) {
        const assessmentSelect = document.getElementById('assessmentSelect');
        const assessmentLoader = document.getElementById('assessmentLoader');

        assessmentLoader.style.display = 'block';
        assessmentSelect.style.display = 'none';

        // Get assessments for this subject class
        fetch(`/exams/assessments/for-subject/${subjectclassId}/${termId}/${sessionId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            assessmentLoader.style.display = 'none';
            assessmentSelect.style.display = 'block';

            if (data.success) {
                window.assessments = data.assessments;
                window.subjectclass_id = data.subjectclass_id;

                let options = '<option value="">Select an assessment</option>';
                data.assessments.forEach(assessment => {
                    const hasSub = assessment.sub_assessments && assessment.sub_assessments.length > 0;
                    options += `<option value="${assessment.id}"
                                     data-max="${assessment.max_score}"
                                     data-has-sub="${hasSub}">
                                    ${assessment.name} (Max: ${assessment.max_score})
                                </option>`;
                });
                assessmentSelect.innerHTML = options;
            } else {
                assessmentSelect.innerHTML = '<option value="">Error loading assessments</option>';
                Swal.fire('Error', 'Failed to load assessments', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            assessmentLoader.style.display = 'none';
            assessmentSelect.style.display = 'block';
            assessmentSelect.innerHTML = '<option value="">Error loading assessments</option>';
        });
    }
});
</script>
@endsection
