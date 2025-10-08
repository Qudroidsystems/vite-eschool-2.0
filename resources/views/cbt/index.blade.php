@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">My Exams</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('cbt.index') }}">Exams</a></li>
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @can('View cbt-exam')
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
                                    <h5 class="card-title mb-0">Available Exams <span class="badge bg-dark-subtle text-dark ms-1">{{ $exams->total() }}</span></h5>
                                    <p class="text-muted mb-0 fs-6">Exams for {{ $student->firstname }} {{ $student->lastname }} <span class="fs-7">({{ $class->schoolclass }} - {{ $term->term }} - {{ $session->session }})</span></p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_exams_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="title">Title</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="description">Description</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="duration">Duration</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="start_time">Start Time</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="end_time">End Time</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list">
                                            @php $i = ($exams->currentPage() - 1) * $exams->perPage() @endphp
                                            @forelse ($exams as $exam)
                                                @php
                                                    $now = now();
                                                    $start = \Carbon\Carbon::parse($exam->start_time);
                                                    $end = \Carbon\Carbon::parse($exam->end_time);
                                                @endphp
                                                <tr>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="title">{{ $exam->title }}</td>
                                                    <td class="description">{{ Str::limit($exam->description ?? '', 50) }}</td>
                                                    <td class="duration">{{ $exam->duration }} mins</td>
                                                    <td class="start_time">{{ $exam->start_time }}</td>
                                                    <td class="end_time">{{ $exam->end_time }}</td>
                                                    <td class="status">
                                                        @if ($now->lt($start))
                                                            <span class="badge badge-light-warning">Upcoming</span>
                                                        @elseif ($now->between($start, $end))
                                                            <span class="badge badge-light-success">Ongoing</span>
                                                        @else
                                                            <span class="badge badge-light-danger">Ended</span>
                                                        @endif
                                                    </td>
                                                    <td class="actions">
                                                        @if ($now->between($start, $end))
                                                            @can('Take cbt-exam')
                                                                <a href="{{ route('cbt.take', $exam->id) }}" class="btn btn-sm btn-primary">Take Exam</a>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endcan
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="noresult" style="display: block;">No exams available for your registered subjects at this time.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $exams->count() }}</span> of <span class="fw-semibold">{{ $exams->total() }}</span> Results
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

            <!-- Additional Info -->
            <div class="row mt-3">
                <div class="col-12">
                    <p class="text-muted">Total Subjects Offered: {{ $totalreg }} | Subjects Registered: {{ $reg }}</p>
                </div>
            </div>
            @endcan
        </div>
        <!-- End Page-content -->
    </div>
</div>

@endsection