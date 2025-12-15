@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Principals Comment Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Principals Comment</a></li>
                                <li class="breadcrumb-item active">Assignments</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div id="principalsCommentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search assignments...">
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
                                    <h5 class="card-title mb-0">
                                        Principals Comment Assignments 
                                        <span class="badge bg-dark-subtle text-dark ms-1" id="total-records">{{ $principalscomments->count() }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    @can('Create principals-comment')
                                        <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addPrincipalsCommentModal">
                                            <i class="bi bi-plus-circle align-baseline me-1"></i> Create Assignment
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th>SN</th>
                                                <th>Staff</th>
                                                <th>Class</th>
                                                <th>Arm</th>
                                                <th>Session</th>
                                                <th>Term</th>
                                                <th>Date Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            @php $i = 0 @endphp
                                            @forelse ($principalscomments as $assignment)
                                                @php
                                                    $picture = $assignment->picture ?? 'unnamed.jpg';
                                                    $imagePath = asset('storage/staff_avatars/' . $picture);
                                                @endphp
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                                <img src="{{ $imagePath }}" alt="{{ $assignment->staffname }}" class="rounded-circle avatar-md staff-image"
                                                                     data-bs-toggle="modal" data-bs-target="#imageViewModal"
                                                                     data-image="{{ $imagePath }}" data-teachername="{{ $assignment->staffname }}"
                                                                     onerror="this.src='{{ asset('storage/staff_avatars/unnamed.jpg') }}';" />
                                                            </div>
                                                            <div>{{ $assignment->staffname }}</div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $assignment->sclass }}</td>
                                                    <td>{{ $assignment->schoolarm ?? 'N/A' }}</td>
                                                    <td>{{ $assignment->session_name }}</td>
                                                    <td>{{ $assignment->term_name }}</td>
                                                    <td>{{ $assignment->updated_at->format('d M Y') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update principals-comment')
                                                                <li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a></li>
                                                            @endcan
                                                            @can('Delete principals-comment')
                                                                <li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-url="{{ route('principalscomment.destroy', $assignment->pcid) }}"><i class="ph-trash"></i></a></li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">No assignments found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Modal -->
                <div class="modal fade" id="addPrincipalsCommentModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="add-principalscomment-form">
                                @csrf
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label>Staff Member *</label>
                                            <select name="staffId" class="form-control" required>
                                                <option value="">Select Staff</option>
                                                @foreach ($staff as $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Session *</label>
                                            <select name="sessionid" class="form-control" required>
                                                <option value="">Select Session</option>
                                                @foreach ($sessions as $session)
                                                    <option value="{{ $session->id }}" {{ $session->status == 'Current' ? 'selected' : '' }}>
                                                        {{ $session->session }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Term *</label>
                                            <select name="termid" class="form-control" required>
                                                <option value="">Select Term</option>
                                                @foreach ($terms as $term)
                                                    <option value="{{ $term->id }}">{{ $term->term }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Classes *</label>
                                            <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                                @foreach ($schoolclasses as $class)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="schoolclassid[]" value="{{ $class->id }}">
                                                        <label class="form-check-label">
                                                            {{ $class->schoolclass }} ({{ $class->arm ?? 'No Arm' }})
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-danger mt-3 d-none" id="alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="add-btn">Add Assignment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <script src="{{ asset('js/pages/principalscomment.init.js') }}"></script> --}}
@endsection