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
                                        Exams <span class="badge bg-dark-subtle text-dark ms-1">{{ $exams->total() }}</span>
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
                                                <th class="sort" data-sort="sn">SN</th>
                                                <th class="sort" data-sort="title">Title</th>
                                                <th class="sort" data-sort="description">Description</th>
                                                <th class="sort" data-sort="duration">Duration</th>
                                                <th class="sort" data-sort="start_time">Start Time</th>
                                                <th class="sort" data-sort="end_time">End Time</th>
                                                <th>Class</th>
                                                <th>Questions</th>
                                                <th>Students</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @php $i = ($exams->currentPage() - 1) * $exams->perPage() @endphp
                                            @forelse ($exams as $exam)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chkIds[]" value="{{ $exam->id }}" />
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="title">{{ $exam->title }}</td>
                                                    <td class="description">{{ Str::limit($exam->description ?? '—', 50) }}</td>
                                                    <td class="duration">{{ $exam->duration }} mins</td>
                                                    <td class="start_time">{{ $exam->formatted_start_time }}</td>
                                                    <td class="end_time">{{ $exam->formatted_end_time }}</td>
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
                                                            <i class="ri-list-check-2"></i> Questions
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('exams.students', $exam->id) }}" class="btn btn-sm btn-soft-info">
                                                            <i class="ri-group-line"></i> Students
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
                                                                <i class="ri-bar-chart-line-line"></i> Analytics
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-5 text-muted fs-15">
                                                        No exams found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="text-muted small">
                                        Showing {{ $exams->firstItem() }} to {{ $exams->lastItem() }} of {{ $exams->total() }} results
                                    </div>
                                    {{ $exams->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Exam Modal -->
            @can('Create exam')
            <div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="addExamModalLabel">Create New Exam</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="addExamForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="staffId" value="{{ Auth::id() }}">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold required">Exam Title</label>
                                    <input type="text" name="title" class="form-control" required placeholder="e.g. End of Term Physics">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Description (optional)</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description or instructions..."></textarea>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Duration (minutes)</label>
                                        <input type="number" name="duration" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="publishAdd" value="1" checked>
                                            <label class="form-check-label" for="publishAdd">Publish immediately</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Start Time</label>
                                        <input type="datetime-local" name="start_time" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">End Time</label>
                                        <input type="datetime-local" name="end_time" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Term</label>
                                        <select name="termid" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Session</label>
                                        <select name="session" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach($session as $s)
                                                <option value="{{ $s->id }}">{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold required">Subject</label>
                                    <select name="subject_id" id="addSubject" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        @foreach($mysubjects as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->subject }} ({{ $sub->subjectcode }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold required">Classes</label>
                                    <div id="addClassContainer" class="border rounded p-3 bg-light" style="max-height: 240px; overflow-y: auto;">
                                        <p class="text-muted text-center mb-0">Select a subject to load classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="addExamSubmit">
                                    <i class="ri-save-line me-1"></i> Create Exam
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Edit Exam Modal -->
            @can('Update exam')
            <div class="modal fade" id="editExamModal" tabindex="-1" aria-labelledby="editExamModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="editExamModalLabel">Edit Exam</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editExamForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id" id="editExamId">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold required">Exam Title</label>
                                    <input type="text" name="title" id="editTitle" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Description (optional)</label>
                                    <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Duration (minutes)</label>
                                        <input type="number" name="duration" id="editDuration" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="editPublish" value="1">
                                            <label class="form-check-label" for="editPublish">Publish immediately</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Start Time</label>
                                        <input type="datetime-local" name="start_time" id="editStartTime" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">End Time</label>
                                        <input type="datetime-local" name="end_time" id="editEndTime" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Term</label>
                                        <select name="termid" id="editTerm" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Session</label>
                                        <select name="session" id="editSession" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach($session as $s)
                                                <option value="{{ $s->id }}">{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold required">Subject</label>
                                    <select name="subject_id" id="editSubject" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        @foreach($mysubjects as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->subject }} ({{ $sub->subjectcode }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold required">Classes</label>
                                    <div id="editClassContainer" class="border rounded p-3 bg-light" style="max-height: 240px; overflow-y: auto;">
                                        <p class="text-muted text-center mb-0">Loading classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-info" id="editExamSubmit">
                                    <i class="ri-save-line me-1"></i> Update Exam
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Delete Confirmation Modal (single) -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this exam record?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript (AJAX + Modals) -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.querySelector('.search');
    const tableContainer = document.getElementById('examsList');

    // Search with debounce
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => loadTable(1), 400));
    }

    // Check all / bulk actions
    const checkAll = document.getElementById('checkAll');
    const bulkDeleteBtn = document.getElementById('remove-actions');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="chkIds[]"]').forEach(cb => cb.checked = this.checked);
            toggleBulkButton();
        });
    }

    document.addEventListener('change', e => {
        if (e.target.name === 'chkIds[]') toggleBulkButton();
    });

    function toggleBulkButton() {
        const checkedCount = document.querySelectorAll('input[name="chkIds[]"]:checked').length;
        if (bulkDeleteBtn) bulkDeleteBtn.classList.toggle('d-none', checkedCount === 0);
    }

    // Load table via AJAX
    function loadTable(page = 1) {
        const search = searchInput?.value.trim() || '';
        const url = `{{ route('exams.index') }}?page=${page}&search=${encodeURIComponent(search)}`;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                tableContainer.innerHTML = doc.getElementById('examsList').innerHTML;
                attachDynamicListeners();
            })
            .catch(err => console.error('Table reload failed:', err));
    }

    // Attach listeners after table reload
    function attachDynamicListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                fetch(`/exams/${id}/edit`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('editExamId').value = data.exam.id;
                            document.getElementById('editTitle').value = data.exam.title;
                            document.getElementById('editDescription').value = data.exam.description || '';
                            document.getElementById('editDuration').value = data.exam.duration;
                            document.getElementById('editStartTime').value = data.exam.start_time?.slice(0, 16) || '';
                            document.getElementById('editEndTime').value = data.exam.end_time?.slice(0, 16) || '';
                            document.getElementById('editTerm').value = data.exam.termid;
                            document.getElementById('editSession').value = data.exam.session;
                            document.getElementById('editSubject').value = data.exam.subject_id;
                            document.getElementById('editPublish').checked = !!data.exam.is_published;

                            loadClasses('edit', data.exam.subject_id, data.schoolclass_ids || []);

                            new bootstrap.Modal(document.getElementById('editExamModal')).show();
                        }
                    })
                    .catch(err => console.error('Edit load failed:', err));
            });
        });

        // Single delete buttons
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                document.getElementById('confirmDelete').onclick = () => deleteSingleExam(id);
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });
        });
    }

    // Load classes for add/edit modals
    function loadClasses(type, subjectId, selected = []) {
        if (!subjectId) return;
        fetch(`/exams/subject-classes/${subjectId}`)
            .then(res => res.json())
            .then(classes => {
                const container = document.getElementById(`${type}ClassContainer`);
                container.innerHTML = '';
                if (!classes.length) {
                    container.innerHTML = '<p class="text-muted text-center">No classes available</p>';
                    return;
                }
                classes.forEach(cls => {
                    const isChecked = selected.includes(cls.schoolclassID) ? 'checked' : '';
                    container.innerHTML += `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="schoolclass_ids[]"
                                   value="${cls.schoolclassID}" id="${type}Cls${cls.schoolclassID}" ${isChecked}>
                            <label class="form-check-label" for="${type}Cls${cls.schoolclassID}">
                                ${cls.schoolclass} ${cls.arm_name ? `(${cls.arm_name})` : ''}
                            </label>
                        </div>
                    `;
                });
            })
            .catch(err => console.error('Classes load failed:', err));
    }

    // Subject change listeners
    document.getElementById('addSubject')?.addEventListener('change', e => {
        loadClasses('add', e.target.value);
    });

    document.getElementById('editSubject')?.addEventListener('change', e => {
        loadClasses('edit', e.target.value);
    });

    // Add form submit
    document.getElementById('addExamForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('addExamSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';

        const formData = new FormData(this);

        fetch('{{ route('exams.store') }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addExamModal')).hide();
                    this.reset();
                    loadTable(1);
                    alert(data.message || 'Exam(s) created successfully!');
                } else {
                    alert(data.message || 'Error creating exam');
                }
            })
            .catch(() => alert('Network error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-save-line me-1"></i> Create Exam';
            });
    });

    // Edit form submit
    document.getElementById('editExamForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('editExamId').value;
        const btn = document.getElementById('editExamSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';

        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        fetch(`/exams/${id}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editExamModal')).hide();
                    loadTable(1);
                    alert(data.message || 'Exam(s) updated successfully!');
                } else {
                    alert(data.message || 'Error updating exam');
                }
            })
            .catch(() => alert('Network error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-save-line me-1"></i> Update Exam';
            });
    });

    // Bulk delete
    function deleteMultiple() {
        const ids = Array.from(document.querySelectorAll('input[name="chkIds[]"]:checked'))
            .map(cb => cb.value);

        if (!ids.length) return;

        if (!confirm(`Delete ${ids.length} exam record(s)?`)) return;

        fetch('{{ route('exams.bulk-destroy') }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadTable(1);
                    alert(data.message);
                } else {
                    alert(data.message || 'Bulk delete failed');
                }
            })
            .catch(() => alert('Network error'));
    }

    // Single delete
    function deleteSingleExam(id) {
        fetch(`/exams/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadTable(1);
                    alert(data.message);
                }
            })
            .catch(() => alert('Delete failed'));
    }

    // Debounce helper
    function debounce(fn, delay) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    }

    // Initial setup
    attachDynamicListeners();
});
</script>

@endsection
