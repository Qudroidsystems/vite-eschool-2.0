@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">All Questions Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                                <li class="breadcrumb-item active">All Questions</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Search Box -->
                                <div class="col-md-4">
                                    <div class="search-box">
                                        <input type="text" class="form-control search"
                                               placeholder="Search questions or options..."
                                               value="{{ request('search', '') }}">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>

                                <!-- Exam Filter -->
                                <div class="col-md-3">
                                    <select class="form-control" id="exam-filter">
                                        <option value="">All Exams</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}"
                                                {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                                {{ $exam->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Class Filter -->
                                <div class="col-md-3">
                                    <select class="form-control" id="class-filter">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}"
                                                {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->schoolclass }}
                                                @if($class->armRelation)
                                                    ({{ $class->armRelation->arm }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Type Filter -->
                                <div class="col-md-2">
                                    <select class="form-control" id="type-filter">
                                        <option value="">All Types</option>
                                        <option value="mcq" {{ request('type') == 'mcq' ? 'selected' : '' }}>MCQ</option>
                                        <option value="true_false" {{ request('type') == 'true_false' ? 'selected' : '' }}>True/False</option>
                                        <option value="short_answer" {{ request('type') == 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-outline-danger d-none" id="bulk-delete-btn">
                                <i class="ri-delete-bin-line me-1"></i> Delete Selected
                            </button>
                            <button class="btn btn-outline-primary d-none" id="bulk-move-btn">
                                <i class="ri-file-copy-line me-1"></i> Move to Exam
                            </button>
                            <button class="btn btn-outline-success d-none" id="bulk-reusable-btn">
                                <i class="ri-repeat-line me-1"></i> Mark as Reusable
                            </button>
                        </div>

                        <div class="d-flex gap-2">
                            <!-- Export Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-download-line me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item export-action" href="#" data-type="pdf">
                                        <i class="ri-file-pdf-line me-2"></i> Export to PDF
                                    </a></li>
                                    <li><a class="dropdown-item export-action" href="#" data-type="word">
                                        <i class="ri-file-word-line me-2"></i> Export to Word
                                    </a></li>
                                    <li><a class="dropdown-item export-action" href="#" data-type="excel">
                                        <i class="ri-file-excel-line me-2"></i> Export to Excel
                                    </a></li>
                                </ul>
                            </div>

                            <!-- Import Button -->
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="ri-upload-line me-1"></i> Import
                            </button>

                            <!-- Add Question Button -->
                            <button type="button" class="btn btn-success" id="add-question-btn">
                                <i class="ri-add-line me-1"></i> Add Question
                            </button>

                            <!-- View Reusable Questions -->
                            <button type="button" class="btn btn-warning" id="view-reusable-btn">
                                <i class="ri-repeat-line me-1"></i> Reusable Questions
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Questions List ({{ $questions->total() }})</h5>
                                <div>
                                    <span class="badge bg-info">MCQ: {{ $questions->where('type', 'mcq')->count() }}</span>
                                    <span class="badge bg-warning ms-1">T/F: {{ $questions->where('type', 'true_false')->count() }}</span>
                                    <span class="badge bg-success ms-1">Short: {{ $questions->where('type', 'short_answer')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="questions-table">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="select-all">
                                                </div>
                                            </th>
                                            <th width="80">#</th>
                                            <th>Question Text</th>
                                            <th width="120">Type</th>
                                            <th>Exam</th>
                                            <th>Class</th>
                                            <th width="80">Marks</th>
                                            <th width="100">Image</th>
                                            <th width="100">Reusable</th>
                                            <th width="180">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-questions">
                                        @forelse($questions as $index => $question)
                                            <tr data-id="{{ $question->id }}" data-exam-id="{{ $question->exam_id }}">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input question-checkbox"
                                                               type="checkbox"
                                                               value="{{ $question->id }}">
                                                    </div>
                                                </td>
                                                <td class="handle">
                                                    <i class="ri-draggable" style="cursor: move;"></i>
                                                    {{ ($questions->currentPage() - 1) * $questions->perPage() + $index + 1 }}
                                                </td>
                                                <td>
                                                    <div class="question-text">
                                                        {!! Str::limit(strip_tags($question->question_text), 100) !!}
                                                    </div>
                                                    @if($question->options->count() > 0)
                                                        <small class="text-muted">
                                                            Options: {{ $question->options->count() }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge
                                                        @if($question->type == 'mcq') bg-info
                                                        @elseif($question->type == 'true_false') bg-warning
                                                        @else bg-success
                                                        @endif">
                                                        {{ strtoupper($question->type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('questions.show', $question->exam_id) }}"
                                                       class="text-primary">
                                                        {{ Str::limit($question->exam->title, 30) }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if($question->exam->schoolclass)
                                                        <span class="badge bg-secondary">
                                                            {{ $question->exam->schoolclass->schoolclass }}
                                                            @if($question->exam->schoolclass->armRelation)
                                                                ({{ $question->exam->schoolclass->armRelation->arm }})
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark">No Class</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $question->marks }}</span>
                                                </td>
                                                <td>
                                                    @if($question->image)
                                                        <a href="{{ asset('storage/' . $question->image) }}"
                                                           target="_blank"
                                                           class="btn btn-sm btn-outline-secondary">
                                                            <i class="ri-image-line"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($question->is_reusable)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-light text-dark">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary view-question"
                                                                data-id="{{ $question->id }}">
                                                            <i class="ri-eye-line"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning edit-question"
                                                                data-id="{{ $question->id }}"
                                                                data-exam-id="{{ $question->exam_id }}">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info duplicate-question"
                                                                data-id="{{ $question->id }}">
                                                            <i class="ri-file-copy-line"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger delete-question"
                                                                data-id="{{ $question->id }}">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="ri-question-line display-4"></i>
                                                        <h5>No questions found</h5>
                                                        <p>Start by adding your first question or importing from a file.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($questions->hasPages())
                                <div class="row mt-3">
                                    <div class="col-12">
                                        {{ $questions->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
    </div>
</div>

<!-- Include all modals -->
@include('questions.partials.exam_selection_modal')
@include('questions.partials.question_form_modal')
@include('questions.partials.import_modal')
@include('questions.partials.move_exam_modal')
@include('questions.partials.reusable_questions_modal')
@include('questions.partials.view_question_modal')
@include('questions.partials.duplicate_modal')

<!-- Include Quill.js CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.handle {
    cursor: move;
}

.question-checkbox:checked + .form-check-label {
    background-color: #e7f1ff;
}

.sortable-ghost {
    opacity: 0.5;
    background-color: #f8f9fa;
}

.sortable-chosen {
    background-color: #e7f1ff;
}

.question-text {
    line-height: 1.5;
    max-height: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-weight: 500;
}

#reusable-questions-list .list-group-item:hover {
    background-color: #f8f9fa;
}

#reusable-questions-list .form-check-input:checked + .form-check-label {
    background-color: #e7f1ff;
    border-radius: 0.375rem;
}

.exam-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
}

.exam-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.exam-card.border-primary {
    border-color: #0d6efd !important;
}

.exam-card .form-check-input {
    margin-top: 0.3rem;
}

.class-group h6 {
    color: #495057;
    font-size: 0.9rem;
}

/* Modal styles */
.modal-backdrop {
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;
}

.modal-xl {
    max-width: 90% !important;
}

#question-text-editor, #short-answer-editor {
    min-height: 150px;
}

.ql-toolbar {
    border-top-left-radius: 0.375rem !important;
    border-top-right-radius: 0.375rem !important;
}

.ql-container {
    border-bottom-left-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
}

#image-preview img {
    max-width: 200px;
    max-height: 200px;
    object-fit: contain;
}

.option-field {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}

.option-field:hover {
    background-color: #e9ecef;
}

.form-check-input.is-correct:checked {
    background-color: #198754;
    border-color: #198754;
}

#image-preview {
    display: flex;
    align-items: center;
}

.required:after {
    content: " *";
    color: #dc3545;
}
</style>

<!-- Include required libraries -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Document ready - Question Management page loaded');

    let selectedQuestions = [];
    let currentDuplicateQuestionId = null;
    let selectedExams = [];
    let questionTextEditor = null;
    let shortAnswerEditor = null;

    // Initialize Sortable for drag-drop
    if (document.getElementById('sortable-questions')) {
        console.log('Initializing SortableJS');
        const sortable = Sortable.create(document.getElementById('sortable-questions'), {
            handle: '.handle',
            animation: 150,
            onEnd: function(evt) {
                const examId = $(evt.item).data('exam-id');
                console.log('Question reordered for exam ID:', examId);
                const questions = [];

                $('#sortable-questions tr[data-exam-id="' + examId + '"]').each(function(index) {
                    questions.push({
                        id: $(this).data('id'),
                        order: index + 1
                    });
                });

                // Update order via AJAX
                $.ajax({
                    url: '{{ route("questions.reorder") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        exam_id: examId,
                        questions: questions
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccess('Questions reordered successfully');
                        }
                    }
                });
            }
        });
    }

    // Helper function to show success message
    function showSuccess(message) {
        console.log('Success:', message);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Helper function to show error message
    function showError(message) {
        console.error('Error:', message);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

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

    // Filter functionality
    function applyFilters() {
        console.log('Applying filters');
        const params = new URLSearchParams();

        const examId = $('#exam-filter').val();
        const classId = $('#class-filter').val();
        const type = $('#type-filter').val();
        const search = $('.search').val();

        if (examId) params.set('exam_id', examId);
        if (classId) params.set('class_id', classId);
        if (type) params.set('type', type);
        if (search) params.set('search', search);

        console.log('Redirecting with params:', params.toString());
        window.location.href = '{{ route("questions.all") }}?' + params.toString();
    }

    $('#exam-filter, #class-filter, #type-filter').change(applyFilters);
    $('.search').on('keyup', debounce(applyFilters, 500));

    // Bulk selection
    $('#select-all').change(function() {
        console.log('Select all changed:', this.checked);
        $('.question-checkbox').prop('checked', this.checked);
        updateBulkButtons();
    });

    $('.question-checkbox').change(function() {
        console.log('Question checkbox changed:', this.value, this.checked);
        if (!this.checked) {
            $('#select-all').prop('checked', false);
        }
        updateBulkButtons();
    });

    function updateBulkButtons() {
        selectedQuestions = $('.question-checkbox:checked').map(function() {
            return this.value;
        }).get();

        console.log('Selected questions:', selectedQuestions);

        if (selectedQuestions.length > 0) {
            $('#bulk-delete-btn, #bulk-move-btn, #bulk-reusable-btn').removeClass('d-none');
            $('#selected-questions').val(selectedQuestions.join(','));
            $('#move-count').text(selectedQuestions.length);
        } else {
            $('#bulk-delete-btn, #bulk-move-btn, #bulk-reusable-btn').addClass('d-none');
        }
    }

    // Bulk delete
    $('#bulk-delete-btn').click(function() {
        if (selectedQuestions.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${selectedQuestions.length} question(s). This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Bulk delete confirmed for:', selectedQuestions);
                $.ajax({
                    url: '{{ route("questions.bulk.update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: selectedQuestions,
                        action: 'delete'
                    },
                    success: function(response) {
                        console.log('Bulk delete response:', response);
                        if (response.success) {
                            showSuccess(response.message);
                            selectedQuestions.forEach(function(id) {
                                $('tr[data-id="' + id + '"]').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                            selectedQuestions = [];
                            updateBulkButtons();
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Bulk delete error:', status, error, xhr.responseText);
                        showError('An error occurred while deleting questions');
                    }
                });
            }
        });
    });

    // Bulk move
    $('#bulk-move-btn').click(function() {
        if (selectedQuestions.length === 0) {
            showError('Please select questions to move');
            return;
        }

        console.log('Opening move exam modal for', selectedQuestions.length, 'questions');
        $('#move-count').text(selectedQuestions.length);
        $('#selected-questions').val(selectedQuestions.join(','));
        $('#moveExamModal').modal('show');
    });

    // Move exam form submission
    $('#move-exam-form').submit(function(e) {
        e.preventDefault();

        const targetExamId = $('#target-exam-select').val();
        if (!targetExamId) {
            showError('Please select a target exam');
            return;
        }

        console.log('Moving questions to exam:', targetExamId);

        // Show loading
        Swal.fire({
            title: 'Moving Questions',
            text: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("questions.bulk.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_ids: selectedQuestions,
                action: 'change_exam',
                data: { exam_id: targetExamId }
            },
            success: function(response) {
                console.log('Move response:', response);
                Swal.close();
                if (response.success) {
                    showSuccess(response.message);
                    $('#moveExamModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Move error:', status, error, xhr.responseText);
                Swal.close();
                showError('An error occurred while moving questions');
            }
        });
    });

    // Bulk mark as reusable
    $('#bulk-reusable-btn').click(function() {
        if (selectedQuestions.length === 0) return;

        Swal.fire({
            title: 'Mark as Reusable',
            text: `Mark ${selectedQuestions.length} question(s) as reusable?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, mark them',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Mark as reusable:', selectedQuestions);
                $.ajax({
                    url: '{{ route("questions.bulk.update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: selectedQuestions,
                        action: 'mark_reusable'
                    },
                    success: function(response) {
                        console.log('Reusable response:', response);
                        if (response.success) {
                            showSuccess(response.message);
                            selectedQuestions.forEach(function(id) {
                                const badge = $('tr[data-id="' + id + '"]').find('td:nth-child(9) .badge');
                                badge.removeClass('bg-light text-dark').addClass('bg-success').text('Yes');
                            });
                            selectedQuestions = [];
                            updateBulkButtons();
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Reusable error:', status, error, xhr.responseText);
                        showError('An error occurred');
                    }
                });
            }
        });
    });

    // Export functionality
    $('.export-action').click(function(e) {
        e.preventDefault();
        const type = $(this).data('type');
        console.log('Export type:', type);

        // Collect current filter parameters
        const params = new URLSearchParams(window.location.search);

        if (type === 'pdf') {
            window.open('{{ route("questions.export.pdf") }}?' + params.toString(), '_blank');
        } else if (type === 'word') {
            window.open('{{ route("questions.export.word") }}?' + params.toString(), '_blank');
        } else if (type === 'excel') {
            showError('Excel export is not yet implemented');
        }
    });

    // Add question button
    $('#add-question-btn').click(function() {
        console.log('Add question button clicked');
        loadExamsForSelection();
        $('#examSelectionModal').modal('show');
    });

    // Load exams grouped by class
    function loadExamsForSelection() {
        console.log('Loading exams for selection...');

        // Show loading
        $('#exams-by-class-container').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading exams...</span>
                </div>
                <p class="mt-2">Loading exams list...</p>
            </div>
        `);

        // Clear previous selections
        selectedExams = [];
        updateSelectedExams();

        // Get exams via AJAX
        $.ajax({
            url: '{{ route("questions.getExams") }}',
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                console.log('Fetching exams from:', this.url);
            },
            success: function(response, status, xhr) {
                console.log('Exams loaded successfully!');
                console.log('Response status:', xhr.status);
                console.log('Response has success:', response.success);
                console.log('Number of exams:', response.exams ? response.exams.length : 0);

                if (response && response.success) {
                    if (response.exams && response.exams.length > 0) {
                        // Debug the data
                        console.log('First exam data:', response.exams[0]);

                        // Check if subjects are loaded
                        response.exams.forEach((exam, index) => {
                            console.log(`Exam ${index}: ${exam.title} - Subject: ${exam.subject}`);
                        });

                        renderExamsByClass(response.exams);
                    } else {
                        console.warn('No exams found for this user');
                        $('#exams-by-class-container').html(`
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                No exams found. Please <a href="{{ route('exams.create') }}" class="alert-link">create an exam</a> first.
                            </div>
                        `);
                    }
                } else {
                    console.error('Invalid response format:', response);
                    showError('Invalid response from server');
                    $('#exams-by-class-container').html(`
                        <div class="alert alert-danger">
                            <i class="ri-alert-line me-2"></i>
                            Invalid response format from server
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load exams:', error);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);

                let errorMessage = 'Failed to load exams. ';
                if (xhr.status === 404) {
                    errorMessage = 'Route not found. Please contact administrator.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else if (xhr.status === 401) {
                    errorMessage = 'Please login again.';
                }

                $('#exams-by-class-container').html(`
                    <div class="alert alert-danger">
                        <i class="ri-alert-line me-2"></i>
                        ${errorMessage}<br>
                        <small>Error: ${xhr.status} - ${error}</small>
                    </div>
                `);
            },
            complete: function() {
                console.log('Exams loading complete');
            }
        });
    }

    // Simple render function
    function renderExamsByClass(exams) {
        console.log('Rendering', exams.length, 'exams');

        if (!exams || exams.length === 0) {
            console.error('No exams to render');
            return;
        }

        let html = '';

        // Group exams by class for better organization
        const examsByClass = {};
        exams.forEach(function(exam) {
            const className = exam.class_name || 'Unclassified';
            if (!examsByClass[className]) {
                examsByClass[className] = [];
            }
            examsByClass[className].push(exam);
        });

        // Render each class group
        Object.keys(examsByClass).forEach(function(className) {
            html += `
                <div class="class-group mb-4">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                        <i class="ri-building-line me-2"></i>${className}
                    </h6>
                    <div class="row">
            `;

            examsByClass[className].forEach(function(exam) {
                // Handle subject display - check if subject exists and is not empty
                const subjectText = exam.subject && exam.subject !== 'No Subject' ? exam.subject : '<span class="text-warning">No Subject</span>';

                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card exam-card h-100">
                            <div class="card-body p-3">
                                <div class="form-check h-100">
                                    <input class="form-check-input exam-checkbox"
                                           type="checkbox"
                                           value="${exam.id}"
                                           id="exam-${exam.id}"
                                           data-title="${exam.title}"
                                           data-class="${exam.class_name || 'No Class'}"
                                           data-subject="${exam.subject || 'No Subject'}">
                                    <label class="form-check-label w-100 h-100" for="exam-${exam.id}">
                                        <div class="d-flex flex-column justify-content-between h-100">
                                            <div>
                                                <strong class="d-block mb-1">${exam.title}</strong>
                                                <small class="text-muted d-block mb-1">
                                                    <i class="ri-book-open-line me-1"></i>
                                                    ${subjectText}
                                                </small>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge bg-primary">${exam.question_count || 0} questions</span>
                                                <span class="badge bg-secondary ms-1">${exam.marks || 0} marks</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        $('#exams-by-class-container').html(html);
        console.log('Exams rendered successfully');

        // Add hover effect
        $('.exam-card').hover(
            function() {
                $(this).addClass('border-primary shadow-sm');
            },
            function() {
                $(this).removeClass('border-primary shadow-sm');
            }
        );

        // Re-attach event listeners
        $('.exam-checkbox').change(updateSelectedExams);
        $('#select-all-exams-checkbox').change(function() {
            console.log('Select all exams:', this.checked);
            $('.exam-checkbox').prop('checked', this.checked);
            updateSelectedExams();
        });

        updateSelectedExams();
    }

    // Update selected exams
    function updateSelectedExams() {
        selectedExams = [];
        $('.exam-checkbox:checked').each(function() {
            selectedExams.push({
                id: $(this).val(),
                title: $(this).data('title'),
                class: $(this).data('class'),
                subject: $(this).data('subject')
            });
        });

        console.log('Selected exams:', selectedExams);

        // Update UI
        const selectedCount = selectedExams.length;
        $('#selected-count').text(selectedCount);
        $('#selected-exam-count').text(selectedCount);

        if (selectedCount > 0) {
            $('#selected-exams-summary').show();
            $('#proceed-to-question-form-btn').prop('disabled', false);

            // Update selected exams list
            let selectedList = '';
            selectedExams.forEach((exam, index) => {
                if (index < 3) {
                    selectedList += `<span class="badge bg-secondary me-1 mb-1">${exam.title}</span>`;
                }
            });
            if (selectedCount > 3) {
                selectedList += `<span class="badge bg-info">+${selectedCount - 3} more</span>`;
            }
            $('#selected-exams-list').html(selectedList);
        } else {
            $('#selected-exams-summary').hide();
            $('#proceed-to-question-form-btn').prop('disabled', true);
        }

        // Update select all checkbox
        const totalExams = $('.exam-checkbox').length;
        const checkedCount = $('.exam-checkbox:checked').length;
        $('#select-all-exams-checkbox').prop('checked', totalExams > 0 && checkedCount === totalExams);
    }

    // Search exams
    $('#search-exams-input').on('keyup', debounce(function() {
        const searchTerm = $(this).val().toLowerCase();
        console.log('Searching exams for:', searchTerm);

        if (!searchTerm) {
            $('.class-group').show();
            $('.exam-card').parent().show();
            return;
        }

        $('.class-group').each(function() {
            const classGroup = $(this);
            const className = classGroup.find('h6').text().toLowerCase();
            let hasVisibleExams = false;

            classGroup.find('.exam-card').each(function() {
                const card = $(this);
                const examTitle = card.find('strong').text().toLowerCase();
                const examSubject = card.find('.text-muted').text().toLowerCase();

                if (examTitle.includes(searchTerm) || examSubject.includes(searchTerm) || className.includes(searchTerm)) {
                    card.parent().show();
                    hasVisibleExams = true;
                } else {
                    card.parent().hide();
                }
            });

            if (hasVisibleExams) {
                classGroup.show();
            } else {
                classGroup.hide();
            }
        });
    }, 300));

    $('#clear-search-exams').click(function() {
        console.log('Clearing search');
        $('#search-exams-input').val('');
        $('.class-group').show();
        $('.exam-card').parent().show();
    });

    // Proceed to question form modal
    $('#proceed-to-question-form-btn').click(function() {
        if (selectedExams.length === 0) {
            showError('Please select at least one exam');
            return;
        }

        console.log('Opening question form modal for exams:', selectedExams);

        // Close exam selection modal
        $('#examSelectionModal').modal('hide');

        // Prepare and show question form modal
        prepareQuestionFormModal(false);
        $('#questionFormModal').modal('show');
    });

    // Function to prepare the question form modal
    function prepareQuestionFormModal(isEditMode = false) {
        console.log('Preparing question form modal with exams:', selectedExams, 'Edit mode:', isEditMode);

        // Clear previous form
        $('#question-form')[0].reset();
        $('#method-field').html('');
        $('#question_id_field').val('');
        $('#selected-exams-field').empty();
        $('#form-errors').addClass('d-none').find('#error-list').empty();

        // Reset image preview
        $('#image').val('');
        $('#image-preview').hide();
        $('#preview-img').attr('src', '#');

        // Set modal title
        if (isEditMode) {
            $('#modal-title-text').text('Edit');
            $('#save-and-add-another-btn').hide();
        } else {
            $('#modal-title-text').text('Add');
            $('#save-and-add-another-btn').show();
        }

        // Set modal title and info
        if (selectedExams.length === 1 && !isEditMode) {
            $('#single-exam-info').show();
            $('#multiple-exams-info').hide();
            $('#multiple-exams-badge').hide();

            const exam = selectedExams[0];
            $('#selected-exam-title').text(exam.title);
            $('#exam-title-text').text(exam.title);
            $('#exam-class-text').text(exam.class);
            // Handle subject display
            if (exam.subject && exam.subject !== 'No Subject') {
                $('#exam-subject-text').text(exam.subject);
            } else {
                $('#exam-subject-text').html('<span class="text-warning">No Subject</span>');
            }
        } else if (selectedExams.length > 1 && !isEditMode) {
            $('#single-exam-info').hide();
            $('#multiple-exams-info').show();
            $('#multiple-exams-badge').show();

            $('#selected-exam-title').text('Multiple Exams');
            $('#selected-exams-count').text(selectedExams.length);
        } else {
            // Edit mode or no exams selected
            $('#single-exam-info').hide();
            $('#multiple-exams-info').hide();
            $('#multiple-exams-badge').hide();
            $('#selected-exam-title').text('');
        }

        // Add hidden fields for selected exams (only for create mode)
        if (!isEditMode) {
            if (selectedExams.length === 1) {
                $('#selected-exams-field').append(`<input type="hidden" name="exam_id" value="${selectedExams[0].id}">`);
            } else {
                selectedExams.forEach(exam => {
                    $('#selected-exams-field').append(`<input type="hidden" name="exam_ids[]" value="${exam.id}">`);
                });
            }
        }

        // Load default question type options (MCQ)
        loadQuestionTypeOptions('mcq');

        // Initialize Quill editor for question text
        initializeQuestionTextEditor();
    }

    // Function to load question type options
    function loadQuestionTypeOptions(type, options = null) {
        console.log('Loading question type options for:', type, 'with options:', options);
        $('#question-options-container').empty();

        let templateId = '';
        switch(type) {
            case 'mcq':
                templateId = 'mcq-options-template';
                break;
            case 'true_false':
                templateId = 'tf-options-template';
                break;
            case 'short_answer':
                templateId = 'sa-options-template';
                break;
        }

        const template = document.getElementById(templateId);
        if (template) {
            const content = template.content.cloneNode(true);
            $('#question-options-container').append(content);

            // Populate options if provided (for edit mode)
            if (options && type === 'mcq') {
                populateMCQOptions(options);
            } else if (options && type === 'true_false') {
                populateTrueFalseOptions(options);
            } else if (options && type === 'short_answer') {
                populateShortAnswerOptions(options);
            }

            // Initialize specific options
            if (type === 'short_answer') {
                initializeShortAnswerEditor();
                if (options && options[0]) {
                    shortAnswerEditor.root.innerHTML = options[0].option_text;
                    $('#short_answer_text').val(options[0].option_text);
                }
            }
        }
    }

    // Function to populate MCQ options
    function populateMCQOptions(options) {
        console.log('Populating MCQ options:', options);

        // Find correct option
        let correctOption = '';
        options.forEach(option => {
            if (option.is_correct) {
                correctOption = option.label;
            }
        });

        // Populate each option field
        options.forEach(option => {
            const label = option.label;
            const fieldName = label.toLowerCase();
            const $optionField = $(`input[name="options[${fieldName}][option_text]"]`);

            if ($optionField.length) {
                $optionField.val(option.option_text);
            }

            // Check the correct option radio button
            if (option.is_correct) {
                $(`input[name="correct_option"][value="${fieldName}"]`).prop('checked', true);
            }
        });
    }

    // Function to populate True/False options
    function populateTrueFalseOptions(options) {
        console.log('Populating True/False options:', options);

        // Find correct option
        let correctOption = '';
        options.forEach(option => {
            if (option.is_correct) {
                correctOption = option.label;
            }
        });

        // Check the correct radio button
        $(`input[name="correct_option"][value="${correctOption}"]`).prop('checked', true);
    }

    // Function to populate Short Answer options
    function populateShortAnswerOptions(options) {
        console.log('Populating Short Answer options:', options);

        if (options && options[0]) {
            // The answer is stored in option_text for short answer
            if (shortAnswerEditor) {
                shortAnswerEditor.root.innerHTML = options[0].option_text;
                $('#short_answer_text').val(options[0].option_text);
            }

            // Check the correct option (should always be 'answer' for short answer)
            $('input[name="correct_option"][value="answer"]').prop('checked', true);
        }
    }

    // Initialize Quill editor for question text
    function initializeQuestionTextEditor() {
        // Destroy existing editor if it exists
        if (questionTextEditor) {
            questionTextEditor = null;
            $('#question-text-editor').empty();
        }

        // Initialize new editor
        questionTextEditor = new Quill('#question-text-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean']
                ]
            },
            placeholder: 'Enter your question here...'
        });

        // Update hidden textarea with editor content
        questionTextEditor.on('text-change', function() {
            $('#question_text').val(questionTextEditor.root.innerHTML);
        });
    }

    // Initialize Quill editor for short answer
    function initializeShortAnswerEditor() {
        // Destroy existing editor if it exists
        if (shortAnswerEditor) {
            shortAnswerEditor = null;
            $('#short-answer-editor').empty();
        }

        // Initialize new editor
        shortAnswerEditor = new Quill('#short-answer-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            },
            placeholder: 'Enter the correct answer...'
        });

        // Update hidden textarea with editor content
        shortAnswerEditor.on('text-change', function() {
            $('#short_answer_text').val(shortAnswerEditor.root.innerHTML);
        });
    }

    // Handle question type change
    $(document).on('change', '#type', function() {
        const type = $(this).val();
        loadQuestionTypeOptions(type);
    });

    // Handle change exam selection button
    $('#change-exam-selection').click(function() {
        $('#questionFormModal').modal('hide');
        setTimeout(() => {
            $('#examSelectionModal').modal('show');
        }, 300);
    });

    // Handle image preview
    $(document).on('change', '#image', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-img').attr('src', e.target.result);
                $('#image-preview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle remove image
    $(document).on('click', '#remove-image', function() {
        $('#image').val('');
        $('#image-preview').hide();
        $('#preview-img').attr('src', '#');
    });

    // Handle form submission
    $('#question-form').submit(function(e) {
        e.preventDefault();

        const questionId = $('#question_id_field').val();
        const isEditMode = !!questionId;
        const url = isEditMode ?
            '{{ url("questions") }}/' + questionId :
            '{{ route("questions.store") }}';

        console.log('Submitting question form to:', url);
        console.log('Edit mode:', isEditMode);

        // Update hidden textareas with editor content
        if (questionTextEditor) {
            $('#question_text').val(questionTextEditor.root.innerHTML);
        }

        if (shortAnswerEditor) {
            $('#short_answer_text').val(shortAnswerEditor.root.innerHTML);
        }

        // Validate form
        if (!validateQuestionForm()) {
            return;
        }

        const formData = new FormData(this);
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }

        console.log('Form data:', Object.fromEntries(formData));

        $('#save-question-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        $('#form-errors').addClass('d-none');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Success response:', response);
                if (response.success) {
                    showSuccess(isEditMode ? 'Question updated successfully!' : 'Question added successfully!');
                    $('#questionFormModal').modal('hide');

                    // Reload page after a delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showFormErrors(response.errors || ['An error occurred']);
                }
            },
            error: function(xhr) {
                console.error('Error response:', xhr.responseText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    showFormErrors(Object.values(errors).flat());
                } else {
                    showFormErrors(['An unexpected error occurred. Please try again.']);
                }
            },
            complete: function() {
                $('#save-question-btn').prop('disabled', false).html('<i class="ri-save-line me-1"></i> Save Question');
            }
        });
    });

    // Handle Save & Add Another button
    $('#save-and-add-another-btn').click(function() {
        console.log('Save & Add Another clicked');

        // Update hidden textareas with editor content
        if (questionTextEditor) {
            $('#question_text').val(questionTextEditor.root.innerHTML);
        }

        if (shortAnswerEditor) {
            $('#short_answer_text').val(shortAnswerEditor.root.innerHTML);
        }

        // Validate form
        if (!validateQuestionForm()) {
            return;
        }

        const formData = new FormData($('#question-form')[0]);
        const url = '{{ route("questions.store") }}';

        console.log('Submitting to:', url);

        $('#save-and-add-another-btn, #save-question-btn').prop('disabled', true)
            .find('.spinner-border').removeClass('d-none');
        $('#form-errors').addClass('d-none');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Save & Add Another response:', response);
                if (response.success) {
                    showSuccess('Question saved! Adding another...');

                    // Reset form but keep selected exams
                    resetQuestionForm();

                    // Re-enable buttons
                    $('#save-and-add-another-btn, #save-question-btn').prop('disabled', false)
                        .find('.spinner-border').addClass('d-none');
                } else {
                    showFormErrors(response.errors || ['An error occurred']);
                    $('#save-and-add-another-btn, #save-question-btn').prop('disabled', false)
                        .find('.spinner-border').addClass('d-none');
                }
            },
            error: function(xhr) {
                console.error('Error response:', xhr.responseText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    showFormErrors(Object.values(errors).flat());
                } else {
                    showFormErrors(['An unexpected error occurred. Please try again.']);
                }
                $('#save-and-add-another-btn, #save-question-btn').prop('disabled', false)
                    .find('.spinner-border').addClass('d-none');
            }
        });
    });

    // Function to reset the question form
    function resetQuestionForm() {
        // Clear form fields but keep selected exams
        $('#type').val('mcq');
        $('#marks').val('1');
        $('#is_reusable').prop('checked', false);

        // Reset editors
        if (questionTextEditor) {
            questionTextEditor.root.innerHTML = '';
            $('#question_text').val('');
        }

        if (shortAnswerEditor) {
            shortAnswerEditor.root.innerHTML = '';
            $('#short_answer_text').val('');
        }

        // Reset image
        $('#image').val('');
        $('#image-preview').hide();
        $('#preview-img').attr('src', '#');

        // Clear errors
        $('#form-errors').addClass('d-none').find('#error-list').empty();

        // Reload default options
        loadQuestionTypeOptions('mcq');

        // Focus on question text editor
        if (questionTextEditor) {
            questionTextEditor.focus();
        }
    }

    // Validate question form
    function validateQuestionForm() {
        const type = $('#type').val();
        let isValid = true;
        const errors = [];

        // Check question text
        if (!questionTextEditor || questionTextEditor.getText().trim() === '') {
            errors.push('Question text is required');
            isValid = false;
        }

        // Check type
        if (!type) {
            errors.push('Please select a question type');
            isValid = false;
        }

        // Check type-specific validation
        if (type === 'mcq') {
            const filledOptions = $('input[name^="options["][name$="][option_text]"]').filter(function() {
                return $(this).val().trim() !== '';
            }).length;

            const correctSelected = $('.is-correct:checked').length;

            if (filledOptions < 2) {
                errors.push('At least 2 MCQ options must be filled');
                isValid = false;
            }
            if (correctSelected === 0) {
                errors.push('Please select a correct option for MCQ');
                isValid = false;
            }
        } else if (type === 'true_false') {
            const correctSelected = $('.is-correct:checked').length;
            if (correctSelected === 0) {
                errors.push('Please select correct answer for True/False');
                isValid = false;
            }
        } else if (type === 'short_answer') {
            if (!shortAnswerEditor || shortAnswerEditor.getText().trim() === '') {
                errors.push('Correct answer is required for Short Answer');
                isValid = false;
            }
        }

        // Check marks
        const marks = $('#marks').val();
        if (!marks || parseFloat(marks) <= 0) {
            errors.push('Marks must be greater than 0');
            isValid = false;
        }

        if (!isValid) {
            showFormErrors(errors);
        }

        return isValid;
    }

    // Show form errors
    function showFormErrors(errors) {
        const errorList = $('#error-list');
        errorList.empty();

        errors.forEach(error => {
            errorList.append(`<li>${error}</li>`);
        });

        $('#form-errors').removeClass('d-none');
        $('html, body').animate({
            scrollTop: $('#form-errors').offset().top - 100
        }, 500);
    }

    // View question details
    $(document).on('click', '.view-question', function() {
        const questionId = $(this).data('id');
        console.log('Viewing question:', questionId);

        $('#view-question-content').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading question details...</p>
            </div>
        `);

        $.get('{{ url("questions") }}/' + questionId + '/details', function(response) {
            console.log('Question details loaded:', response);
            $('#view-question-content').html(`
                <div class="question-details">
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">Question Text:</h6>
                        <div class="border rounded p-3 bg-light">
                            ${response.question_text || 'No question text'}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Question Info:</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Type:</span>
                                    <span class="badge bg-${response.type === 'mcq' ? 'info' : response.type === 'true_false' ? 'warning' : 'success'}">
                                        ${response.type.toUpperCase()}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Marks:</span>
                                    <span class="badge bg-primary">${response.marks || 0}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Reusable:</span>
                                    <span class="badge bg-${response.is_reusable ? 'success' : 'secondary'}">
                                        ${response.is_reusable ? 'Yes' : 'No'}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Exam:</span>
                                    <span>${response.exam_title || 'Unknown Exam'}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Image:</h6>
                            ${response.image ?
                                `<a href="{{ asset('storage/') }}/${response.image}" target="_blank">
                                    <img src="{{ asset('storage/') }}/${response.image}"
                                         alt="Question Image"
                                         class="img-fluid rounded border"
                                         style="max-height: 200px;">
                                </a>` :
                                '<p class="text-muted">No image</p>'
                            }
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">Options:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Option</th>
                                        <th>Text</th>
                                        <th>Correct</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.options && response.options.length > 0 ?
                                        response.options.map(option => `
                                            <tr class="${option.is_correct ? 'table-success' : ''}">
                                                <td class="fw-bold">${option.label ? option.label.toUpperCase() : 'N/A'}</td>
                                                <td>${option.option_text || 'No text'}</td>
                                                <td>
                                                    ${option.is_correct ?
                                                        '<span class="badge bg-success"><i class="ri-check-line"></i> Correct</span>' :
                                                        '<span class="badge bg-secondary">Incorrect</span>'
                                                    }
                                                </td>
                                            </tr>
                                        `).join('') :
                                        '<tr><td colspan="3" class="text-center">No options found</td></tr>'
                                    }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `);
            $('#viewQuestionModal').modal('show');
        }).fail(function(xhr, status, error) {
            console.error('Failed to load question details:', error);
            showError('Failed to load question details');
        });
    });

    // Edit question (load into modal)
    $(document).on('click', '.edit-question', function() {
        const questionId = $(this).data('id');
        const examId = $(this).data('exam-id');

        console.log('Editing question:', questionId, 'for exam:', examId);

        // Show loading in the question form modal
        $('#question-options-container').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading question data...</p>
            </div>
        `);

        // Load question data via AJAX
        $.get('{{ url("questions") }}/' + questionId + '/edit', function(response) {
            console.log('Edit response:', response);

            if (response.success) {
                // Set selected exam for edit mode
                selectedExams = [{
                    id: response.exam_id,
                    title: 'Loading...',
                    class: 'Loading...',
                    subject: 'Loading...'
                }];

                // Populate the modal with question data
                populateEditForm(response);
                $('#questionFormModal').modal('show');
            } else {
                showError('Failed to load question data');
            }
        }).fail(function(xhr, status, error) {
            console.error('Edit error:', error);
            showError('Failed to load question data');
        });
    });

    // Function to populate edit form
    function populateEditForm(data) {
        console.log('Populating edit form with:', data);

        // Clear form
        $('#question-form')[0].reset();
        $('#method-field').html('');
        $('#question_id_field').val('');
        $('#selected-exams-field').empty();
        $('#form-errors').addClass('d-none').find('#error-list').empty();

        // Set modal title
        $('#modal-title-text').text('Edit');
        $('#selected-exam-title').text('');
        $('#multiple-exams-badge').hide();
        $('#save-and-add-another-btn').hide();

        // Hide exam selection info in edit mode
        $('#exam-selection-info').hide();

        // Set up form for update
        $('#method-field').html(`
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        `);
        $('#question_id_field').val(data.question.id);

        // Add exam_id field
        $('#selected-exams-field').append(`<input type="hidden" name="exam_id" value="${data.exam_id}">`);

        // Populate basic fields
        $('#type').val(data.question.type);
        $('#marks').val(data.question.marks);
        $('#is_reusable').prop('checked', data.question.is_reusable);

        // Set question text in editor
        if (questionTextEditor) {
            questionTextEditor.root.innerHTML = data.question.question_text;
            $('#question_text').val(data.question.question_text);
        }

        // Set image preview if exists
        if (data.question.image) {
            $('#preview-img').attr('src', '{{ asset('storage/') }}/' + data.question.image);
            $('#image-preview').show();
        } else {
            $('#image-preview').hide();
        }

        // Load question type options with existing data
        loadQuestionTypeOptions(data.question.type, data.options);
    }

    // Duplicate question (quick)
    $(document).on('click', '.duplicate-question', function() {
        const questionId = $(this).data('id');
        console.log('Duplicating question:', questionId);
        currentDuplicateQuestionId = questionId;
        $('#duplicate-question-id').val(questionId);
        $('#duplicateModal').modal('show');
    });

    // Confirm duplicate
    $('#confirm-duplicate').click(function() {
        const targetExamId = $('#duplicate-target-exam').val();
        const count = $('#duplicate-count').val();

        if (!targetExamId) {
            showError('Please select a target exam');
            return;
        }

        console.log('Duplicating question to exam:', targetExamId, 'count:', count);

        $('#confirm-duplicate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Duplicating...');

        // Create the first copy
        $.ajax({
            url: '{{ url("questions") }}/' + currentDuplicateQuestionId + '/duplicate',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                target_exam_id: targetExamId
            },
            success: function(response) {
                console.log('Duplicate response:', response);
                if (response.success) {
                    showSuccess(`Successfully duplicated question`);
                    $('#duplicateModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Duplicate error:', status, error, xhr.responseText);
                showError('Error duplicating question');
            },
            complete: function() {
                $('#confirm-duplicate').prop('disabled', false).text('Duplicate');
                $('#duplicate-count').val(1);
                $('#duplicate-target-exam').val('');
            }
        });
    });

    // Delete question
    $(document).on('click', '.delete-question', function() {
        const questionId = $(this).data('id');
        const questionText = $(this).closest('tr').find('.question-text').text().substring(0, 50) + '...';

        console.log('Deleting question:', questionId);

        Swal.fire({
            title: 'Delete Question',
            text: `Are you sure you want to delete this question: "${questionText}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("questions") }}/' + questionId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Delete response:', response);
                        if (response.success) {
                            showSuccess('Question deleted successfully');
                            $('tr[data-id="' + questionId + '"]').fadeOut(300, function() {
                                $(this).remove();
                                if ($('#questions-table tbody tr').length === 1) {
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', status, error, xhr.responseText);
                        showError('Failed to delete question');
                    }
                });
            }
        });
    });

    // View reusable questions
    $('#view-reusable-btn').click(function() {
        console.log('Viewing reusable questions');
        loadReusableQuestions();
        $('#reusableQuestionsModal').modal('show');
    });

    // Load reusable questions
    function loadReusableQuestions(search = '', examId = '') {
        console.log('Loading reusable questions, search:', search, 'examId:', examId);

        // Show loading
        $('#reusable-questions-list').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading reusable questions...</p>
            </div>
        `);

        $.get('{{ route("questions.reusable.list") }}', {
            search: search,
            exam_id: examId
        }, function(response) {
            console.log('Reusable questions response:', response);
            if (response.questions && response.questions.length > 0) {
                let html = '<div class="list-group">';
                response.questions.forEach(function(question, index) {
                    html += `
                        <div class="list-group-item">
                            <div class="form-check">
                                <input class="form-check-input reusable-checkbox"
                                       type="checkbox"
                                       value="${question.id}"
                                       id="reusable-${question.id}">
                                <label class="form-check-label w-100" for="reusable-${index}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>${question.text}</strong>
                                            <div class="text-muted small">
                                                <span class="badge bg-info me-2">${question.type.toUpperCase()}</span>
                                                <span class="badge bg-primary me-2">${question.marks} marks</span>
                                                <span class="me-2">From: ${question.exam_title}</span>
                                                <span>Class: ${question.class}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary">${question.options_count} options</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                $('#reusable-questions-list').html(html);
                updateReusableButton();
            } else {
                $('#reusable-questions-list').html(`
                    <div class="text-center py-5">
                        <i class="ri-inbox-line display-4 text-muted"></i>
                        <h5 class="mt-3">No reusable questions found</h5>
                        <p class="text-muted">Mark questions as reusable to see them here</p>
                    </div>
                `);
            }
        }).fail(function(xhr, status, error) {
            console.error('Failed to load reusable questions:', error);
            $('#reusable-questions-list').html(`
                <div class="alert alert-danger">
                    <i class="ri-alert-line me-2"></i>
                    Failed to load reusable questions. Please try again.
                </div>
            `);
        });
    }

    // Update reusable button state
    function updateReusableButton() {
        const selected = $('.reusable-checkbox:checked').length;
        console.log('Reusable questions selected:', selected);
        $('#add-reusable-questions').prop('disabled', selected === 0);
    }

    // Search reusable questions
    $('#search-reusable').on('keyup', debounce(function() {
        loadReusableQuestions($(this).val(), $('#reusable-exam-filter').val());
    }, 500));

    $('#reusable-exam-filter').change(function() {
        loadReusableQuestions($('#search-reusable').val(), $(this).val());
    });

    // Add reusable questions to current exam
    $('#add-reusable-questions').click(function() {
        const selectedQuestions = $('.reusable-checkbox:checked').map(function() {
            return this.value;
        }).get();

        console.log('Adding reusable questions:', selectedQuestions);

        if (selectedQuestions.length === 0) return;

        // Show exam selection for adding
        const examSelect = $('#importModal select[name="exam_id"]').clone();
        examSelect.attr('id', 'add-reusable-exam-select').addClass('mb-3');

        Swal.fire({
            title: 'Add to Exam',
            html: `
                <div class="mb-3">
                    <label class="form-label">Select target exam:</label>
                    ${examSelect[0].outerHTML}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add Questions',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const examId = document.getElementById('add-reusable-exam-select').value;
                if (!examId) {
                    Swal.showValidationMessage('Please select an exam');
                    return false;
                }
                return { examId: examId };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const examId = result.value.examId;

                // Show loading
                Swal.fire({
                    title: 'Adding Questions',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("questions.bulk.update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: selectedQuestions,
                        action: 'change_exam',
                        data: { exam_id: examId }
                    },
                    success: function(response) {
                        console.log('Add reusable response:', response);
                        Swal.close();
                        if (response.success) {
                            showSuccess(`Added ${selectedQuestions.length} question(s) to exam`);
                            $('#reusableQuestionsModal').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Add reusable error:', status, error, xhr.responseText);
                        Swal.close();
                        showError('Failed to add questions');
                    }
                });
            }
        });
    });

    // Initialize on page load
    console.log('Initializing bulk buttons');
    updateBulkButtons();
    console.log('Page initialization complete');
});
</script>
@endsection
