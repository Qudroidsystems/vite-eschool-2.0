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

<!-- Question Form Modal -->
<div class="modal fade" id="questionFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Question to
                    <span id="selected-exam-title"></span>
                    <span id="multiple-exams-badge" class="badge bg-info ms-2" style="display: none;">Multiple Exams</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Exam Selection Info -->
                <div id="exam-selection-info" class="alert alert-info mb-4">
                    <div class="d-flex align-items-start">
                        <i class="ri-information-line fs-4 me-2"></i>
                        <div>
                            <div id="single-exam-info" style="display: none;">
                                <strong>Exam:</strong> <span id="exam-title-text"></span><br>
                                <strong>Class:</strong> <span id="exam-class-text"></span><br>
                                <strong>Subject:</strong> <span id="exam-subject-text"></span>
                            </div>
                            <div id="multiple-exams-info" style="display: none;">
                                <strong>Selected Exams:</strong> <span id="selected-exams-count"></span> exams<br>
                                <small class="text-muted">This question will be added to all selected exams.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Form -->
                <form id="question-form" enctype="multipart/form-data">
                    @csrf
                    <div id="method-field"></div>
                    <input type="hidden" name="question_id" id="question_id_field">
                    <div id="selected-exams-field"></div>

                    <div class="mb-3">
                        <label for="question_text" class="form-label required">Question Text</label>
                        <div id="question-text-editor" style="min-height: 150px;"></div>
                        <textarea name="question_text" id="question_text" style="display: none;" required></textarea>
                        <div class="form-text">Enter the main question text here.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label required">Question Type</label>
                            <select name="type" id="type" class="form-control question-type" required>
                                <option value="" disabled selected>Select a type</option>
                                <option value="mcq">Multiple Choice (MCQ)</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="marks" class="form-label required">Marks</label>
                            <input type="number" name="marks" id="marks" class="form-control" value="1" min="0.1" step="0.1" required>
                        </div>
                    </div>

                    <!-- Question Type Options -->
                    <div id="question-options-container">
                        <!-- Options will be dynamically loaded based on type -->
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image (Optional)</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" />
                        <div id="image-preview" class="mt-3" style="display: none;">
                            <img id="preview-img" src="#" alt="Image Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                            <button type="button" class="btn btn-sm btn-danger ms-2" id="remove-image">
                                <i class="ri-close-line"></i> Remove
                            </button>
                        </div>
                    </div>

                    <!-- Reusable Option -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_reusable" id="is_reusable" value="1">
                            <label class="form-check-label" for="is_reusable">
                                <strong>Mark as reusable question</strong>
                                <div class="form-text">This question can be reused in other exams</div>
                            </label>
                        </div>
                    </div>

                    <!-- Error Display -->
                    <div class="alert alert-danger d-none" id="form-errors">
                        <ul id="error-list" class="mb-0"></ul>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-secondary" id="change-exam-selection">
                    <i class="ri-arrow-left-line me-1"></i> Change Exams
                </button>
                <button type="button" class="btn btn-success" id="save-and-add-another-btn">
                    <i class="ri-add-circle-line me-1"></i> Save & Add Another
                </button>
                <button type="submit" class="btn btn-primary" id="save-question-btn" form="question-form">
                    <i class="ri-save-line me-1"></i> Save Question
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MCQ Options Template -->
<template id="mcq-options-template">
    <div class="mcq-options">
        <h6 class="fw-bold mb-3">Multiple Choice Options (Select at least 2)</h6>
        <div class="alert alert-warning">
            <i class="ri-alert-line me-2"></i> You must select one correct option
        </div>

        <div class="options-fields" id="mcq-options-fields">
            <!-- Options will be dynamically added here -->
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm" id="add-mcq-option">
            <i class="ri-add-line me-1"></i> Add Another Option
        </button>

        <div class="alert alert-info mt-3">
            <i class="ri-information-line me-2"></i>
            Options labeled A-E will be saved as default labels. Additional options will be saved with custom labels.
        </div>
    </div>
</template>

<!-- True/False Options Template -->
<template id="tf-options-template">
    <div class="tf-options">
        <h6 class="fw-bold mb-3">True/False Options</h6>
        <div class="alert alert-warning">
            <i class="ri-alert-line me-2"></i> Select the correct answer
        </div>
        <div class="options-fields">
            <div class="option-field mb-3">
                <div class="d-flex align-items-center">
                    <input type="hidden" name="options[true][option_text]" value="True">
                    <label class="fw-semibold me-3">True</label>
                    <div class="form-check">
                        <input class="form-check-input is-correct" type="radio" name="correct_option" value="true" required />
                        <label class="form-check-label">Correct</label>
                    </div>
                </div>
            </div>
            <div class="option-field mb-3">
                <div class="d-flex align-items-center">
                    <input type="hidden" name="options[false][option_text]" value="False">
                    <label class="fw-semibold me-3">False</label>
                    <div class="form-check">
                        <input class="form-check-input is-correct" type="radio" name="correct_option" value="false" />
                        <label class="form-check-label">Correct</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Short Answer Options Template -->
<template id="sa-options-template">
    <div class="sa-options">
        <h6 class="fw-bold mb-3">Correct Answer</h6>
        <div class="mb-3">
            <div id="short-answer-editor" style="min-height: 100px;"></div>
            <textarea name="options[answer][option_text]" id="short_answer_text" style="display: none;" required></textarea>
        </div>
    </div>
