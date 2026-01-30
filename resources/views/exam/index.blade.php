@extends('layouts.master')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Exams</h4>
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
                                    <div class="col-xxl-3">
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
                                    <h5 class="card-title mb-0">Exams <span class="badge bg-dark-subtle text-dark ms-1">{{ $exams->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
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
                                                <th scope="col" style="width: 50px;">
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
                                                    <td class="start_time">{{ $exam->start_time->format('d M Y H:i') }}</td>
                                                    <td class="end_time">{{ $exam->end_time->format('d M Y H:i') }}</td>
                                                    <td>
                                                        @if($exam->schoolclass)
                                                            {{ $exam->schoolclass->schoolclass }}
                                                            {{ $exam->schoolclass->arm ? '(' . $exam->schoolclass->arm->arm . ')' : '' }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('questions.index', $exam->id) }}" class="btn btn-sm btn-soft-primary">
                                                            View Questions
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
                                                                <button class="btn btn-sm btn-soft-danger remove-btn" data-id="{{ $exam->id }}" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">
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
                                                    <td colspan="11" class="text-center py-4 text-muted">No exams found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="text-muted">
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
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addExamModalLabel">Create New Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="addExamForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="staffId" value="{{ Auth::id() }}">

                                <div class="mb-3">
                                    <label class="form-label required">Exam Title</label>
                                    <input type="text" name="title" class="form-control" required placeholder="e.g. Mid-Term Mathematics">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Duration (minutes)</label>
                                        <input type="number" name="duration" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Publish?</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1">
                                            <label class="form-check-label" for="isPublished">Publish immediately</label>
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
                                        <select name="termid" class="form-select" required>
                                            <option value="">Select Term</option>
                                            @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Session</label>
                                        <select name="session" class="form-select" required>
                                            <option value="">Select Session</option>
                                            @foreach($session as $s)
                                                <option value="{{ $s->id }}">{{ $s->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Subject</label>
                                    <select name="subject_id" id="addSubjectSelect" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        @foreach($mysubjects as $sub)
                                            <option value="{{ $sub->id }}">
                                                {{ $sub->subject }} ({{ $sub->subjectcode }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Classes</label>
                                    <div id="addClassCheckboxes" class="border rounded p-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                                        <p class="text-muted mb-0">Select a subject first...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="addExamBtn">Create Exam</button>
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
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editExamModalLabel">Edit Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editExamForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id" id="editExamId">
                            <div class="modal-body">
                                <!-- Same fields as add modal, but populated via JS -->
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
                                        <label class="form-label required">Publish?</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="editIsPublished" value="1">
                                            <label class="form-check-label" for="editIsPublished">Publish immediately</label>
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
                                        <select name="termid" id="editTermId" class="form-select" required>
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
                                    <select name="subject_id" id="editSubjectId" class="form-select" required>
                                        <option value="">Select Subject</option>
                                        @foreach($mysubjects as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->subject }} ({{ $sub->subjectcode }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Classes</label>
                                    <div id="editClassCheckboxes" class="border rounded p-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                                        <p class="text-muted mb-0">Loading classes...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="updateExamBtn">Update Exam</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Delete Confirmation Modal (for single delete) -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this exam? This action cannot be undone.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Search
    const searchInput = document.querySelector('.search');
    searchInput?.addEventListener('input', debounce(() => {
        loadTable(1);
    }, 400));

    // Bulk actions visibility
    const checkAll = document.getElementById('checkAll');
    const removeActions = document.getElementById('remove-actions');

    checkAll?.addEventListener('change', function() {
        document.querySelectorAll('input[name="chkIds[]"]').forEach(cb => cb.checked = this.checked);
        toggleBulkDelete();
    });

    document.addEventListener('change', e => {
        if (e.target.name === 'chkIds[]') {
            toggleBulkDelete();
        }
    });

    function toggleBulkDelete() {
        const checked = document.querySelectorAll('input[name="chkIds[]"]:checked').length;
        removeActions?.classList.toggle('d-none', checked === 0);
    }

    // Load table (AJAX)
    function loadTable(page = 1) {
        const search = searchInput?.value || '';
        const url = `{{ route('exams.index') }}?page=${page}&search=${encodeURIComponent(search)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('examsList').innerHTML = doc.getElementById('examsList').innerHTML;
            attachEventListeners();
        })
        .catch(err => console.error('Table load error:', err));
    }

    // Attach listeners after load
    function attachEventListeners() {
        // Edit button
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch(`{{ url('exams') }}/${id}/edit`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editExamId').value = data.exam.id;
                        document.getElementById('editTitle').value = data.exam.title;
                        document.getElementById('editDescription').value = data.exam.description || '';
                        document.getElementById('editDuration').value = data.exam.duration;
                        document.getElementById('editStartTime').value = data.exam.start_time.slice(0,16);
                        document.getElementById('editEndTime').value = data.exam.end_time.slice(0,16);
                        document.getElementById('editTermId').value = data.exam.termid;
                        document.getElementById('editSession').value = data.exam.session;
                        document.getElementById('editSubjectId').value = data.exam.subject_id;
                        document.getElementById('editIsPublished').checked = data.exam.is_published;

                        // Load classes for selected subject
                        loadClasses('edit', data.exam.subject_id, data.schoolclass_ids);

                        new bootstrap.Modal(document.getElementById('editExamModal')).show();
                    }
                });
            });
        });

        // Delete single
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                document.getElementById('confirmDeleteBtn').onclick = () => {
                    deleteExam(id);
                    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                };
            });
        });
    }

    // Load classes checkboxes
    function loadClasses(modalType, subjectId, selectedIds = []) {
        if (!subjectId) return;
        fetch(`/exams/subject-classes/${subjectId}`)
            .then(res => res.json())
            .then(classes => {
                const container = document.getElementById(`${modalType}ClassCheckboxes`);
                container.innerHTML = '';
                if (classes.length === 0) {
                    container.innerHTML = '<p class="text-muted">No classes found for this subject.</p>';
                    return;
                }
                classes.forEach(cls => {
                    const checked = selectedIds.includes(cls.schoolclassID) ? 'checked' : '';
                    container.innerHTML += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="schoolclass_ids[]"
                                   value="${cls.schoolclassID}" id="${modalType}Class${cls.schoolclassID}" ${checked}>
                            <label class="form-check-label" for="${modalType}Class${cls.schoolclassID}">
                                ${cls.schoolclass} ${cls.arm_name || ''}
                            </label>
                        </div>
                    `;
                });
            });
    }

    // Subject change → load classes
    document.getElementById('addSubjectSelect')?.addEventListener('change', e => {
        loadClasses('add', e.target.value);
    });

    document.getElementById('editSubjectId')?.addEventListener('change', e => {
        loadClasses('edit', e.target.value);
    });

    // Add Exam Form Submit
    document.getElementById('addExamForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
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
                alert(data.message);
            } else {
                alert('Error: ' + (data.message || 'Validation failed'));
            }
        });
    });

    // Edit Exam Form Submit
    document.getElementById('editExamForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('editExamId').value;
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
                alert(data.message);
            } else {
                alert('Error: ' + (data.message || 'Update failed'));
            }
        });
    });

    // Bulk Delete
    function deleteMultiple() {
        const checked = Array.from(document.querySelectorAll('input[name="chkIds[]"]:checked'))
                            .map(cb => cb.value);

        if (checked.length === 0) return;

        if (!confirm(`Delete ${checked.length} exam(s)?`)) return;

        fetch('{{ route('exams.bulk-destroy') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: checked })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadTable(1);
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    // Single delete helper
    function deleteExam(id) {
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
        });
    }

    // Debounce helper
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

    // Initial attach
    attachEventListeners();
});
</script>

@endsection
