@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Exams Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <div id="alert-container"></div>

            <div id="examsList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search exams">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Exams <span class="badge bg-dark-subtle text-dark ms-1">{{ $exams->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        @can('Delete exam')
                                            <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @endcan
                                        @can('Create exam')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addExamModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Create New Exam</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_exams_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-125px">SN</th>
                                                <th class="min-w-125px">Title</th>
                                                <th class="min-w-125px">Description</th>
                                                <th class="min-w-125px">Duration</th>
                                                <th class="min-w-125px">Start Time</th>
                                                <th class="min-w-125px">End Time</th>
                                                <th class="min-w-125px">Class</th>
                                                <th class="min-w-125px">Questions</th>
                                                <th class="min-w-100px">View Students</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = ($exams->currentPage() - 1) * $exams->perPage() @endphp
                                            @forelse ($exams as $exam)
                                                @if($exam->id)
                                                <tr>
                                                    <td class="id" data-id="{{ $exam->id }}">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="title">{{ $exam->title }}</td>
                                                    <td class="description">{{ Str::limit($exam->description ?? '', 50) }}</td>
                                                    <td class="duration">{{ $exam->duration }} mins</td>
                                                    <td class="start_time">{{ $exam->start_time->format('M d, Y h:i A') }}</td>
                                                    <td class="end_time">{{ $exam->end_time->format('M d, Y h:i A') }}</td>
                                                    <td class="class">
                                                        {{ $exam->schoolclass->schoolclass ?? 'N/A' }}
                                                        @if(isset($exam->schoolclass->arm))
                                                            ({{ $exam->schoolclass->arm }})
                                                        @endif
                                                    </td>
                                                    <td class="questions">
                                                        <a href="{{ route('questions.index', $exam->id) }}" class="btn btn-subtle-primary btn-icon btn-sm">View Questions</a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('exams.students', $exam->id) }}" class="btn btn-subtle-info btn-icon btn-sm"><i class="ph-users"></i></a>
                                                    </td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update exam')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-exam-btn" data-id="{{ $exam->id }}"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete exam')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm delete-exam-btn" data-id="{{ $exam->id }}"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="noresult text-center py-4">No exams found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $exams->firstItem() ?? 0 }}</span> to <span class="fw-semibold">{{ $exams->lastItem() ?? 0 }}</span> of <span class="fw-semibold">{{ $exams->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                                            @if($exams->onFirstPage())
                                                <span class="page-item pagination-prev disabled">
                                                    <i class="mdi mdi-chevron-left align-middle"></i>
                                                </span>
                                            @else
                                                <a class="page-item pagination-prev" href="{{ $exams->previousPageUrl() }}">
                                                    <i class="mdi mdi-chevron-left align-middle"></i>
                                                </a>
                                            @endif

                                            <ul class="pagination listjs-pagination mb-0">
                                                @foreach ($exams->links()->elements[0] as $page => $url)
                                                    <li class="page-item {{ $exams->currentPage() == $page ? 'active' : '' }}">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            @if($exams->hasMorePages())
                                                <a class="page-item pagination-next" href="{{ $exams->nextPageUrl() }}">
                                                    <i class="mdi mdi-chevron-right align-middle"></i>
                                                </a>
                                            @else
                                                <span class="page-item pagination-next disabled">
                                                    <i class="mdi mdi-chevron-right align-middle"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Exam Modal -->
            @can('Create exam')
            <div id="addExamModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="add-exam-form" autocomplete="off">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="staffId" value="{{ Auth::user()->id }}" required>
                                <div class="mb-3">
                                    <label class="form-label required">Exam Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter exam title..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Enter exam description..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Duration (minutes)</label>
                                    <input type="number" name="duration" class="form-control" placeholder="Enter duration in minutes..." required min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Start Time</label>
                                    <input type="datetime-local" name="start_time" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">End Time</label>
                                    <input type="datetime-local" name="end_time" class="form-control" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Select Term</label>
                                        <select name="termid" id="addTerm" class="form-control" required>
                                            <option value="" selected>Select Term</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Select Session</label>
                                        <select name="session" id="addSession" class="form-control" required>
                                            <option value="" selected>Select Session</option>
                                            @foreach ($sessions as $schoolsession)
                                                <option value="{{ $schoolsession->id }}">{{ $schoolsession->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Select Subject</label>
                                    <select name="subject_id" id="addSubject" class="form-control" required>
                                        <option value="" selected>Select Subject</option>
                                        @foreach ($mysubjects as $subject)
                                            <option value="{{ $subject->id }}"
                                                data-termid="{{ $subject->termid }}"
                                                data-sessionid="{{ $subject->sessionid }}">
                                                {{ $subject->subject }} ({{ $subject->subjectcode }}) - {{ $subject->term }} {{ $subject->session }} - {{ $subject->schoolclass }} {{ $subject->arm ? '(' . $subject->arm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Select Classes</label>
                                    <div id="addClassContainer" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                        <p class="text-muted text-center mb-0">Select a subject first...</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="publishStatus">
                                        <label class="form-check-label" for="publishStatus">Publish exam immediately</label>
                                    </div>
                                    <div class="text-muted fs-7 mt-1">If not checked, the exam will be saved as a draft.</div>
                                </div>
                                <div class="alert alert-danger d-none" id="add-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Edit Exam Modal -->
            @can('Update exam')
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="edit-exam-form" autocomplete="off">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit-id-field" name="id">
                            <div class="modal-body">
                                <input type="hidden" name="staffId" value="{{ Auth::user()->id }}">
                                <div class="mb-3">
                                    <label class="form-label required">Exam Title</label>
                                    <input type="text" name="title" id="edit-title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Duration (minutes)</label>
                                    <input type="number" name="duration" id="edit-duration" class="form-control" required min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Start Time</label>
                                    <input type="datetime-local" name="start_time" id="edit-start_time" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">End Time</label>
                                    <input type="datetime-local" name="end_time" id="edit-end_time" class="form-control" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Select Term</label>
                                        <select name="termid" id="edit-termid" class="form-control" required>
                                            <option value="" selected>Select Term</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Select Session</label>
                                        <select name="session" id="edit-session" class="form-control" required>
                                            <option value="" selected>Select Session</option>
                                            @foreach ($sessions as $schoolsession)
                                                <option value="{{ $schoolsession->id }}">{{ $schoolsession->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Select Subject</label>
                                    <select name="subject_id" id="edit-subject_id" class="form-control" required>
                                        <option value="" selected>Select Subject</option>
                                        @foreach ($mysubjects as $subject)
                                            <option value="{{ $subject->id }}"
                                                data-termid="{{ $subject->termid }}"
                                                data-sessionid="{{ $subject->sessionid }}">
                                                {{ $subject->subject }} ({{ $subject->subjectcode }}) - {{ $subject->term }} {{ $subject->session }} - {{ $subject->schoolclass }} {{ $subject->arm ? '(' . $subject->arm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Assigned Class</label>
                                    <div id="editClassContainer" class="border rounded p-3 bg-light">
                                        <p class="text-muted text-center mb-0">Loading class information...</p>
                                    </div>
                                    <input type="hidden" id="edit-schoolclass_id" name="schoolclass_id">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="edit-publishStatus" value="1">
                                        <label class="form-check-label" for="edit-publishStatus">Publish exam immediately</label>
                                    </div>
                                    <div class="text-muted fs-7 mt-1">If not checked, the exam will be saved as a draft.</div>
                                </div>
                                <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        <!-- End Page-content -->
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
// Define csrfToken in global scope
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

console.log('CSRF Token:', csrfToken);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal filtering
    initModalFiltering();

    // Edit button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-exam-btn')) {
            e.preventDefault();
            const examId = e.target.closest('.edit-exam-btn').dataset.id;
            if (examId) {
                console.log('Editing exam:', examId);
                loadExamForEdit(examId);
            }
        }

        if (e.target.closest('.delete-exam-btn')) {
            e.preventDefault();
            const examId = e.target.closest('.delete-exam-btn').dataset.id;
            if (examId) {
                console.log('Deleting exam:', examId);
                deleteExam(examId);
            }
        }

        // Check all functionality
        if (e.target.id === 'checkAll') {
            const checkboxes = document.querySelectorAll('input[name="chk_child"]');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            toggleRemoveActions();
        }

        // Individual checkbox change
        if (e.target.name === 'chk_child') {
            toggleRemoveActions();
        }
    });

    // Form submissions
    const addForm = document.getElementById('add-exam-form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Add form submitted');
            submitAddForm();
        });
    }

    const editForm = document.getElementById('edit-exam-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Edit form submitted');
            submitEditForm();
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.location.href = `{{ route('exams.index') }}?search=${encodeURIComponent(this.value)}`;
            }, 500);
        });
    }
});

