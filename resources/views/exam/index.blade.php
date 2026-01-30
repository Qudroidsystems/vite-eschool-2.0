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
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="title">Title</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="description">Description</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="duration">Duration</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="start_time">Start Time</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="end_time">End Time</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="questions">Questions</th>
                                                <th class="min-w-100px">View Students</th>
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
                                                    <td class="start_time">{{ $exam->formatted_start_time ?? $exam->start_time }}</td>
                                                    <td class="end_time">{{ $exam->formatted_end_time ?? $exam->end_time }}</td>
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
                                                    <td colspan="10" class="noresult" style="display: block;">No exams found</td>
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
                                                @if(isset($exams->links()->elements[0]))
                                                    @foreach ($exams->links()->elements[0] as $page => $url)
                                                        <li class="page-item {{ $exams->currentPage() == $page ? 'active' : '' }}">
                                                            <a class="page-link" href="javascript:void(0);" data-url="{{ $url }}">{{ $page }}</a>
                                                        </li>
                                                    @endforeach
                                                @endif
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
                                                data-sessionid="{{ $subject->sessionid }}"
                                                data-class="{{ $subject->schoolclass }}"
                                                data-arm="{{ $subject->arm }}">
                                                {{ $subject->subject }} ({{ $subject->subjectcode }}) - {{ $subject->term }} {{ $subject->session }} - {{ $subject->schoolclass }} {{ $subject->arm ? '(' . $subject->arm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Select Classes</label>
                                    <div id="addClassContainer" class="border rounded p-3 bg-light" style="max-height: 240px; overflow-y: auto;">
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
                                                data-sessionid="{{ $subject->sessionid }}"
                                                data-class="{{ $subject->schoolclass }}"
                                                data-arm="{{ $subject->arm }}">
                                                {{ $subject->subject }} ({{ $subject->subjectcode }}) - {{ $subject->term }} {{ $subject->session }} - {{ $subject->schoolclass }} {{ $subject->arm ? '(' . $subject->arm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Select Classes</label>
                                    <div id="editClassContainer" class="border rounded p-3 bg-light" style="max-height: 240px; overflow-y: auto;">
                                        <p class="text-muted text-center mb-0">Loading classes...</p>
                                    </div>
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

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Custom styles for dropdown filtering */
option[data-termid][data-sessionid] {
    padding: 8px 12px;
}

option[style*="display: none"] {
    display: none !important;
}

option:disabled {
    font-weight: bold;
    background-color: #f8f9fa;
    color: #495057;
    padding: 10px 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#kt_exams_table tbody');
    const searchInput = document.querySelector('.search');
    const checkAll = document.getElementById('checkAll');
    const removeActions = document.getElementById('remove-actions');
    const paginationItems = document.querySelectorAll('[data-url]');
    let currentPage = {{ $exams->currentPage() }};
    let searchTerm = '';
    const baseUrl = '{{ route('exams.index') }}';

    // CSRF Token setup
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Initialize
    initEventListeners();
    initModals();

    // Handle checkboxes
    checkAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="chk_child"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
        toggleRemoveActions();
    });

    function toggleRemoveActions() {
        const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
        removeActions.classList.toggle('d-none', checkedBoxes.length === 0);
    }

    // Search functionality
    searchInput.addEventListener('input', debounce(function(e) {
        searchTerm = e.target.value.trim();
        loadData(1);
    }, 500));

    // Pagination
    document.addEventListener('click', function(e) {
        const prevBtn = e.target.closest('.pagination-prev');
        const nextBtn = e.target.closest('.pagination-next');
        const pageLink = e.target.closest('.page-link');

        if (prevBtn && !prevBtn.classList.contains('disabled') && prevBtn.dataset.url) {
            e.preventDefault();
            const url = new URL(prevBtn.dataset.url, window.location.origin);
            const page = url.searchParams.get('page') || 1;
            loadData(page);
        } else if (nextBtn && !nextBtn.classList.contains('disabled') && nextBtn.dataset.url) {
            e.preventDefault();
            const url = new URL(nextBtn.dataset.url, window.location.origin);
            const page = url.searchParams.get('page') || 1;
            loadData(page);
        } else if (pageLink && pageLink.dataset.url) {
            e.preventDefault();
            const url = new URL(pageLink.dataset.url, window.location.origin);
            const page = url.searchParams.get('page') || 1;
            loadData(page);
        }
    });

    // Load data function (AJAX)
    function loadData(page = 1) {
        const params = new URLSearchParams({
            page: page,
            search: searchTerm
        });

        fetch(`${baseUrl}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateTable(data);
            updatePagination(data);
            currentPage = data.current_page;
        })
        .catch(error => {
            console.error('Error:', error);
            showSweetAlert('error', 'Error', 'Failed to load data. Please try again.');
        });
    }

    function updateTable(data) {
        const tbody = document.querySelector('tbody');
        tbody.innerHTML = '';

        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="noresult text-center py-4 text-muted">No exams found</td></tr>';
            return;
        }

        let i = (data.current_page - 1) * data.per_page + 1;

        data.data.forEach(exam => {
            if (!exam.id) return;

            const description = exam.description ?
                (exam.description.length > 50 ? exam.description.substring(0, 50) + '...' : exam.description) : '';

            const formattedStartTime = exam.formatted_start_time || formatDateTime(exam.start_time);
            const formattedEndTime = exam.formatted_end_time || formatDateTime(exam.end_time);

            const row = `
                <tr data-url="/exams/${exam.id}">
                    <td class="id" data-id="${exam.id}">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="chk_child" />
                        </div>
                    </td>
                    <td class="sn">${i++}</td>
                    <td class="title">${escapeHtml(exam.title)}</td>
                    <td class="description">${escapeHtml(description)}</td>
                    <td class="duration">${exam.duration} mins</td>
                    <td class="start_time">${formattedStartTime}</td>
                    <td class="end_time">${formattedEndTime}</td>
                    <td class="questions">
                        <a href="/questions/${exam.id}" class="btn btn-subtle-primary btn-icon btn-sm">View Questions</a>
                    </td>
                    <td>
                        <a href="/exams/${exam.id}/students" class="btn btn-subtle-info btn-icon btn-sm"><i class="ph-users"></i></a>
                    </td>
                    <td>
                        <ul class="d-flex gap-2 list-unstyled mb-0">
                            <li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="${exam.id}"><i class="ph-pencil"></i></a></li>
                            <li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-url="/exams/${exam.id}"><i class="ph-trash"></i></a></li>
                        </ul>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Re-attach event listeners
        attachTableEventListeners();
    }

    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return '';
        const date = new Date(dateTimeString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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

        // Create page links
        let startPage = Math.max(1, data.current_page - 2);
        let endPage = Math.min(data.last_page, startPage + 4);

        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const params = new URLSearchParams({
                page: i,
                search: searchTerm
            });
            const fullUrl = `${baseUrl}?${params}`;
            const li = document.createElement('li');
            li.className = `page-item ${i === data.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="javascript:void(0);" data-url="${fullUrl}">${i}</a>`;
            paginationUl.appendChild(li);
        }
    }

    // Initialize modal event listeners
    function initModals() {
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
        const editTerm = document.getElementById('edit-termid');
        const editSession = document.getElementById('edit-session');
        const editSubject = document.getElementById('edit-subject_id');

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
    }

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
            const containerId = subjectSelect.id === 'addSubject' ? 'addClassContainer' : 'editClassContainer';
            document.getElementById(containerId).innerHTML =
                '<p class="text-muted text-center mb-0">Select a subject first...</p>';
        }
    }

    // Function to load classes for a subject
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
            if (data.success && data.classes.length > 0) {
                let html = '<div class="row">';

                data.classes.forEach(cls => {
                    const isChecked = mode === 'edit' && data.selectedClasses && data.selectedClasses.includes(parseInt(cls.id));
                    html += `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="schoolclass_ids[]"
                                       value="${cls.id}"
                                       id="class_${mode}_${cls.id}"
                                       ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label" for="class_${mode}_${cls.id}">
                                    ${escapeHtml(cls.schoolclass)} ${cls.arm ? '(' + escapeHtml(cls.arm) + ')' : ''}
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

    // Initialize event listeners
    function initEventListeners() {
        attachTableEventListeners();

        // Add form submission
        const addForm = document.getElementById('add-exam-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleFormSubmit(this, '{{ route('exams.store') }}', 'POST', 'add');
            });
        }

        // Edit form submission
        const editForm = document.getElementById('edit-exam-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('edit-id-field').value;
                if (!id) return;
                handleFormSubmit(this, `/exams/${id}`, 'PUT', 'edit');
            });
        }

        // Single delete
        document.getElementById('delete-record')?.addEventListener('click', function() {
            const urlToDelete = this.dataset.url;
            if (urlToDelete) {
                deleteItem(urlToDelete, false);
            }
        });
    }

    // Attach table event listeners
    function attachTableEventListeners() {
        // Checkbox changes in table body
        tableBody.addEventListener('change', function(e) {
            if (e.target.name === 'chk_child') {
                toggleRemoveActions();
            }
        });

        // Edit buttons
        document.querySelectorAll('.edit-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!id) return;
                loadExamForEdit(id);
            });
        });

        // Delete buttons
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                if (!url) return;
                showDeleteConfirmation(url, false);
            });
        });
    }

    // Load exam data for editing
    function loadExamForEdit(id) {
        fetch(`/exams/${id}/edit`, {
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
                populateEditForm(data);
                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            } else {
                showSweetAlert('error', 'Error', 'Failed to load exam data.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSweetAlert('error', 'Error', 'Failed to load exam data.');
        });
    }

    function populateEditForm(data) {
        const exam = data.exam;

        document.getElementById('edit-id-field').value = exam.id;
        document.getElementById('edit-title').value = exam.title;
        document.getElementById('edit-description').value = exam.description || '';
        document.getElementById('edit-duration').value = exam.duration;

        // Format datetime for input fields
        const startTime = new Date(exam.start_time);
        const endTime = new Date(exam.end_time);

        document.getElementById('edit-start_time').value = startTime.toISOString().slice(0, 16);
        document.getElementById('edit-end_time').value = endTime.toISOString().slice(0, 16);

        document.getElementById('edit-termid').value = exam.termid;
        document.getElementById('edit-session').value = exam.session;
        document.getElementById('edit-publishStatus').checked = exam.is_published == 1;

        // Set subject value
        const subjectSelect = document.getElementById('edit-subject_id');
        subjectSelect.value = exam.subject_id;

        // Apply filtering based on selected term and session
        filterSubjects(exam.termid, exam.session, subjectSelect);

        // Load classes for this subject
        loadClassesForEdit(exam.subject_id, data.schoolclass_ids || []);
    }

    function loadClassesForEdit(subjectTeacherId, selectedClassIds = []) {
        const container = document.getElementById('editClassContainer');

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
                                    ${escapeHtml(cls.schoolclass)} ${cls.arm ? '(' + escapeHtml(cls.arm) + ')' : ''}
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

    // Handle form submission
    function handleFormSubmit(form, url, method, type) {
        const submitBtn = form.querySelector(type === 'add' ? '#add-btn' : '#update-btn');
        const originalText = submitBtn.textContent;
        const formData = new FormData(form);

        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        // Validate class selection
        const classCheckboxes = form.querySelectorAll('input[name="schoolclass_ids[]"]:checked');
        if (classCheckboxes.length === 0) {
            showSweetAlert('error', 'Error', 'Please select at least one class.');
            return;
        }

        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(JSON.stringify(err));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const modalId = type === 'add' ? 'addExamModal' : 'editModal';
                const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                if (modal) modal.hide();

                showSweetAlert('success', 'Success', data.message);
                form.reset();

                // Clear class container
                if (type === 'add') {
                    document.getElementById('addClassContainer').innerHTML =
                        '<p class="text-muted text-center mb-0">Select a subject first...</p>';
                }

                // Reload data after a short delay
                setTimeout(() => loadData(currentPage), 1000);
            } else {
                showFormErrors(form, data.errors || {});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            try {
                const errData = JSON.parse(error.message);
                showFormErrors(form, errData.errors || {});
            } catch (e) {
                showSweetAlert('error', 'Error', 'An error occurred. Please try again.');
            }
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    // Show delete confirmation
    function showDeleteConfirmation(url, isMultiple = false) {
        const title = isMultiple ? 'Delete Selected Exams' : 'Delete Exam';
        const text = isMultiple ?
            'Are you sure you want to delete the selected exams?' :
            'Are you sure you want to delete this exam? This action cannot be undone.';

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItem(url, isMultiple);
            }
        });
    }

    // Delete item
    function deleteItem(url, isMultiple = false) {
        const ids = isMultiple ?
            Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
                .map(cb => cb.closest('td').dataset.id)
                .filter(id => id) :
            null;

        const deleteUrl = isMultiple ? '/exams/bulk-destroy' : url;
        const deleteData = isMultiple ? { ids: ids } : null;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: isMultiple ? JSON.stringify(deleteData) : null
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSweetAlert('success', 'Success', data.message);

                // Reset checkboxes
                checkAll.checked = false;
                document.querySelectorAll('input[name="chk_child"]').forEach(cb => cb.checked = false);
                toggleRemoveActions();

                // Reload data
                loadData(currentPage);

                // Close delete modal if open
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal'));
                if (deleteModal) deleteModal.hide();
            } else {
                showSweetAlert('error', 'Error', data.message || 'Failed to delete.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSweetAlert('error', 'Error', 'Failed to delete. Please try again.');
        });
    }

    // Bulk delete
    window.deleteMultiple = function() {
        const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
        if (checkedBoxes.length === 0) {
            showSweetAlert('warning', 'No Selection', 'Please select at least one exam to delete.');
            return;
        }

        showDeleteConfirmation(null, true);
    };

    // Helper functions
    function showFormErrors(form, errors) {
        const errorContainer = form.querySelector('.alert');
        if (errorContainer) {
            errorContainer.classList.remove('d-none');
            errorContainer.innerHTML = Object.values(errors).flat().join('<br>');
        }
    }

    function showSweetAlert(icon, title, text) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

@endsection