</template>

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
                                           data-class="${exam.class_name || 'No Class'}">
                                    <label class="form-check-label w-100 h-100" for="exam-${exam.id}">
                                        <div class="d-flex flex-column justify-content-between h-100">
                                            <div>
                                                <strong class="d-block mb-1">${exam.title}</strong>
                                                <small class="text-muted d-block mb-1">
                                                    <i class="ri-book-open-line me-1"></i>${exam.subject || 'No Subject'}
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
                class: $(this).data('class')
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
        prepareQuestionFormModal();
        $('#questionFormModal').modal('show');
    });

    // Function to prepare the question form modal
    function prepareQuestionFormModal() {
        console.log('Preparing question form modal with exams:', selectedExams);

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

        // Set modal title and info
        if (selectedExams.length === 1) {
            $('#single-exam-info').show();
            $('#multiple-exams-info').hide();
            $('#multiple-exams-badge').hide();

            const exam = selectedExams[0];
            $('#selected-exam-title').text(exam.title);
            $('#exam-title-text').text(exam.title);
            $('#exam-class-text').text(exam.class);
            $('#exam-subject-text').text('To be loaded');
        } else {
            $('#single-exam-info').hide();
            $('#multiple-exams-info').show();
            $('#multiple-exams-badge').show();

            $('#selected-exam-title').text('Multiple Exams');
            $('#selected-exams-count').text(selectedExams.length);
        }

        // Add hidden fields for selected exams
        if (selectedExams.length === 1) {
            $('#selected-exams-field').append(`<input type="hidden" name="exam_id" value="${selectedExams[0].id}">`);
        } else {
            // If multiple exams selected, we need to handle this differently
            selectedExams.forEach(exam => {
                $('#selected-exams-field').append(`<input type="hidden" name="exam_ids[]" value="${exam.id}">`);
            });
        }

        // Set form action and method
        $('#method-field').html(`
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        `);

        // Load default question type options (MCQ)
        loadQuestionTypeOptions('mcq');

        // Initialize Quill editor for question text
        initializeQuestionTextEditor();
    }

    // Function to add MCQ option fields
    function addMCQOption(label = null, value = '', isCorrect = false) {
        const optionsContainer = $('#mcq-options-fields');
        const optionCount = optionsContainer.find('.option-field').length;

        // Generate label if not provided
        if (!label) {
            if (optionCount < 5) {
                const defaultLabels = ['A', 'B', 'C', 'D', 'E'];
                label = defaultLabels[optionCount];
            } else {
                label = `Option ${optionCount + 1}`;
            }
        }

        const optionId = `option-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
        const fieldName = label.toLowerCase().replace(/[^a-z]/g, '');

        const optionHtml = `
            <div class="option-field mb-3" id="${optionId}">
                <div class="d-flex align-items-center">
                    <label class="fw-semibold me-3">${label}:</label>
                    <input type="text"
                           name="options[${fieldName}][option_text]"
                           class="form-control me-3"
                           placeholder="Enter option ${label}..."
                           value="${value}"
                           ${optionCount < 2 ? 'required' : ''} />
                    <div class="form-check me-2">
                        <input class="form-check-input is-correct"
                               type="radio"
                               name="correct_option"
                               value="${fieldName}"
                               ${isCorrect ? 'checked' : ''} />
                        <label class="form-check-label">Correct</label>
                    </div>
                    ${optionCount >= 2 ? `
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option" data-option="${optionId}">
                            <i class="ri-close-line"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;

        optionsContainer.append(optionHtml);

        // Add remove option functionality
        $(`#${optionId} .remove-option`).click(function() {
            $(this).closest('.option-field').remove();
            updateMCQValidation();
        });
    }

    // Function to update MCQ validation
    function updateMCQValidation() {
        const filledOptions = $('#mcq-options-fields input[type="text"]').filter(function() {
            return $(this).val().trim() !== '';
        }).length;

        const minRequiredOptions = 2;

        // Update required attribute based on count
        $('#mcq-options-fields input[type="text"]').each(function(index) {
            if (index < minRequiredOptions) {
                $(this).prop('required', true);
            } else {
                $(this).prop('required', false);
            }
        });

        // Show warning if not enough options
        if (filledOptions < minRequiredOptions) {
            $('#mcq-options-fields').before(`
                <div class="alert alert-danger" id="option-warning">
                    <i class="ri-alert-line me-2"></i>
                    At least ${minRequiredOptions} options must be filled
                </div>
            `);
        } else {
            $('#option-warning').remove();
        }
    }

    // Initialize MCQ options with default A and B
    function initializeMCQOptions() {
        const optionsContainer = $('#mcq-options-fields');
        optionsContainer.empty();

        // Add first two required options
        addMCQOption('A', '', false);
        addMCQOption('B', '', false);

        // Add more options button functionality
        $('#add-mcq-option').off('click').click(function() {
            addMCQOption();
            updateMCQValidation();
        });

        // Add input validation on change
        optionsContainer.on('input', 'input[type="text"]', function() {
            updateMCQValidation();
        });
    }

    // Function to load question type options
    function loadQuestionTypeOptions(type) {
        console.log('Loading question type options for:', type);
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

            // Initialize specific options
            if (type === 'mcq') {
                initializeMCQOptions();
            } else if (type === 'short_answer') {
                initializeShortAnswerEditor();
            }
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
        console.log('Submitting question form');

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
        const url = '{{ route("questions.store") }}';

        console.log('Submitting to:', url);
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
                    showSuccess('Question added successfully!');
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
            const filledOptions = $('#mcq-options-fields input[type="text"]').filter(function() {
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

    // Edit question (redirect to edit page)
    $(document).on('click', '.edit-question', function() {
        const questionId = $(this).data('id');
        const examId = $(this).data('exam-id');
        console.log('Editing question:', questionId, 'for exam:', examId);
        window.location.href = '{{ url("questions") }}/' + questionId + '/edit?exam_id=' + examId;
    });

    // Initialize on page load
    console.log('Initializing bulk buttons');
    updateBulkButtons();
    console.log('Page initialization complete');
});
</script>
@endsection
