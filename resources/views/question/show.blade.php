@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">All Exams</a></li>
                                <li class="breadcrumb-item active">Questions</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Exam Summary Card -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card overflow-hidden">
                        <div class="bg-primary-subtle">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-3">
                                        <h5 class="text-primary mb-0">{{ $exam->title }}</h5>
                                        <p class="mb-0">{{ $exam->description ?? 'No description available' }}</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="{{ asset('assets/images/profile-img.png') }}" alt="" class="img-fluid" style="max-height: 120px;">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-24">
                                            <i class="ph-book-open"></i>
                                        </span>
                                    </div>
                                    <h5 class="font-size-15 text-truncate">{{ $exam->subject->subject ?? 'No Subject' }}</h5>
                                    <p class="text-muted mb-0 text-truncate">Subject</p>
                                </div>

                                <div class="col-sm-4">
                                    <div class="mt-4">
                                        <h5 class="font-size-14 mb-1">Class</h5>
                                        <div class="d-flex align-items-center">
                                            <i class="ph-users-three text-primary me-2"></i>
                                            <p class="mb-0">
                                                {{ $exam->schoolclass->schoolclass ?? 'N/A' }}
                                                @if($exam->schoolclass && $exam->schoolclass->armRelation)
                                                    <span class="badge bg-primary ms-1">{{ $exam->schoolclass->armRelation->arm }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="mt-4">
                                        <h5 class="font-size-14 mb-1">Time Details</h5>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ph-clock text-primary me-2"></i>
                                            <span class="fw-medium">{{ $exam->duration }} mins</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="ph-calendar-blank text-primary me-2"></i>
                                            <small>{{ $exam->start_time->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">
                                        <i class="ph-question"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Total Questions</p>
                                    <h4 class="fs-4 mb-0">{{ $questions->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle text-success rounded-2 fs-2">
                                        <i class="ph-check-circle"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Total Marks</p>
                                    <h4 class="fs-4 mb-0">{{ number_format($questions->sum('marks'), 1) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-info-subtle text-info rounded-2 fs-2">
                                        <i class="ph-list-numbers"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Avg. Marks</p>
                                    <h4 class="fs-4 mb-0">
                                        {{ $questions->count() > 0 ? number_format($questions->avg('marks'), 1) : '0.0' }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle text-warning rounded-2 fs-2">
                                        <i class="ph-graduation-cap"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Status</p>
                                    <h4 class="fs-4 mb-0">
                                        @if($exam->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Section -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">Exam Questions</h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-subtle-info btn-sm" id="toggleViewBtn">
                                        <i class="ph-list ph-sm me-1"></i> Toggle View
                                    </button>
                                    <a href="{{ route('exams.index') }}" class="btn btn-subtle-secondary btn-sm">
                                        <i class="ph-arrow-left ph-sm me-1"></i> Back to Exams
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($questions->count() > 0)
                                <!-- Grid View (Default) -->
                                <div id="gridView" class="row g-4">
                                    @foreach($questions as $index => $question)
                                        <div class="col-xl-4 col-lg-6">
                                            <div class="card question-card h-100 border">
                                                <div class="card-body">
                                                    <!-- Question Header -->
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-primary rounded-pill p-2 me-2">
                                                                <span class="fw-bold fs-5">{{ $index + 1 }}</span>
                                                            </span>
                                                            <span class="badge
                                                                @if($question->type === 'mcq') bg-primary
                                                                @elseif($question->type === 'true_false') bg-info
                                                                @else bg-success @endif">
                                                                {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                                                            </span>
                                                            <span class="badge bg-warning-subtle text-warning ms-2">
                                                                <i class="ph-star ph-xs me-1"></i>{{ $question->marks }} pts
                                                            </span>
                                                        </div>
                                                        @if($question->image)
                                                            <button class="btn btn-sm btn-subtle-primary view-image-btn"
                                                                    data-image="{{ asset('storage/' . $question->image) }}">
                                                                <i class="ph-image ph-sm"></i>
                                                            </button>
                                                        @endif
                                                    </div>

                                                    <!-- Question Text -->
                                                    <div class="mb-3">
                                                        <div class="fw-medium text-dark mb-1">Question:</div>
                                                        <div class="question-text">{!! $question->question_text !!}</div>
                                                    </div>

                                                    <!-- Options/Answer -->
                                                    <div class="mb-3">
                                                        @if($question->type === 'mcq')
                                                            <div class="fw-medium text-dark mb-2">Options:</div>
                                                            <div class="options-container">
                                                                @php
                                                                    $labels = ['A', 'B', 'C', 'D', 'E'];
                                                                    $optionIndex = 0;
                                                                @endphp
                                                                @foreach($question->options as $option)
                                                                    @if($option->option_text)
                                                                        <div class="option-item d-flex align-items-center mb-2 p-2 rounded
                                                                            {{ $option->is_correct ? 'bg-success-subtle border border-success' : 'bg-light' }}">
                                                                            <span class="badge
                                                                                {{ $option->is_correct ? 'bg-success' : 'bg-secondary' }}
                                                                                me-2">{{ $labels[$optionIndex] }}</span>
                                                                            <span class="{{ $option->is_correct ? 'text-success fw-medium' : '' }}">
                                                                                {{ $option->option_text }}
                                                                            </span>
                                                                            @if($option->is_correct)
                                                                                <i class="ph-check-circle-fill text-success ms-auto"></i>
                                                                            @endif
                                                                        </div>
                                                                        @php $optionIndex++; @endphp
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @elseif($question->type === 'true_false')
                                                            <div class="fw-medium text-dark mb-2">Correct Answer:</div>
                                                            <div class="d-flex align-items-center">
                                                                @foreach($question->options as $option)
                                                                    @if($option->is_correct)
                                                                        <span class="badge bg-success fs-6 px-3 py-2">
                                                                            <i class="ph-check-circle ph-sm me-2"></i>
                                                                            {{ ucfirst($option->label) }}
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @elseif($question->type === 'short_answer')
                                                            <div class="fw-medium text-dark mb-2">Expected Answer:</div>
                                                            <div class="bg-light p-3 rounded">
                                                                @foreach($question->options as $option)
                                                                    @if($option->is_correct)
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="ph-check-circle text-success me-2"></i>
                                                                            <span class="text-success fw-medium">{{ $option->option_text }}</span>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Footer -->
                                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                                        <div>
                                                            @if($question->is_reusable)
                                                                <span class="badge bg-info-subtle text-info">
                                                                    <i class="ph-repeat ph-xs me-1"></i>Reusable
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="ph-clock ph-xs me-1"></i>
                                                            {{ $question->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Table View (Hidden by Default) -->
                                <div id="tableView" class="d-none">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 50px;">#</th>
                                                    <th style="min-width: 300px;">Question</th>
                                                    <th class="text-center" style="width: 100px;">Type</th>
                                                    <th class="text-center" style="width: 80px;">Marks</th>
                                                    <th style="min-width: 200px;">Correct Answer</th>
                                                    <th class="text-center" style="width: 100px;">Image</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($questions as $index => $question)
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary rounded-pill p-2">
                                                                <span class="fw-bold">{{ $index + 1 }}</span>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="fw-medium text-dark mb-1">{!! Str::limit($question->question_text, 150) !!}</div>
                                                            @if($question->is_reusable)
                                                                <span class="badge bg-info-subtle text-info fs-10">
                                                                    <i class="ph-repeat ph-xs me-1"></i>Reusable
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge
                                                                @if($question->type === 'mcq') bg-primary
                                                                @elseif($question->type === 'true_false') bg-info
                                                                @else bg-success @endif">
                                                                {{ strtoupper(substr($question->type, 0, 2)) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="fw-bold text-warning">{{ $question->marks }}</span>
                                                        </td>
                                                        <td>
                                                            @if($question->type === 'mcq')
                                                                @foreach($question->options as $option)
                                                                    @if($option->is_correct)
                                                                        @php
                                                                            $labels = ['A', 'B', 'C', 'D', 'E'];
                                                                            $optionIndex = 0;
                                                                        @endphp
                                                                        @foreach($question->options as $opt)
                                                                            @if($opt->id === $option->id)
                                                                                <span class="badge bg-success">Option {{ $labels[$optionIndex] }}</span>
                                                                            @endif
                                                                            @if($opt->option_text) @php $optionIndex++; @endphp @endif
                                                                        @endforeach
                                                                        <div class="text-muted fs-12 mt-1">{{ Str::limit($option->option_text, 50) }}</div>
                                                                    @endif
                                                                @endforeach
                                                            @elseif($question->type === 'true_false')
                                                                @foreach($question->options as $option)
                                                                    @if($option->is_correct)
                                                                        <span class="badge bg-success">{{ ucfirst($option->label) }}</span>
                                                                    @endif
                                                                @endforeach
                                                            @elseif($question->type === 'short_answer')
                                                                @foreach($question->options as $option)
                                                                    @if($option->is_correct)
                                                                        <div class="text-success fw-medium">{{ Str::limit($option->option_text, 50) }}</div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($question->image)
                                                                <button class="btn btn-sm btn-subtle-primary view-image-btn"
                                                                        data-image="{{ asset('storage/' . $question->image) }}">
                                                                    <i class="ph-image ph-sm"></i> View
                                                                </button>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Question Type Distribution -->
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">Question Type Distribution</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @php
                                                        $typeCounts = $questions->groupBy('type')->map->count();
                                                    @endphp
                                                    @foreach($typeCounts as $type => $count)
                                                        <div class="col-md-4 mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    @if($type === 'mcq')
                                                                        <span class="avatar avatar-sm bg-primary-subtle text-primary rounded">
                                                                            <i class="ph-list-dashes ph-sm"></i>
                                                                        </span>
                                                                    @elseif($type === 'true_false')
                                                                        <span class="avatar avatar-sm bg-info-subtle text-info rounded">
                                                                            <i class="ph-check ph-sm"></i>
                                                                        </span>
                                                                    @else
                                                                        <span class="avatar avatar-sm bg-success-subtle text-success rounded">
                                                                            <i class="ph-pencil-line ph-sm"></i>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-0">{{ ucfirst(str_replace('_', ' ', $type)) }}</h6>
                                                                    <p class="text-muted mb-0">{{ $count }} questions
                                                                        ({{ number_format(($count/$questions->count())*100, 1) }}%)</p>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <span class="fw-bold fs-5">{{ $count }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @else
                                <!-- Empty State -->
                                <div class="text-center py-5">
                                    <div class="avatar-lg mx-auto mb-4">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle display-5">
                                            <i class="ph-question ph-2x"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-3">No Questions Found</h4>
                                    <p class="text-muted mb-4">This exam doesn't have any questions yet. Start by adding some questions.</p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('questions.create') }}" class="btn btn-primary">
                                            <i class="ph-plus-circle me-2"></i> Add New Question
                                        </a>
                                        <a href="{{ route('exams.index') }}" class="btn btn-subtle-secondary">
                                            <i class="ph-arrow-left me-2"></i> Back to Exams
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Question Image" class="img-fluid rounded">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="downloadImage" class="btn btn-primary" download>
                    <i class="ph-download-simple me-2"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between grid and table view
    const toggleViewBtn = document.getElementById('toggleViewBtn');
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');

    if (toggleViewBtn) {
        toggleViewBtn.addEventListener('click', function() {
            if (gridView.classList.contains('d-none')) {
                // Show Grid View
                gridView.classList.remove('d-none');
                tableView.classList.add('d-none');
                toggleViewBtn.innerHTML = '<i class="ph-list ph-sm me-1"></i> Switch to Table View';
            } else {
                // Show Table View
                gridView.classList.add('d-none');
                tableView.classList.remove('d-none');
                toggleViewBtn.innerHTML = '<i class="ph-grid-four ph-sm me-1"></i> Switch to Card View';
            }
        });
    }

    // Image Modal
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    const downloadLink = document.getElementById('downloadImage');

    document.querySelectorAll('.view-image-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imageUrl = this.getAttribute('data-image');
            modalImage.src = imageUrl;
            downloadLink.href = imageUrl;
            imageModal.show();
        });
    });

    // Add animation to cards on hover
    document.querySelectorAll('.question-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    // Print functionality
    const printBtn = document.createElement('button');
    printBtn.className = 'btn btn-subtle-info btn-sm';
    printBtn.innerHTML = '<i class="ph-printer ph-sm me-1"></i> Print Questions';
    printBtn.addEventListener('click', function() {
        window.print();
    });

    if (document.querySelector('.card-header .flex-shrink-0 .d-flex')) {
        document.querySelector('.card-header .flex-shrink-0 .d-flex').appendChild(printBtn);
    }
});
</script>
@endpush

@push('styles')
<style>
.question-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.question-card:hover {
    border-color: var(--bs-primary);
}

.question-text {
    line-height: 1.6;
    color: #333;
}

.option-item {
    transition: all 0.2s ease;
}

.option-item:hover {
    transform: translateX(5px);
    background-color: var(--bs-primary-subtle) !important;
}

.avatar-sm {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-animate {
    transition: transform 0.3s ease;
}

.card-animate:hover {
    transform: translateY(-5px);
}

.table-hover tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.bg-primary-subtle {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(var(--bs-success-rgb), 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(var(--bs-info-rgb), 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
}

.fs-10 {
    font-size: 10px;
}

.fs-12 {
    font-size: 12px;
}

/* Print Styles */
@media print {
    .card-header,
    .btn,
    #toggleViewBtn,
    .view-image-btn {
        display: none !important;
    }

    .question-card {
        break-inside: avoid;
        border: 1px solid #ddd;
        box-shadow: none !important;
        transform: none !important;
    }
}
</style>
@endpush