function initModalFiltering() {
    // Add modal filtering
    const addTerm = document.getElementById('addTerm');
    const addSession = document.getElementById('addSession');
    const addSubject = document.getElementById('addSubject');

    if (addTerm && addSession && addSubject) {
        addTerm.addEventListener('change', function() {
            filterSubjects(this.value, addSession.value, addSubject);
        });

        addSession.addEventListener('change', function() {
            filterSubjects(addTerm.value, this.value, addSubject);
        });

        // Initialize filtering
        filterSubjects('', '', addSubject);
    }

    // Subject change listener for add modal
    if (addSubject) {
        addSubject.addEventListener('change', function() {
            if (this.value) {
                loadClassesForSubject(this.value, 'add');
            } else {
                document.getElementById('addClassContainer').innerHTML =
                    '<p class="text-muted text-center mb-0">Select a subject first...</p>';
            }
        });
    }
}

function filterSubjects(termId, sessionId, subjectSelect) {
    const allOptions = subjectSelect.querySelectorAll('option');

    allOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }

        const optionTermId = option.getAttribute('data-termid');
        const optionSessionId = option.getAttribute('data-sessionid');

        // Show option if it matches both selected term and session (if provided)
        const showOption = (!termId || optionTermId == termId) &&
                          (!sessionId || optionSessionId == sessionId);

        option.style.display = showOption ? '' : 'none';
        option.disabled = !showOption;
    });

    // Reset selection if current selection is hidden
    if (subjectSelect.value && subjectSelect.selectedOptions[0].style.display === 'none') {
        subjectSelect.value = '';
        const containerId = subjectSelect.id === 'addSubject' ? 'addClassContainer' : 'editClassContainer';
        document.getElementById(containerId).innerHTML =
            '<p class="text-muted text-center mb-0">Select a subject first...</p>';
    }
}

