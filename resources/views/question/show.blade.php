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
                                <li class="breadcrumb-item active">Questions</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">Exam Details</h5>
                            </div>
                            <div>
                                <a href="{{ route('exams.index') }}" class="btn btn-subtle-secondary btn-sm">
                                    <i class="ph-arrow-left"></i> Back to Exams
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Exam Information</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Title:</strong> {{ $exam->title }}</li>
                                        <li><strong>Subject:</strong> {{ $exam->subject->subject ?? 'N/A' }}</li>
                                        <li><strong>Class:</strong> {{ $exam->schoolclass->schoolclass ?? 'N/A' }}</li>
                                        @if($exam->schoolclass && $exam->schoolclass->armRelation)
                                            <li><strong>Arm:</strong> {{ $exam->schoolclass->armRelation->arm }}</li>
                                        @endif
                                        <li><strong>Total Questions:</strong> {{ $questions->count() }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Timing</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Start:</strong> {{ $exam->start_time->format('M d, Y h:i A') }}</li>
                                        <li><strong>End:</strong> {{ $exam->end_time->format('M d, Y h:i A') }}</li>
                                        <li><strong>Duration:</strong> {{ $exam->duration }} minutes</li>
                                    </ul>
                                </div>
                            </div>

                            @if($questions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-50px">SN</th>
                                                <th class="min-w-300px">Question</th>
                                                <th class="min-w-100px">Type</th>
                                                <th class="min-w-100px">Marks</th>
                                                <th class="min-w-200px">Options/Answers</th>
                                                <th class="min-w-100px">Correct Answer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($questions as $index => $question)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold">{!! $question->question_text !!}</div>
                                                        @if($question->image)
                                                            <div class="mt-2">
                                                                <img src="{{ asset('storage/' . $question->image) }}"
                                                                     alt="Question Image"
                                                                     class="img-thumbnail"
                                                                     style="max-height: 150px;">
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge
                                                            @if($question->type === 'mcq') bg-primary
                                                            @elseif($question->type === 'true_false') bg-info
                                                            @else bg-success @endif">
                                                            {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $question->marks }}</td>
                                                    <td>
                                                        @if($question->type === 'mcq')
                                                            <ul class="list-unstyled mb-0">
                                                                @php
                                                                    $labels = ['A', 'B', 'C', 'D', 'E'];
                                                                    $optionIndex = 0;
                                                                @endphp
                                                                @foreach($question->options as $option)
                                                                    @if($option->option_text)
                                                                        <li>
                                                                            <strong>{{ $labels[$optionIndex] }}:</strong>
                                                                            {{ $option->option_text }}
                                                                        </li>
                                                                        @php $optionIndex++; @endphp
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        @elseif($question->type === 'true_false')
                                                            <div>True / False</div>
                                                        @elseif($question->type === 'short_answer')
                                                            <div>Short Answer Question</div>
                                                        @endif
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
                                                                            <span class="badge bg-success">{{ $labels[$optionIndex] }}</span>
                                                                        @endif
                                                                        @if($opt->option_text) @php $optionIndex++; @endphp @endif
                                                                    @endforeach
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
                                                                    <div><strong>Answer:</strong> {{ $option->option_text }}</div>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="display-5 text-muted mb-3">
                                        <i class="ph-question ph-2x"></i>
                                    </div>
                                    <h4 class="mb-3">No Questions Found</h4>
                                    <p class="text-muted mb-4">This exam doesn't have any questions yet.</p>
                                    <a href="{{ route('questions.create') }}" class="btn btn-primary">
                                        <i class="ph-plus-circle me-2"></i> Add Questions
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
