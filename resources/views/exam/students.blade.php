@extends('layouts.master')
@section('content')

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
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="students-count">{{ $students->total() }}</span></h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-125px">SN</th>
                                            <th class="min-w-125px">Photo</th>
                                            <th class="min-w-125px">Student Name</th>
                                            <th class="min-w-125px">Admission No</th>
                                            <th class="min-w-125px">No of Questions</th>
                                            <th class="min-w-125px">Attempted</th>
                                            <th class="min-w-125px">Correct</th>
                                            <th class="min-w-125px">Missed</th>
                                            <th class="min-w-125px">Total Score</th>
                                            <th class="min-w-100px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="students-tbody">
                                        @php $i = ($students->currentPage() - 1) * $students->perPage() @endphp
                                        @forelse ($students as $student)
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
                                                        {{ $student->total_marks ?? 0 }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->attempt_status === 'in_progress')
                                                        <span class="badge bg-warning text-dark">In Progress</span>
                                                    @else
                                                        {{ $student->attempted_questions ?? 0 }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->attempt_status === 'in_progress')
                                                        <span class="badge bg-warning text-dark">In Progress</span>
                                                    @else
                                                        {{ $student->score ?? 0 }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->attempt_status === 'in_progress')
                                                        <span class="badge bg-warning text-dark">In Progress</span>
                                                    @else
                                                        {{ ($student->attempted_questions ?? 0) - ($student->score ?? 0) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->attempt_status === 'in_progress')
                                                        <span class="badge bg-info">Ongoing</span>
                                                    @else
                                                        <span class="badge bg-success">{{ $student->score ?? 0 }} / {{ $student->total_marks ?? 0 }}</span>
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
                                        @empty
                                            <tr class="empty-row">
                                                <td colspan="10" class="text-center">No students found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const deleteButtons = document.querySelectorAll('.delete-attempt');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const examId = this.dataset.examId;
            const studentId = this.dataset.studentId;
            const studentName = this.dataset.studentName;
            const deleteUrl = this.dataset.deleteUrl; // Using the pre-generated URL
            const row = this.closest('tr');
            const isInProgress = row.querySelector('.badge.bg-warning') !== null;

            const confirmMsg = isInProgress
                ? `Are you sure you want to delete ${studentName}'s ongoing exam attempt? This will stop the exam and allow a retake.`
                : `Are you sure you want to delete ${studentName}'s exam attempt? This will allow them to retake the exam.`;

            // SweetAlert confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: confirmMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Using the pre-generated URL from data attribute
                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('Delete Error Response:', text);
                                throw new Error(`Server error ${response.status}: ${text.substring(0, 200)}...`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Remove the row from the table
                            row.remove();

                            // Update the total count badge
                            updateCountBadge();

                            // Update pagination text
                            updatePaginationText();

                            // Update serial numbers
                            updateSerialNumbers();

                            // Check if table is empty
                            checkEmptyTable();

                            // Show success message
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Error deleting attempt. Please try again.',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Delete Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'An error occurred while deleting the attempt.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    });
                }
            });
        });
    });

    function updateCountBadge() {
        const badge = document.getElementById('students-count');
        if (badge) {
            let currentTotal = parseInt(badge.textContent.trim());
            if (!isNaN(currentTotal)) {
                badge.textContent = currentTotal - 1;
            }
        }
    }

    function updatePaginationText() {
        const paginationText = document.getElementById('pagination-text');
        if (paginationText) {
            const match = paginationText.textContent.match(/of (\d+) Results/);
            if (match) {
                const newTotal = parseInt(match[1]) - 1;
                paginationText.innerHTML = paginationText.innerHTML.replace(/of \d+ Results/, `of ${newTotal} Results`);
            }
        }
    }

    function updateSerialNumbers() {
        const rows = document.querySelectorAll('#students-tbody tr:not(.empty-row)');
        let i = (1 + (Math.max(0, rows.length - 15) / 15) * 15);
        rows.forEach(row => {
            const snCell = row.querySelector('.sn-number');
            if (snCell) {
                snCell.textContent = ++i;
            }
        });
    }

    function checkEmptyTable() {
        const tbody = document.getElementById('students-tbody');
        const rows = tbody.querySelectorAll('tr:not(.empty-row)');
        if (rows.length === 0) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="10" class="text-center">No students found</td></tr>';
        }
    }
});
</script>

@endsection