function loadClassesForSubject(subjectTeacherId, mode = 'add') {
    const containerId = mode === 'add' ? 'addClassContainer' : 'editClassContainer';
    const container = document.getElementById(containerId);

    container.innerHTML = '<p class="text-muted text-center mb-0"><i class="ri-loader-2-line spin me-1"></i> Loading classes...</p>';

    fetch(`/exams/subject-classes/${subjectTeacherId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('Classes response for', mode, 'mode:', data);
        if (data.success && data.classes && data.classes.length > 0) {
            if (mode === 'add') {
                // For add modal: show checkboxes
                let html = '<div class="row">';
                data.classes.forEach(cls => {
                    html += `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="schoolclass_ids[]"
                                       value="${cls.id}"
                                       id="class_add_${cls.id}">
                                <label class="form-check-label" for="class_add_${cls.id}">
                                    ${cls.schoolclass} ${cls.arm ? '(' + cls.arm + ')' : ''}
                                </label>
                            </div>
                        </div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            }
            // For edit modal, we handle it differently
        } else {
            container.innerHTML = '<p class="text-muted text-center mb-0">No classes assigned to this subject.</p>';
        }
    })
    .catch(error => {
        console.error('Error loading classes:', error);
        container.innerHTML = '<p class="text-danger text-center mb-0">Error loading classes. Please try again.</p>';
    });
}

function loadExamForEdit(examId) {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/exams/${examId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken // Use the globally defined csrfToken
        }
    })
    .then(response => {
        console.log('Edit response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('RAW Edit response data:', JSON.stringify(data, null, 2));

        if (data.success && data.exam) {
            populateEditForm(data);
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
            Swal.close();
        } else {
            console.error('Invalid response format:', data);
            throw new Error(data.message || 'Invalid response format');
        }
    })
    .catch(error => {
        console.error('Error loading exam:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load exam data. Please try again.',
            timer: 3000
        });
    });
}

function populateEditForm(data) {
    console.log('Populating edit form with data:', data);

    // Check if we have all required data
    if (!data.exam) {
        console.error('Missing exam data in response:', data);
        Swal.fire({
            icon: 'error',
            title: 'Data Error',
            text: 'Failed to load exam data properly.',
            timer: 3000
        });
        return;
    }

    const exam = data.exam;

    // Basic fields
    document.getElementById('edit-id-field').value = exam.id;
    document.getElementById('edit-title').value = exam.title || '';
    document.getElementById('edit-description').value = exam.description || '';
    document.getElementById('edit-duration').value = exam.duration || '';

    // Date fields - format for datetime-local input
    if (exam.start_time) {
        let startDate;
        if (exam.start_time.includes('T')) {
            startDate = new Date(exam.start_time);
        } else {
            startDate = new Date(exam.start_time.replace(' ', 'T'));
        }
        document.getElementById('edit-start_time').value = formatDateForInput(startDate);
    }

    if (exam.end_time) {
        let endDate;
        if (exam.end_time.includes('T')) {
            endDate = new Date(exam.end_time);
        } else {
            endDate = new Date(exam.end_time.replace(' ', 'T'));
        }
        document.getElementById('edit-end_time').value = formatDateForInput(endDate);
    }

    // Select fields
    document.getElementById('edit-termid').value = exam.termid || '';
    document.getElementById('edit-session').value = exam.session || '';
    document.getElementById('edit-publishStatus').checked = exam.is_published == 1;

    // Subject selection
    const subjectSelect = document.getElementById('edit-subject_id');
    subjectSelect.value = exam.subject_id || '';

    // Apply filtering based on selected term and session
    filterSubjects(exam.termid, exam.session, subjectSelect);

    // Display class information directly from the data
    const container = document.getElementById('editClassContainer');
    const className = data.schoolclass_name || (exam.schoolclass ? exam.schoolclass.schoolclass : 'Class not found');
    const classArm = data.schoolclass_arm || (exam.schoolclass ? exam.schoolclass.arm : null);
    const classId = data.schoolclass_id || exam.schoolclass_id;

    const html = `
        <div class="alert alert-info mb-0">
            <div class="d-flex align-items-center">
                <i class="ri-information-line me-2"></i>
                <div>
                    <strong>${className} ${classArm ? '(' + classArm + ')' : ''}</strong>
                    <p class="mb-0 mt-1">This exam is assigned to this class. You cannot change the class.</p>
                </div>
            </div>
        </div>
        <input type="hidden" name="schoolclass_id" value="${classId}" id="edit-schoolclass_id">`;

    container.innerHTML = html;

    console.log('Class displayed:', { className, classArm, classId });
}

