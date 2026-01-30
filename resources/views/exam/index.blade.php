@extends('layouts.master')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="examsList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4 col-lg-6">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search exams...">
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
                                    <h5 class="card-title mb-0">
                                        Exams <span class="badge bg-dark-subtle text-dark ms-1" id="totalBadge">{{ $exams->total() }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('Delete exam')
                                            <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                                <i class="ri-delete-bin-2-line align-bottom me-1"></i> Delete Selected
                                            </button>
                                        @endcan
                                        @can('Create exam')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addExamModal">
                                                <i class="ri-add-circle-line align-bottom me-1"></i> Create New Exam
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-nowrap table-hover mb-0" id="kt_exams_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th>SN</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Duration</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Class</th>
                                                <th>Questions</th>
                                                <th>Students</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            @php $i = ($exams->currentPage() - 1) * $exams->perPage() @endphp
                                            @forelse ($exams as $exam)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chkIds[]" value="{{ $exam->id }}" />
                                                        </div>
                                                    </td>
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $exam->title }}</td>
                                                    <td>{{ Str::limit($exam->description ?? '—', 50) }}</td>
                                                    <td>{{ $exam->duration }} mins</td>
                                                    <td>{{ $exam->formatted_start_time }}</td>
                                                    <td>{{ $exam->formatted_end_time }}</td>
                                                    <td>
                                                        @if($exam->schoolclass)
                                                            {{ $exam->schoolclass->schoolclass }}
                                                            {{ $exam->schoolclass->arm ? '(' . $exam->schoolclass->arm . ')' : '' }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('questions.index', $exam->id) }}" class="btn btn-sm btn-soft-primary">
                                                            Questions
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('exams.students', $exam->id) }}" class="btn btn-sm btn-soft-info">
                                                            Students
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            @can('Update exam')
                                                                <button class="btn btn-sm btn-soft-secondary edit-btn" data-id="{{ $exam->id }}">
                                                                    <i class="ri-pencil-line"></i>
                                                                </button>
                                                            @endcan
                                                            @can('Delete exam')
                                                                <button class="btn btn-sm btn-soft-danger remove-btn" data-id="{{ $exam->id }}">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            @endcan
                                                            <a href="{{ route('exams.analytics', $exam->id) }}" class="btn btn-sm btn-soft-success">
                                                                <i class="ri-bar-chart-line-line"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-5 text-muted">
                                                        No exams found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4" id="paginationContainer">
                                    <div class="text-muted small" id="showingInfo">
                                        Showing {{ $exams->firstItem() }} to {{ $exams->lastItem() }} of {{ $exams->total() }} results
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0" id="paginationLinks">
                                            <!-- Filled by JS -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Exam Modal -->
            @can('Create exam')
            <div class="modal fade" id="addExamModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="addExamForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="staffId" value="{{ Auth::id() }}">

                                <div class="mb-3">
                                    <label class="form-label required">Exam Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Duration (minutes)</label>
                                        <input type="number" name="duration" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Publish?</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_published" value="1" checked>
                                            <label class="form-check-label">Publish immediately</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Start Time</label>
                                        <input type="datetime-local" name="start_time" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">End Time</label>
                                        <input type="datetime-local" name="end_time" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Term</label>
                                        <select name="termid" id="addTerm" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Session</label>
                                        <select name="session" id="addSession" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach($sessions as $s)
                                                <option value="{{ $s->id }}">{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Subject</label>
                                    <select name="subject_id" id="addSubject" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        @foreach($mysubjects as $sub)
                                            <option value="{{ $sub->id }}"
                                                data-termid="{{ $sub->termid }}"
                                                data-sessionid="{{ $sub->sessionid }}"
                                                data-class="{{ $sub->schoolclass }}"
                                                data-arm="{{ $sub->arm }}">
                                                {{ $sub->subject }} ({{ $sub->subjectcode }}) - {{ $sub->term }} {{ $sub->session }} - {{ $sub->schoolclass }} {{ $sub->arm ? '(' . $sub->arm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Classes</label>
                                    <div id="addClassContainer" class="border rounded p-3 bg-light" style="max-height: 240px; overflow-y: auto;">
                                        <p class="text-muted text-center mb-0">Select a subject first...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Edit Modal -->
            @can('Update exam')
            <div class="modal fade" id="editExamModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editExamForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="editExamId" name="id">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label required">Exam Title</label>
                                    <input type="text" name="title" id="editTitle" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Duration (minutes)</label>
                                        <input type="number" name="duration" id="editDuration" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Publish?</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="editPublish" value="1">
                                            <label class="form-check-label">Publish immediately</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Start Time</label>
                                        <input type="datetime-local" name="start_time" id="editStartTime" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">End Time</label>
                                        <input type="datetime-local" name="end_time" id="editEndTime" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Term</label>
                                        <select name="termid" id="editTerm" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Session</label>
                                        <select name="session" id="editSession" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach($sessions as $s)
                                                <option value="{{ $s->id }}">{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Subject</label>
                                    <select name="subject_id" id="editSubject" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        @foreach($mysubjects as $sub)
                                            <option value="{{ $sub->id }}"
                                                data-termid="{{ $sub->termid }}"
                                                data-sessionid="{{ $sub->sessionid }}"
                                                data-class="{{ $sub->schoolclass }}"
                                                data-arm="{{ $sub->arm }}">
                                                {{ $sub->subject }} ({{ $sub->subjectcode }}) - {{ $sub->term }} {{ $sub->session }} - {{ $sub->schoolclass }} {{ $sub->arm ? '(' . $sub->arm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Classes</label>
                                    <div id="editClassContainer" class="border rounded p-3 bg-light" style="max-height: 240px; overflow-y: auto;">
                                        <p class="text-muted text-center mb-0">Loading classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

        </div>
    </div>
</div>

<style>
/* Custom styles for dropdown filtering */
option[data-termid][data-sessionid] {
    padding: 8px 12px;
}

option:disabled {
    font-weight: bold;
    background-color: #f8f9fa;
    color: #495057;
    padding: 10px 12px;
}

option:not(:disabled) {
    border-bottom: 1px solid #f0f0f0;
}

option:last-child:not(:disabled) {
    border-bottom: none;
}
</style>

<script>
// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Full JS for dynamic table + pagination
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('tableBody');
    const paginationLinks = document.getElementById('paginationLinks');
    const showingInfo = document.getElementById('showingInfo');
    const totalBadge = document.getElementById('totalBadge');
    const searchInput = document.querySelector('.search');

    let currentPage = {{ $exams->currentPage() }};
    let searchTerm = '';

    function loadTable(page = currentPage) {
        currentPage = page;
        const url = `{{ route('exams.index') }}?page=${page}&search=${encodeURIComponent(searchTerm)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            // Update table body
            tableBody.innerHTML = '';
            let i = (data.current_page - 1) * data.per_page + 1;

            if (!data.data.length) {
                tableBody.innerHTML = '<tr><td colspan="11" class="text-center py-5">No exams found</td></tr>';
            } else {
                data.data.forEach(exam => {
                    const classDisplay = exam.schoolclass
                        ? `${exam.schoolclass.schoolclass}${exam.schoolclass.arm ? ' (' + exam.schoolclass.arm + ')' : ''}`
                        : '—';

                    const row = `
                        <tr>
                            <td><div class="form-check"><input class="form-check-input" type="checkbox" name="chkIds[]" value="${exam.id}"></div></td>
                            <td>${i++}</td>
                            <td>${exam.title}</td>
                            <td>${exam.description ? exam.description.substring(0,50) + (exam.description.length > 50 ? '...' : '') : '—'}</td>
                            <td>${exam.duration} mins</td>
                            <td>${exam.formatted_start_time || '—'}</td>
                            <td>${exam.formatted_end_time || '—'}</td>
                            <td>${classDisplay}</td>
                            <td><a href="/questions/${exam.id}" class="btn btn-sm btn-soft-primary">Questions</a></td>
                            <td><a href="/exams/${exam.id}/students" class="btn btn-sm btn-soft-info">Students</a></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-soft-secondary edit-btn" data-id="${exam.id}"><i class="ri-pencil-line"></i></button>
                                    <button class="btn btn-sm btn-soft-danger remove-btn" data-id="${exam.id}"><i class="ri-delete-bin-line"></i></button>
                                    <a href="/exams/${exam.id}/analytics" class="btn btn-sm btn-soft-success"><i class="ri-bar-chart-line-line"></i></a>
                                </div>
                            </td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
            }

            // Update pagination
            paginationLinks.innerHTML = '';
            const prev = document.createElement('li');
            prev.className = `page-item ${data.current_page === 1 ? 'disabled' : ''}`;
            prev.innerHTML = `<a class="page-link" href="#" ${data.current_page === 1 ? '' : `onclick="loadTable(${data.current_page - 1}); return false;"`}>«</a>`;
            paginationLinks.appendChild(prev);

            for (let p = 1; p <= data.last_page; p++) {
                const li = document.createElement('li');
                li.className = `page-item ${data.current_page === p ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#" onclick="loadTable(${p}); return false;">${p}</a>`;
                paginationLinks.appendChild(li);
            }

            const next = document.createElement('li');
            next.className = `page-item ${data.current_page === data.last_page ? 'disabled' : ''}`;
            next.innerHTML = `<a class="page-link" href="#" ${data.current_page === data.last_page ? '' : `onclick="loadTable(${data.current_page + 1}); return false;"`}>»</a>`;
            paginationLinks.appendChild(next);

            // Update info
            showingInfo.textContent = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total} results`;
            totalBadge.textContent = data.total;

            // Re-attach event listeners for edit and delete buttons
            attachEventListeners();
        });
    }

    // Search
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            searchTerm = searchInput.value.trim();
            loadTable(1);
        }, 400));
    }

    // Initial load
    loadTable(currentPage);
});

// Function to filter subjects in dropdown
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
        if (subjectSelect.id === 'addSubject') {
            document.getElementById('addClassContainer').innerHTML =
                '<p class="text-muted text-center mb-0">Select a subject first...</p>';
        } else if (subjectSelect.id === 'editSubject') {
            document.getElementById('editClassContainer').innerHTML =
                '<p class="text-muted text-center mb-0">Select a subject first...</p>';
        }
    }
}

// Function to load classes for a subject
function loadClassesForSubject(subjectTeacherId, mode = 'add') {
    const containerId = mode === 'add' ? 'addClassContainer' : 'editClassContainer';
    const container = document.getElementById(containerId);

    container.innerHTML = '<p class="text-muted text-center mb-0"><i class="ri-loader-2-line spin me-1"></i> Loading classes...</p>';

    fetch(`/exams/subject-classes/${subjectTeacherId}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.success && data.classes.length > 0) {
                let html = '<div class="row">';

                data.classes.forEach(cls => {
                    const isChecked = mode === 'edit' && data.selectedClasses && data.selectedClasses.includes(cls.id);
                    html += `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="schoolclass_ids[]"
                                       value="${cls.id}"
                                       id="class_${mode}_${cls.id}"
                                       ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label" for="class_${mode}_${cls.id}">
                                    ${cls.schoolclass} ${cls.arm ? '(' + cls.arm + ')' : ''}
                                </label>
                            </div>
                        </div>`;
                });

                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted text-center mb-0">No classes assigned to this subject.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading classes:', error);
            container.innerHTML = '<p class="text-danger text-center mb-0">Error loading classes. Please try again.</p>';
        });
}

// Attach event listeners to edit and delete buttons
function attachEventListeners() {
    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const examId = this.getAttribute('data-id');

            fetch(`/exams/${examId}/edit`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        // Populate form
                        document.getElementById('editExamId').value = examId;
                        document.getElementById('editTitle').value = data.exam.title;
                        document.getElementById('editDescription').value = data.exam.description || '';
                        document.getElementById('editDuration').value = data.exam.duration;

                        // Format datetime for input fields
                        const startTime = new Date(data.exam.start_time);
                        const endTime = new Date(data.exam.end_time);

                        document.getElementById('editStartTime').value = startTime.toISOString().slice(0, 16);
                        document.getElementById('editEndTime').value = endTime.toISOString().slice(0, 16);

                        document.getElementById('editTerm').value = data.exam.termid;
                        document.getElementById('editSession').value = data.exam.session;
                        document.getElementById('editPublish').checked = data.exam.is_published;

                        // Set subject value and filter
                        const subjectSelect = document.getElementById('editSubject');
                        subjectSelect.value = data.subject_id;

                        // Apply filtering based on selected term and session
                        filterSubjects(data.exam.termid, data.exam.session, subjectSelect);

                        // Load classes for this subject
                        loadClassesForSubjectEdit(data.subject_id, data.schoolclass_ids);

                        // Show modal
                        const editModal = new bootstrap.Modal(document.getElementById('editExamModal'));
                        editModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading exam data:', error);
                    alert('Error loading exam data. Please try again.');
                });
        });
    });

    // Delete buttons
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const examId = this.getAttribute('data-id');

            if (confirm('Are you sure you want to delete this exam?')) {
                fetch(`/exams/${examId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error deleting exam:', error);
                    alert('Error deleting exam. Please try again.');
                });
            }
        });
    });
}

// Special function for edit modal to load classes with selected ones
function loadClassesForSubjectEdit(subjectTeacherId, selectedClassIds = []) {
    const container = document.getElementById('editClassContainer');

    container.innerHTML = '<p class="text-muted text-center mb-0"><i class="ri-loader-2-line spin me-1"></i> Loading classes...</p>';

    fetch(`/exams/subject-classes/${subjectTeacherId}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.success && data.classes.length > 0) {
                let html = '<div class="row">';

                data.classes.forEach(cls => {
                    const isChecked = selectedClassIds.includes(parseInt(cls.id));
                    html += `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="schoolclass_ids[]"
                                       value="${cls.id}"
                                       id="class_edit_${cls.id}"
                                       ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label" for="class_edit_${cls.id}">
                                    ${cls.schoolclass} ${cls.arm ? '(' + cls.arm + ')' : ''}
                                </label>
                            </div>
                        </div>`;
                });

                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted text-center mb-0">No classes assigned to this subject.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading classes:', error);
            container.innerHTML = '<p class="text-danger text-center mb-0">Error loading classes. Please try again.</p>';
        });
}

