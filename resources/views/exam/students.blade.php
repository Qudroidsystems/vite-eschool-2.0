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
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1">{{ $students->total() }}</span></h5>
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
                                    <tbody class="fw-semibold text-gray-600">
                                        @php $i = ($students->currentPage() - 1) * $students->perPage() @endphp
                                        @forelse ($students as $student)
                                            <tr data-student-id="{{ $student->id }}">
                                                <td>{{ ++$i }}</td>
                                                <td>
                                                    <img src="{{ $student->picture ? asset('storage/student_avatars/' . basename($student->picture)) : asset('storage/student_avatars/unnamed.jpg') }}"
                                                         alt="{{ $student->lastname }} {{ $student->firstname }}"
                                                         class="rounded-circle avatar-xs"
                                                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                                </td>
                                                <td>{{ $student->lastname }} {{ $student->firstname }}</td>
                                                <td>{{ $student->admissionNo }}</td>
                                                <td>{{ $student->total_marks }}</td>
                                                <td>{{ $student->attempted_questions }}</td>
                                                <td>{{ $student->score }}</td>
                                                <td>{{ $student->attempted_questions - $student->score }}</td>
                                                <td>
                                                    <span class="badge bg-success">{{ $student->score }} / {{ $student->total_marks }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('exams.student.answers', [$exam->id, $student->id]) }}" 
                                                           class="btn btn-subtle-info btn-icon btn-sm" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="View Answers">
                                                            <i class="ph-eye"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-subtle-danger btn-icon btn-sm delete-attempt" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="top" 
                                                                title="Delete Attempt (allows retake)"
                                                                data-exam-id="{{ $exam->id }}" 
                                                                data-student-id="{{ $student->id }}"
                                                                data-student-name="{{ $student->lastname }} {{ $student->firstname }}">
                                                            <i class="ph-trash-simple"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No students found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-3 align-items-center">
                                <div class="col-sm">
                                    <div class="text-muted text-center text-sm-start">
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
            e.stopPropagation(); // Prevent btn-group interference
            
            const examId = this.dataset.examId;
            const studentId = this.dataset.studentId;
            const studentName = this.dataset.studentName;
            const row = this.closest('tr');
            
            if (confirm(`Are you sure you want to delete ${studentName}'s exam attempt? This will allow them to retake the exam.`)) {
                fetch(`/exams/${examId}/students/${studentId}/attempt/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table
                        row.remove();
                        
                        // Update the total count badge if necessary
                        const badge = document.querySelector('.badge');
                        if (badge) {
                            const currentTotal = parseInt(badge.textContent);
                            badge.textContent = currentTotal - 1;
                        }
                        
                        // Update pagination text if needed
                        updatePaginationText();
                        
                        // Show success message (assuming you have a toast or alert system)
                        alert(data.message); // Replace with your preferred notification system
                    } else {
                        alert('Error deleting attempt. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the attempt.');
                });
            }
        });
    });
    
    function updatePaginationText() {
        const totalText = document.querySelector('.text-muted');
        if (totalText) {
            const match = totalText.textContent.match(/of (\d+) Results/);
            if (match) {
                const newTotal = parseInt(match[1]) - 1;
                totalText.innerHTML = totalText.innerHTML.replace(/of \d+ Results/, `of ${newTotal} Results`);
            }
        }
    }
});
</script>

@endsection