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
                                            @foreach($session as $s)
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
                                                    data-term="{{ $sub->term_id }}"
                                                    data-session="{{ $sub->session_id }}">
                                                {{ $sub->subject }} ({{ $sub->subjectcode }}) {{ $sub->term_name }}-{{ $sub->session_name }} {{ $sub->class_name }}{{ $sub->arm_name ? ' - ' . $sub->arm_name : '' }}
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
                                            @foreach($session as $s)
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
                                                    data-term="{{ $sub->term_id }}"
                                                    data-session="{{ $sub->session_id }}">
                                                {{ $sub->subject }} ({{ $sub->subjectcode }}) {{ $sub->term_name }}-{{ $sub->session_name }} {{ $sub->class_name }}{{ $sub->arm_name ? ' - ' . $sub->arm_name : '' }}
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

<script>
// Debounce function for search
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
                            <td><a href="/questions?exam=${exam.id}" class="btn btn-sm btn-soft-primary">Questions</a></td>
                            <td><a href="{{ route('exams.students', '') }}/${exam.id}" class="btn btn-sm btn-soft-info">Students</a></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-soft-secondary edit-btn" data-id="${exam.id}"><i class="ri-pencil-line"></i></button>
                                    <button class="btn btn-sm btn-soft-danger remove-btn" data-id="${exam.id}"><i class="ri-delete-bin-line"></i></button>
                                    <a href="{{ route('exams.analytics', '') }}/${exam.id}" class="btn btn-sm btn-soft-success"><i class="ri-bar-chart-line-line"></i></a>
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

