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
                                <li class="breadcrumb-item"><a href="{{ route('exams.students', $exam->id) }}">Students</a></li>
                                <li class="breadcrumb-item active">Answers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            @if($result)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>Overall Score:</strong> {{ $result->score }} / {{ $result->total_marks }}
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">Question Answers</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-125px">SN</th>
                                            <th class="min-w-125px">Question</th>
                                            <th class="min-w-125px">Image</th>
                                            <th class="min-w-125px">Student Answer</th>
                                            <th class="min-w-125px">Correct Answer</th>
                                            <th class="min-w-125px">Marked Correct</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        @php $i = 1 @endphp
                                        @foreach ($questionAnswers as $qa)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $qa->question_text }}</td>
                                                <td>
                                                    @if($qa->image)
                                                        <img src="{{ asset('storage/' . $qa->image) }}" class="img-fluid rounded" alt="Question Image" style="max-width: 100px; max-height: 100px;">
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $qa->student_answer ?? 'Not Attempted' }}</td>
                                                <td>{{ $qa->correct_answer ?? '-' }}</td>
                                                <td>
                                                    <span class="badge {{ $qa->marked_correct == 'Yes' ? 'bg-success' : ($qa->marked_correct == 'Not Attempted' ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $qa->marked_correct }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
    </div>
</div>

@endsection