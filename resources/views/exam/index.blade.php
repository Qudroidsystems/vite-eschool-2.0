@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
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
            <!-- End page title -->

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="title">Title</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="description">Description</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="duration">Duration</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="start_time">Start Time</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="end_time">End Time</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="questions">Questions</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = ($exams->currentPage() - 1) * $exams->perPage() @endphp
                                            @forelse ($exams as $exam)
                                                @if($exam->id)
                                                <tr data-url="{{ route('exams.destroy', ['exam' => $exam->id]) }}">
                                                    <td class="id" data-id="{{ $exam->id }}">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="title">{{ $exam->title }}</td>
                                                    <td class="description">{{ Str::limit($exam->description ?? '', 50) }}</td>
                                                    <td class="duration">{{ $exam->duration }} mins</td>
                                                    <td class="start_time">{{ $exam->start_time }}</td>
                                                    <td class="end_time">{{ $exam->end_time }}</td>
                                                    <td class="questions">
                                                        <a href="{{ route('questions.show', $exam->id) }}" class="btn btn-subtle-primary btn-icon btn-sm">View Questions</a>
                                                    </td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update exam')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="{{ $exam->id }}"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete exam')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-url="{{ route('exams.destroy', ['exam' => $exam->id]) }}"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="noresult" style="display: block;">No exams found</td>
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
                                            <a class="page-item pagination-prev {{ $exams->onFirstPage() ? 'disabled' : '' }}" href="javascript:void(0);" data-url="{{ $exams->previousPageUrl() }}">
                                                <i class="mdi mdi-chevron-left align-middle"></i>
                                            </a>
                                            <ul class="pagination listjs-pagination mb-0">
                                                @foreach ($exams->links()->elements[0] as $page => $url)
                                                    <li class="page-item {{ $exams->currentPage() == $page ? 'active' : '' }}">
                                                        <a class="page-link" href="javascript:void(0);" data-url="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <a class="page-item pagination-next {{ $exams->hasMorePages() ? '' : 'disabled' }}" href="javascript:void(0);" data-url="{{ $exams->nextPageUrl() }}">
                                                <i class="mdi mdi-chevron-right align-middle"></i>
                                            </a>
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
                                <div class="mb-3">
                                    <label class="form-label required">Select Term</label>
                                    <select name="termid" class="form-control" required>
                                        <option value="" selected>Select Term</option>
                                        @foreach ($terms as $term => $name)
                                            <option value="{{ $name->id }}">{{ $name->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Select Session</label>
                                    <select name="session" class="form-control" required>
                                        <option value="" selected>Select Session</option>
                                        @foreach ($session as $schoolsession => $name)
                                            <option value="{{ $name->id }}">{{ $name->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Select Subject</label>
                                    <select name="subject_id" class="form-control" required>
                                        <option value="" selected>Select Subject</option>
                                        @foreach ($mysubjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->subject }} ({{ $subject->subjectcode }}) - {{ $subject->schoolclass }} {{ $subject->arm }} {{ $subject->term }} {{ $subject->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Select Class</label>
                                    <select name="schoolclass_id" class="form-control" required>
                                        <option value="" selected>Select Class</option>
                                        @foreach ($myclass as $class)
                                            <option value="{{ $class->schoolclassID }}">{{ $class->schoolclass }} {{ $class->arm_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="publishStatus">
                                        <label class="form-check-label" for="publishStatus">Publish exam immediately</label>
                                    </div>
                                    <div class="text-muted fs-7 mt-1">If not checked, the exam will be saved as a draft.</div>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
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
                            @method('PUT')
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
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
                                <div class="mb-3">
                                    <label class="form-label required">Select Term</label>
                                    <select name="termid" id="edit-termid" class="form-control" required>
                                        <option value="" selected>Select Term</option>
                                        @foreach ($terms as $term => $name)
                                            <option value="{{ $name->id }}">{{ $name->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Select Session</label>
                                    <select name="session" id="edit-session" class="form-control" required>
                                        <option value="" selected>Select Session</option>
                                        @foreach ($session as $schoolsession => $name)
                                            <option value="{{ $name->id }}">{{ $name->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Select Subject</label>
                                    <select name="subject_id" id="edit-subject_id" class="form-control" required>
                                        <option value="" selected>Select Subject</option>
                                        @foreach ($mysubjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->subject }} ({{ $subject->subjectcode }}) - {{ $subject->schoolclass }} {{ $subject->arm }} {{ $subject->term }} {{ $subject->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Select Class</label>
                                    <select name="schoolclass_id" id="edit-schoolclass_id" class="form-control" required>
                                        <option value="" selected>Select Class</option>
                                        @foreach ($myclass as $class)
                                            <option value="{{ $class->schoolclassID }}">{{ $class->schoolclass }} {{ $class->arm_name }}</option>
                                        @endforeach
                                    </select>
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

            <!-- Delete Confirmation Modal -->
            @can('Delete exam')
            <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <h4>Are you sure?</h4>
                            <p>You won't be able to revert this!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="delete-record">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        <!-- End Page-content -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#kt_exams_table tbody');
    const searchInput = document.querySelector('.search');
    const checkAll = document.getElementById('checkAll');
    const removeActions = document.getElementById('remove-actions');
    const paginationItems = document.querySelectorAll('[data-url]');
    const currentPage = {{ $exams->currentPage() }};
    let currentSort = { field: null, direction: 'asc' };
    let searchTerm = '';
    const baseUrl = '{{ route('exams.index') }}';

    // Handle checkboxes
    checkAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="chk_child"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
        toggleRemoveActions();
    });

    tableBody.addEventListener('change', function(e) {
        if (e.target.name === 'chk_child') {
            toggleRemoveActions();
        }
    });

    function toggleRemoveActions() {
        const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
        removeActions.classList.toggle('d-none', checkedBoxes.length === 0);
    }

    // Search functionality
    searchInput.addEventListener('input', debounce(function(e) {
        searchTerm = e.target.value;
        loadData(1);
    }, 300));

    // Sorting
    document.querySelectorAll('.sort').forEach(th => {
        th.addEventListener('click', function() {
            const field = this.dataset.sort;
            if (currentSort.field === field) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = field;
                currentSort.direction = 'asc';
            }
            loadData(1);
        });
    });

    // Pagination
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination-prev') || e.target.closest('.pagination-next') || e.target.closest('.page-link')) {
            e.preventDefault();
            const link = e.target.closest('a');
            if (!link.classList.contains('disabled') && link.dataset.url) {
                const url = new URL(link.dataset.url, window.location.origin);
                const page = url.searchParams.get('page') || 1;
                loadData(page);
            }
        }
    });

    // Load data function (AJAX)
    function loadData(page = 1) {
        const params = new URLSearchParams({
            page: page,
            search: searchTerm,
            sort: currentSort.field || '',
            direction: currentSort.direction || ''
        });

        fetch(`${baseUrl}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateTable(data);
            updatePagination(data);
        })
        .catch(error => console.error('Error:', error));
    }

    function updateTable(data) {
        const tbody = document.querySelector('tbody');
        tbody.innerHTML = '';
        let i = (data.current_page - 1) * data.per_page + 1;

        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="noresult" style="display: block;">No exams found</td></tr>';
            return;
        }

        data.data.forEach(exam => {
            if (!exam.id) return; // Skip if no ID
            const destroyUrl = `/exams/${exam.id}`;
            const questionsUrl = `/questions/${exam.id}/show`; // Adjust if needed
            const description = exam.description ? (exam.description.length > 50 ? exam.description.substring(0, 50) + '...' : exam.description) : '';
            const row = `
                <tr data-url="${destroyUrl}">
                    <td class="id" data-id="${exam.id}">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="chk_child" />
                        </div>
                    </td>
                    <td class="sn">${i++}</td>
                    <td class="title">${exam.title}</td>
                    <td class="description">${description}</td>
                    <td class="duration">${exam.duration} mins</td>
                    <td class="start_time">${exam.start_time}</td>
                    <td class="end_time">${exam.end_time}</td>
                    <td class="questions">
                        <a href="${questionsUrl}" class="btn btn-subtle-primary btn-icon btn-sm">View Questions</a>
                    </td>
                    <td>
                        <ul class="d-flex gap-2 list-unstyled mb-0">
                            <li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="${exam.id}"><i class="ph-pencil"></i></a></li>
                            <li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-url="${destroyUrl}"><i class="ph-trash"></i></a></li>
                        </ul>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Re-attach event listeners after update
        attachEventListeners();
    }

    function updatePagination(data) {
        const showingFirst = document.querySelector('#pagination-element .fw-semibold:nth-of-type(1)');
        const showingLast = document.querySelector('#pagination-element .fw-semibold:nth-of-type(2)');
        const totalSpan = document.querySelector('#pagination-element .fw-semibold:nth-of-type(3)');
        const badge = document.querySelector('.badge');
        const prev = document.querySelector('.pagination-prev');
        const next = document.querySelector('.pagination-next');

        showingFirst.textContent = data.from || 0;
        showingLast.textContent = data.to || 0;
        totalSpan.textContent = data.total;
        badge.textContent = data.total;

        prev.classList.toggle('disabled', data.current_page === 1);
        prev.dataset.url = data.prev_page_url || '';
        next.classList.toggle('disabled', !data.next_page_url);
        next.dataset.url = data.next_page_url || '';

        // Update page links
        const paginationUl = document.querySelector('.listjs-pagination');
        paginationUl.innerHTML = '';
        for (let i = 1; i <= data.last_page; i++) {
            const params = new URLSearchParams({
                page: i,
                search: searchTerm,
                sort: currentSort.field || '',
                direction: currentSort.direction || ''
            });
            const fullUrl = `${baseUrl}?${params}`;
            const li = document.createElement('li');
            li.className = `page-item ${i === data.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="javascript:void(0);" data-url="${fullUrl}">${i}</a>`;
            paginationUl.appendChild(li);
        }
    }

    // Attach event listeners
    function attachEventListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!id) return;
                fetch(`/exams/${id}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit-id-field').value = data.exam.id;
                        document.getElementById('edit-title').value = data.exam.title;
                        document.getElementById('edit-description').value = data.exam.description || '';
                        document.getElementById('edit-duration').value = data.exam.duration;
                        document.getElementById('edit-start_time').value = data.exam.start_time;
                        document.getElementById('edit-end_time').value = data.exam.end_time;
                        document.getElementById('edit-termid').value = data.exam.termid;
                        document.getElementById('edit-session').value = data.exam.session;
                        document.getElementById('edit-subject_id').value = data.exam.subject_id;
                        document.getElementById('edit-schoolclass_id').value = data.exam.schoolclass_id;
                        document.getElementById('edit-publishStatus').checked = data.exam.is_published == 1;
                        new bootstrap.Modal(document.getElementById('editModal')).show();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        // Delete buttons
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                if (!url) return;
                document.getElementById('delete-record').onclick = function() {
                    deleteExam(url);
                    bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal')).hide();
                };
                new bootstrap.Modal(document.getElementById('deleteRecordModal')).show();
            });
        });
    }

    // Initial attach
    attachEventListeners();

    // Add form submission
    document.getElementById('add-exam-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = document.getElementById('add-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding...';
        submitBtn.disabled = true;

        fetch('{{ route('exams.store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addExamModal')).hide();
                this.reset();
                showAlert('success', data.message);
                loadData(1);
            } else {
                showFormErrors(this, data.errors || {});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while adding the exam.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // Edit form submission
    document.getElementById('edit-exam-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id-field').value;
        if (!id) return;
        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        const submitBtn = document.getElementById('update-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;

        fetch(`/exams/${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                showAlert('success', data.message);
                loadData(currentPage);
            } else {
                showFormErrors(this, data.errors || {});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while updating the exam.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // Delete function
    function deleteExam(url, isMultiple = false) {
        if (!url && !isMultiple) return;
        const ids = isMultiple ? Array.from(document.querySelectorAll('input[name="chk_child"]:checked')).map(cb => cb.closest('td').dataset.id).filter(id => id) : [];
        if (isMultiple && ids.length === 0) return;
        const deleteUrl = isMultiple ? '/exams/bulk-destroy' : url; // Assume bulk route exists

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: isMultiple ? JSON.stringify({ ids: ids }) : null
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                checkAll.checked = false;
                loadData(currentPage);
            } else {
                showAlert('danger', data.message || 'An error occurred while deleting.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while deleting.');
        });
    }

    // Bulk delete
    window.deleteMultiple = function() {
        const checked = document.querySelectorAll('input[name="chk_child"]:checked');
        if (checked.length === 0) return;

        document.getElementById('delete-record').onclick = function() {
            deleteExam(null, true);
            bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal')).hide();
        };
        new bootstrap.Modal(document.getElementById('deleteRecordModal')).show();
    };

    // Helper functions
    function showFormErrors(form, errors) {
        const alert = form.querySelector('.alert');
        alert.classList.remove('d-none');
        alert.innerHTML = Object.values(errors).flat().join('<br>');
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(alertDiv, document.getElementById('examsList'));
        setTimeout(() => alertDiv.remove(), 5000);
    }

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

    // CSRF token if not present
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
});
</script>

@endsection