// Initialize event listeners for modals when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
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

    // Edit modal filtering
    const editTerm = document.getElementById('editTerm');
    const editSession = document.getElementById('editSession');
    const editSubject = document.getElementById('editSubject');

    if (editTerm && editSession && editSubject) {
        editTerm.addEventListener('change', function() {
            filterSubjects(this.value, editSession.value, editSubject);
        });

        editSession.addEventListener('change', function() {
            filterSubjects(editTerm.value, this.value, editSubject);
        });
    }

    // Subject change listeners
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

    if (editSubject) {
        editSubject.addEventListener('change', function() {
            if (this.value) {
                loadClassesForSubject(this.value, 'edit');
            } else {
                document.getElementById('editClassContainer').innerHTML =
                    '<p class="text-muted text-center mb-0">Select a subject first...</p>';
            }
        });
    }

    // Form submissions
    const addExamForm = document.getElementById('addExamForm');
    if (addExamForm) {
        addExamForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('{{ route("exams.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error creating exam. Please check your inputs.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating exam. Please try again.');
            });
        });
    }

    const editExamForm = document.getElementById('editExamForm');
    if (editExamForm) {
        editExamForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const examId = document.getElementById('editExamId').value;
            const formData = new FormData(this);

            fetch(`/exams/${examId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-HTTP-Method-Override': 'PUT'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error updating exam. Please check your inputs.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating exam. Please try again.');
            });
        });
    }

    // Check all functionality
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="chkIds[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            const removeActions = document.getElementById('remove-actions');
            if (removeActions) {
                removeActions.classList.toggle('d-none', !this.checked);
            }
        });
    }
});

// Bulk delete function
function deleteMultiple() {
    const selectedIds = Array.from(document.querySelectorAll('input[name="chkIds[]"]:checked'))
        .map(checkbox => checkbox.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one exam to delete.');
        return;
    }

    if (confirm(`Are you sure you want to delete ${selectedIds.length} selected exam(s)?`)) {
        fetch('{{ route("exams.bulk-destroy") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Error deleting exams.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting exams. Please try again.');
        });
    }
}
</script>

@endsection
