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

<!-- Exam Selection Modal -->
<div class="modal fade" id="examSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Exams for Questions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-start">
                        <i class="ri-information-line fs-4 me-2"></i>
                        <div>
                            <strong>Instructions:</strong>
                            <ul class="mb-0">
                                <li>Select one or more exams to add questions to</li>
                                <li>Questions will be added to all selected exams</li>
                                <li>Use search to filter exams by title or class</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" id="search-exams-input" placeholder="Search exams by title, class, or subject...">
                        <button class="btn btn-outline-secondary" type="button" id="clear-search-exams">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>

                <!-- Exams Grouped by Class -->
                <div id="exams-by-class-container" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading exams...</span>
                        </div>
                    </div>
                </div>

                <!-- Selected Exams Summary -->
                <div class="selected-exams-summary mt-4 p-3 bg-light rounded" id="selected-exams-summary" style="display: none;">
                    <h6 class="fw-bold mb-2">Selected Exams: <span id="selected-count">0</span></h6>
                    <div id="selected-exams-list" class="small"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                <div class="form-check me-auto">
                    <input class="form-check-input" type="checkbox" id="select-all-exams-checkbox">
                    <label class="form-check-label" for="select-all-exams-checkbox">
                        Select all
                    </label>
                </div>
                <button type="button" class="btn btn-primary" id="proceed-to-question-form-btn" disabled>
                    <i class="ri-arrow-right-line me-1"></i> Proceed with <span id="selected-exam-count">0</span> Exam(s)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('questions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Questions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Exam</label>
                        <select name="exam_id" class="form-control" required>
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                        <div class="form-text">
                            Supported formats: CSV, Excel (.xlsx, .xls)<br>
                            <a href="{{ asset('templates/questions_import_template.xlsx') }}" download class="btn btn-sm btn-outline-primary">
                                <i class="ri-download-line me-1"></i> Download Template
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="ri-information-line me-2"></i>File Format Instructions:</h6>
                        <ul class="mb-0">
                            <li><strong>Column A:</strong> Question Text (Required)</li>
                            <li><strong>Column B:</strong> Type (mcq/true_false/short_answer)</li>
                            <li><strong>Column C:</strong> Correct Answer</li>
                            <li><strong>Column D-H:</strong> Options A-E (for MCQ)</li>
                            <li><strong>Column I:</strong> Marks (default: 1)</li>
                            <li><strong>Column J:</strong> Reusable (true/false)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Questions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move to Exam Modal -->
<div class="modal fade" id="moveExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="move-exam-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Move Questions to Another Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="selected-questions" name="question_ids">
                    <div class="mb-3">
                        <label class="form-label">Select Target Exam</label>
                        <select name="data[exam_id]" class="form-control" required id="target-exam-select">
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        This will move <span id="move-count">0</span> selected question(s) to the target exam.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Move Questions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reusable Questions Modal -->
<div class="modal fade" id="reusableQuestionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reusable Questions Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="search-reusable" placeholder="Search reusable questions...">
                    </div>
                    <div class="col-md-6">
                        <select class="form-control" id="reusable-exam-filter">
                            <option value="">Filter by Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="reusable-questions-list" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="add-reusable-questions" disabled>
                    <i class="ri-add-line me-1"></i> Add Selected to Exam
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Question Modal -->
<div class="modal fade" id="viewQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view-question-content">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Duplicate Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duplicate Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Target Exam</label>
                    <select class="form-control" id="duplicate-target-exam">
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Number of Copies</label>
                    <input type="number" class="form-control" id="duplicate-count" value="1" min="1" max="10">
                </div>
                <input type="hidden" id="duplicate-question-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-duplicate">Duplicate</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- Include SortableJS for drag-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<!-- Include SweetAlert for notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let selectedQuestions = [];
    let currentDuplicateQuestionId = null;
    let selectedExams = [];

    // Initialize Sortable for drag-drop
    if (document.getElementById('sortable-questions')) {
        const sortable = Sortable.create(document.getElementById('sortable-questions'), {
            handle: '.handle',
            animation: 150,
            onEnd: function(evt) {
                const examId = $(evt.item).data('exam-id');
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
        const params = new URLSearchParams();

        const examId = $('#exam-filter').val();
        const classId = $('#class-filter').val();
        const type = $('#type-filter').val();
        const search = $('.search').val();

        if (examId) params.set('exam_id', examId);
        if (classId) params.set('class_id', classId);
        if (type) params.set('type', type);
        if (search) params.set('search', search);

        window.location.href = '{{ route("questions.all") }}?' + params.toString();
    }

    $('#exam-filter, #class-filter, #type-filter').change(applyFilters);
    $('.search').on('keyup', debounce(applyFilters, 500));

    // Bulk selection
    $('#select-all').change(function() {
        $('.question-checkbox').prop('checked', this.checked);
        updateBulkButtons();
    });

    $('.question-checkbox').change(function() {
        if (!this.checked) {
            $('#select-all').prop('checked', false);
        }
        updateBulkButtons();
    });

    function updateBulkButtons() {
        selectedQuestions = $('.question-checkbox:checked').map(function() {
            return this.value;
        }).get();

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
                $.ajax({
                    url: '{{ route("questions.bulk.update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: selectedQuestions,
                        action: 'delete'
                    },
                    success: function(response) {
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
                    error: function() {
                        showError('An error occurred while deleting questions');
                    }
                });
            }
        });
    });

    // Bulk move
    $('#bulk-move-btn').click(function() {
        $('#moveExamModal').modal('show');
    });

    $('#move-exam-form').submit(function(e) {
        e.preventDefault();

        const targetExamId = $('#target-exam-select').val();
        if (!targetExamId) {
            showError('Please select a target exam');
            return;
        }

        const formData = new FormData(this);
        formData.append('question_ids', selectedQuestions);
        formData.append('action', 'change_exam');

        $.ajax({
            url: '{{ route("questions.bulk.update") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    $('#moveExamModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('An error occurred while moving questions');
            }
        });
    });

    // Bulk mark as reusable
    $('#bulk-reusable-btn').click(function() {
        Swal.fire({
            title: 'Mark as Reusable',
            text: `Mark ${selectedQuestions.length} question(s) as reusable?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, mark them',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("questions.bulk.update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: selectedQuestions,
                        action: 'mark_reusable'
                    },
                    success: function(response) {
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
                    error: function() {
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
        loadExamsForSelection();
        $('#examSelectionModal').modal('show');
    });

    // Load exams grouped by class
    function loadExamsForSelection() {
        $.ajax({
            url: '{{ route("questions.getExams") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    renderExamsByClass(response.exams);
                } else {
                    showError('Failed to load exams');
                }
            },
            error: function() {
                showError('Failed to load exams');
            }
        });
    }

    // Render exams grouped by class
    function renderExamsByClass(exams) {
        let html = '';

        // Group exams by class
        const examsByClass = {};
        exams.forEach(exam => {
            const classKey = exam.class_name || 'No Class';
            if (!examsByClass[classKey]) {
                examsByClass[classKey] = [];
            }
            examsByClass[classKey].push(exam);
        });

        // Render each class group
        Object.keys(examsByClass).forEach(className => {
            html += `
                <div class="class-group mb-4">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                        <i class="ri-building-line me-2"></i>${className}
                    </h6>
                    <div class="row">
            `;

            examsByClass[className].forEach(exam => {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card exam-card">
                            <div class="card-body p-3">
                                <div class="form-check">
                                    <input class="form-check-input exam-checkbox"
                                           type="checkbox"
                                           value="${exam.id}"
                                           id="exam-${exam.id}"
                                           data-title="${exam.title}"
                                           data-class="${className}">
                                    <label class="form-check-label w-100" for="exam-${exam.id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong class="d-block">${exam.title}</strong>
                                                <small class="text-muted">
                                                    <i class="ri-book-open-line me-1"></i>${exam.subject || 'No Subject'}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="ri-question-line me-1"></i>${exam.question_count} questions
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary">${exam.marks || 'N/A'}</span>
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

        // Re-attach event listeners
        $('.exam-checkbox').change(updateSelectedExams);
        $('#select-all-exams-checkbox').change(function() {
            $('.exam-checkbox').prop('checked', this.checked);
            updateSelectedExams();
        });
    }

    // Update selected exams
    function updateSelectedExams() {
        selectedExams = [];
        $('.exam-checkbox:checked').each(function() {
            selectedExams.push({
                id: $(this).val(),
                title: $(this).data('title'),
                class: $(this).data('class')
            });
        });

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

        $('.exam-card').each(function() {
            const card = $(this);
            const examTitle = card.find('strong').text().toLowerCase();
            const examClass = card.find('.text-muted').text().toLowerCase();

            if (examTitle.includes(searchTerm) || examClass.includes(searchTerm) || searchTerm === '') {
                card.parent().show();
            } else {
                card.parent().hide();
            }
        });
    }, 300));

    $('#clear-search-exams').click(function() {
        $('#search-exams-input').val('');
        $('.exam-card').parent().show();
    });

    // Proceed to question form modal (simplified)
    $('#proceed-to-question-form-btn').click(function() {
        if (selectedExams.length === 0) {
            showError('Please select at least one exam');
            return;
        }

        // Get the first selected exam (for now, we'll just use the first one)
        const firstExamId = selectedExams[0].id;

        // Close exam selection modal
        $('#examSelectionModal').modal('hide');

        // Open new question form for that exam
        window.location.href = '{{ route("questions.create") }}?exam_id=' + firstExamId;
    });

    // View question
    $(document).on('click', '.view-question', function() {
        const questionId = $(this).data('id');

        $('#view-question-content').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading question details...</p>
            </div>
        `);

        $.get('{{ url("questions") }}/' + questionId + '/details', function(response) {
            $('#view-question-content').html(response);
            $('#viewQuestionModal').modal('show');
        }).fail(function() {
            showError('Failed to load question details');
        });
    });

    // Duplicate question (quick)
    $(document).on('click', '.duplicate-question', function() {
        const questionId = $(this).data('id');
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

        $('#confirm-duplicate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Duplicating...');

        // Create multiple copies
        const promises = [];
        for (let i = 0; i < count; i++) {
            promises.push(
                $.ajax({
                    url: '{{ url("questions") }}/' + currentDuplicateQuestionId + '/duplicate',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        target_exam_id: targetExamId
                    }
                })
            );
        }

        Promise.all(promises).then(function(responses) {
            $('#duplicateModal').modal('hide');
            showSuccess(`Successfully duplicated ${count} question(s)`);
            setTimeout(() => location.reload(), 1500);
        }).catch(function(error) {
            showError('Error duplicating question(s)');
        }).finally(function() {
            $('#confirm-duplicate').prop('disabled', false).text('Duplicate');
            $('#duplicate-count').val(1);
            $('#duplicate-target-exam').val('');
        });
    });

    // Delete question
    $(document).on('click', '.delete-question', function() {
        const questionId = $(this).data('id');
        const questionText = $(this).closest('tr').find('.question-text').text().substring(0, 50) + '...';

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
                    error: function() {
                        showError('Failed to delete question');
                    }
                });
            }
        });
    });

    // View reusable questions
    $('#view-reusable-btn').click(function() {
        loadReusableQuestions();
        $('#reusableQuestionsModal').modal('show');
    });

    // Load reusable questions
    function loadReusableQuestions(search = '', examId = '') {
        $.get('{{ route("questions.reusable.list") }}', {
            search: search,
            exam_id: examId
        }, function(response) {
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
        });
    }

    // Update reusable button state
    function updateReusableButton() {
        const selected = $('.reusable-checkbox:checked').length;
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
                        if (response.success) {
                            showSuccess(`Added ${selectedQuestions.length} question(s) to exam`);
                            $('#reusableQuestionsModal').modal('hide');
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function() {
                        showError('Failed to add questions');
                    }
                });
            }
        });
    });

    // Edit question (redirect to edit page)
    $(document).on('click', '.edit-question', function() {
        const questionId = $(this).data('id');
        const examId = $(this).data('exam-id');
        window.location.href = '{{ url("questions") }}/' + questionId + '/edit?exam_id=' + examId;
    });

    // Initialize on page load
    updateBulkButtons();
});
</script>

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

.exam-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
@endsection