// Dynamic filtering for subjects
document.addEventListener('DOMContentLoaded', function() {
    // CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Elements for Add Modal
    const addTermSelect = document.querySelector('#addExamModal select[name="termid"]');
    const addSessionSelect = document.querySelector('#addExamModal select[name="session"]');
    const addSubjectSelect = document.getElementById('addSubject');
    const addClassContainer = document.getElementById('addClassContainer');

    // Elements for Edit Modal
    const editTermSelect = document.querySelector('#editExamModal select[name="termid"]');
    const editSessionSelect = document.querySelector('#editExamModal select[name="session"]');
    const editSubjectSelect = document.getElementById('editSubject');
    const editClassContainer = document.getElementById('editClassContainer');

    // Function to load subjects based on filters
    function loadSubjects(termId, sessionId, targetSelect, callback = null) {
        const url = new URL('{{ route("exams.index") }}/get-subjects', window.location.origin);
        if (termId) url.searchParams.append('term_id', termId);
        if (sessionId) url.searchParams.append('session_id', sessionId);

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // Clear existing options except the first one
                while (targetSelect.options.length > 1) {
                    targetSelect.remove(1);
                }

                // Add new options
                if (Array.isArray(data)) {
                    data.forEach(subject => {
                        const option = new Option(subject.display_name, subject.id);
                        option.setAttribute('data-term', subject.term_id);
                        option.setAttribute('data-session', subject.session_id);
                        targetSelect.add(option);
                    });
                }

                if (callback) callback();
            })
            .catch(error => console.error('Error loading subjects:', error));
    }

    // Function to load classes for selected subject
    function loadClasses(subjectId, container, selectedClasses = []) {
        if (!subjectId) {
            container.innerHTML = '<p class="text-muted text-center mb-0">Select a subject first...</p>';
            return;
        }

        fetch(`{{ route("exams.subject-classes", "") }}/${subjectId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(classes => {
                let html = '';
                if (Array.isArray(classes) && classes.length > 0) {
                    classes.forEach(cls => {
                        const isChecked = selectedClasses.includes(cls.id.toString());
                        html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input class-checkbox" type="checkbox"
                                       name="schoolclass_ids[]" value="${cls.id}"
                                       id="class_${cls.id}" ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label" for="class_${cls.id}">
                                    ${cls.schoolclass}${cls.arm ? ' - ' + cls.arm : ''}
                                </label>
                            </div>`;
                    });
                } else {
                    html = '<p class="text-muted text-center mb-0">No classes available for this subject</p>';
                }

                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading classes:', error);
                container.innerHTML = '<p class="text-danger text-center mb-0">Error loading classes</p>';
            });
    }

    // Add modal event listeners
    if (addTermSelect && addSessionSelect && addSubjectSelect) {
        // Filter subjects when term or session changes
        [addTermSelect, addSessionSelect].forEach(select => {
            select.addEventListener('change', function() {
                const termId = addTermSelect.value;
                const sessionId = addSessionSelect.value;

                if (termId && sessionId) {
                    loadSubjects(termId, sessionId, addSubjectSelect, function() {
                        // Clear class container when subject list changes
                        addClassContainer.innerHTML = '<p class="text-muted text-center mb-0">Select a subject first...</p>';
                        addSubjectSelect.value = '';
                    });
                }
            });
        });

        // Load classes when subject changes
        addSubjectSelect.addEventListener('change', function() {
            const subjectId = this.value;
            loadClasses(subjectId, addClassContainer);
        });
    }

    // Edit modal event listeners
    if (editTermSelect && editSessionSelect && editSubjectSelect) {
        // Filter subjects when term or session changes
        [editTermSelect, editSessionSelect].forEach(select => {
            select.addEventListener('change', function() {
                const termId = editTermSelect.value;
                const sessionId = editSessionSelect.value;

                if (termId && sessionId) {
                    loadSubjects(termId, sessionId, editSubjectSelect, function() {
                        // Clear class container when subject list changes
                        editClassContainer.innerHTML = '<p class="text-muted text-center mb-0">Select a subject first...</p>';
                        editSubjectSelect.value = '';
                    });
                }
            });
        });

        // Load classes when subject changes
        editSubjectSelect.addEventListener('change', function() {
            const subjectId = this.value;
            loadClasses(subjectId, editClassContainer);
        });
    }

    // Edit button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-btn')) {
            const btn = e.target.closest('.edit-btn');
            const examId = btn.dataset.id;

            fetch(`{{ route("exams.index") }}/${examId}/edit`, {
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
                    if (data.success) {
                        const exam = data.exam;

                        // Populate edit form
                        document.getElementById('editExamId').value = exam.id;
                        document.getElementById('editTitle').value = exam.title;
                        document.getElementById('editDescription').value = exam.description || '';
                        document.getElementById('editDuration').value = exam.duration;

                        // Format dates for datetime-local input
                        const startTime = new Date(exam.start_time);
                        const endTime = new Date(exam.end_time);

                        document.getElementById('editStartTime').value = startTime.toISOString().slice(0, 16);
                        document.getElementById('editEndTime').value = endTime.toISOString().slice(0, 16);

                        document.getElementById('editTerm').value = exam.termid;
                        document.getElementById('editSession').value = exam.session;
                        document.getElementById('editPublish').checked = exam.is_published;

                        // Set subject first, then load its classes
                        loadSubjects(exam.termid, exam.session, editSubjectSelect, function() {
                            document.getElementById('editSubject').value = exam.subject_id;

                            // Now load classes for this subject with pre-selected classes
                            loadClasses(exam.subject_id, editClassContainer, data.schoolclass_ids);
                        });

                        // Show modal
                        const editModal = new bootstrap.Modal(document.getElementById('editExamModal'));
                        editModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading exam:', error);
                    alert('Error loading exam data');
                });
        }
    });

    // Form submission handlers
    const addExamForm = document.getElementById('addExamForm');
    const editExamForm = document.getElementById('editExamForm');

    if (addExamForm) {
        addExamForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            // Get selected classes
            const classCheckboxes = addClassContainer.querySelectorAll('input[name="schoolclass_ids[]"]:checked');
            if (classCheckboxes.length === 0) {
                alert('Please select at least one class');
                return;
            }

            // Add selected classes to form data
            classCheckboxes.forEach(checkbox => {
                formData.append('schoolclass_ids[]', checkbox.value);
            });

            fetch('{{ route("exams.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the exam');
            });
        });
    }

    if (editExamForm) {
        editExamForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const examId = document.getElementById('editExamId').value;
            const formData = new FormData(this);

            // Get selected classes
            const classCheckboxes = editClassContainer.querySelectorAll('input[name="schoolclass_ids[]"]:checked');
            if (classCheckboxes.length === 0) {
                alert('Please select at least one class');
                return;
            }

            // Add selected classes to form data
            classCheckboxes.forEach(checkbox => {
                formData.append('schoolclass_ids[]', checkbox.value);
            });

            fetch(`{{ route("exams.index") }}/${examId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the exam');
            });
        });
    }

    // Delete button handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-btn')) {
            const btn = e.target.closest('.remove-btn');
            const examId = btn.dataset.id;

            if (confirm('Are you sure you want to delete this exam?')) {
                fetch(`{{ route("exams.index") }}/${examId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the exam');
                });
            }
        }
    });

    // Bulk delete
    window.deleteMultiple = function() {
        const checkboxes = document.querySelectorAll('input[name="chkIds[]"]:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);

        if (ids.length === 0) {
            alert('Please select at least one exam to delete');
            return;
        }

        if (confirm(`Are you sure you want to delete ${ids.length} selected exam(s)?`)) {
            fetch('{{ route("exams.bulk-destroy") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting exams');
            });
        }
    };

    // Check all functionality
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="chkIds[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);

            // Show/hide bulk delete button
            const removeActionsBtn = document.getElementById('remove-actions');
            if (removeActionsBtn) {
                if (this.checked && checkboxes.length > 0) {
                    removeActionsBtn.classList.remove('d-none');
                } else {
                    removeActionsBtn.classList.add('d-none');
                }
            }
        });
    }

    // Individual checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="chkIds[]"]')) {
            const checkboxes = document.querySelectorAll('input[name="chkIds[]"]');
            const checkedCount = document.querySelectorAll('input[name="chkIds[]"]:checked').length;
            const checkAll = document.getElementById('checkAll');
            const removeActionsBtn = document.getElementById('remove-actions');

            if (checkAll) {
                checkAll.checked = checkedCount === checkboxes.length;
                checkAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }

            if (removeActionsBtn) {
                if (checkedCount > 0) {
                    removeActionsBtn.classList.remove('d-none');
                } else {
                    removeActionsBtn.classList.add('d-none');
                }
            }
        }
    });
});
</script>

@endsection