function formatDateForInput(date) {
    if (!date || isNaN(date)) return '';

    try {
        // Format to YYYY-MM-DDTHH:MM
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch (error) {
        console.error('Date formatting error:', error);
        return '';
    }
}

function submitAddForm() {
    const form = document.getElementById('add-exam-form');
    const submitBtn = document.getElementById('add-btn');
    const originalText = submitBtn.textContent;

    // Validate class selection
    const classCheckboxes = form.querySelectorAll('input[name="schoolclass_ids[]"]:checked');
    if (classCheckboxes.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please select at least one class.',
            timer: 3000
        });
        return;
    }

    const formData = new FormData(form);

    // Log form data for debugging
    console.log('Add form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
    submitBtn.disabled = true;

    fetch('{{ route('exams.store') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Add response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Add response data:', data);
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Exam created successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addExamModal'));
                if (modal) modal.hide();
                form.reset();

                // Reset class container
                document.getElementById('addClassContainer').innerHTML =
                    '<p class="text-muted text-center mb-0">Select a subject first...</p>';

                // Reload page
                window.location.reload();
            });
        } else {
            let errorMsg = 'An error occurred.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            } else if (data.message) {
                errorMsg = data.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMsg,
                timer: 5000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
            timer: 3000
        });
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function submitEditForm() {
    const form = document.getElementById('edit-exam-form');
    const examId = document.getElementById('edit-id-field').value;
    const submitBtn = document.getElementById('update-btn');
    const originalText = submitBtn.textContent;

    if (!examId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Invalid exam ID.',
            timer: 3000
        });
        return;
    }

    const formData = new FormData(form);
    formData.append('_method', 'PUT'); // Add method override for PUT

    // Log form data for debugging
    console.log('Edit form data for exam', examId, ':');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    submitBtn.disabled = true;

    fetch(`/exams/${examId}`, {
        method: 'POST', // Use POST with _method override
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Update response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Exam updated successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                if (modal) modal.hide();

                // Reload page
                window.location.reload();
            });
        } else {
            let errorMsg = 'An error occurred.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            } else if (data.message) {
                errorMsg = data.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMsg,
                timer: 5000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
            timer: 3000
        });
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function deleteExam(examId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/exams/${examId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Delete response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data);
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Exam deleted successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete exam.',
                        timer: 3000
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete exam. Please try again.',
                    timer: 3000
                });
            });
        }
    });
}

function deleteMultiple() {
    const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
    if (checkedBoxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one exam to delete.',
            timer: 3000
        });
        return;
    }

    const ids = Array.from(checkedBoxes)
        .map(cb => cb.closest('td').dataset.id)
        .filter(id => id);

    Swal.fire({
        title: `Delete ${ids.length} exam(s)?`,
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/exams/bulk-destroy`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => {
                console.log('Bulk delete response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Bulk delete response data:', data);
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Exams deleted successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete exams.',
                        timer: 3000
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete exams. Please try again.',
                    timer: 3000
                });
            });
        }
    });
}

function toggleRemoveActions() {
    const removeActions = document.getElementById('remove-actions');
    if (removeActions) {
        const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
        removeActions.classList.toggle('d-none', checkedBoxes.length === 0);
    }
}
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

option[style*="display: none"] {
    display: none !important;
}

.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    vertical-align: text-bottom;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}
</style>

@endsection
