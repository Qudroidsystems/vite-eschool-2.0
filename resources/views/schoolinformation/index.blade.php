@extends('layouts.master')
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">School Information</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">School Management</a></li>
                                <li class="breadcrumb-item active">School Information</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->
            <!-- Schools by Status Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Schools by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="schoolsByStatusChart" data-status='@json($status_counts)' height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
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
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div id="schoolList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search schools">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idStatus" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Status</option>
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div>
                                            <select class="form-control" id="idEmail" data-choices data-choices-search-false data-choices-removeItem>
                                                <option value="all">Select Email</option>
                                                @foreach ($data as $school)
                                                    <option value="{{ $school->school_email }}">{{ $school->school_email }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filters</button>
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
                                    <h5 class="card-title mb-0">Schools <span class="badge bg-dark-subtle text-dark ms-1">{{ $data->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create schoolinformation')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Add School</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="schoolList">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="name">Name</th>
                                                <th class="sort cursor-pointer" data-sort="email">Email</th>
                                                <th class="sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="sort cursor-pointer" data-sort="no_of_times_school_opened">Times Opened</th>
                                                <th class="sort cursor-pointer" data-sort="date_school_opened">Date Opened</th>
                                                <th class="sort cursor-pointer" data-sort="date_next_term_begins">Next Term Begins</th>
                                                <th class="sort cursor-pointer" data-sort="created_at">Date Created</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($data as $school)
                                                <tr>
                                                    <td class="id" data-id="{{ $school->id }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="name" data-name="{{ $school->school_name }}" data-address="{{ $school->school_address }}" data-motto="{{ $school->school_motto }}" data-website="{{ $school->school_website }}">
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <h6 class="mb-0"><a href="{{ route('admin.school-info.show', $school->id) }}" class="text-reset products">{{ $school->school_name }}</a></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="email" data-email="{{ $school->school_email }}">{{ $school->school_email }}</td>
                                                    <td class="status" data-status="{{ $school->is_active ? 'Active' : 'Inactive' }}">
                                                        <label class="badge bg-{{ $school->is_active ? 'success' : 'secondary' }}">{{ $school->is_active ? 'Active' : 'Inactive' }}</label>
                                                    </td>
                                                    <td class="no_of_times_school_opened" data-no_of_times_school_opened="{{ $school->no_of_times_school_opened }}">{{ $school->no_of_times_school_opened }}</td>
                                                    <td class="date_school_opened" data-date_school_opened="{{ $school->date_school_opened ? $school->date_school_opened->format('Y-m-d') : '' }}">{{ $school->date_school_opened ? $school->date_school_opened->format('Y-m-d') : '-' }}</td>
                                                    <td class="date_next_term_begins" data-date_next_term_begins="{{ $school->date_next_term_begins ? $school->date_next_term_begins->format('Y-m-d') : '' }}">{{ $school->date_next_term_begins ? $school->date_next_term_begins->format('Y-m-d') : '-' }}</td>
                                                    <td class="created_at">{{ $school->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('View schoolinformation')
                                                                <li>
                                                                    <a href="{{ route('admin.school-info.show', $school->id) }}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Update schoolinformation')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="{{ $school->id }}"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete schoolinformation')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="{{ $school->id }}"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $data->count() }}</span> of <span class="fw-semibold">{{ $data->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                                            <a class="page-item pagination-prev {{ $data->onFirstPage() ? 'disabled' : '' }}" href="{{ $data->previousPageUrl() }}">
                                                <i class="mdi mdi-chevron-left align-middle"></i>
                                            </a>
                                            <ul class="pagination listjs-pagination mb-0">
                                                @foreach ($data->links()->elements[0] as $page => $url)
                                                    <li class="page-item {{ $data->currentPage() == $page ? 'active' : '' }}">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <a class="page-item pagination-next {{ $data->hasMorePages() ? '' : 'disabled' }}" href="{{ $data->nextPageUrl() }}">
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
            <!-- Add School Modal -->
            <div id="showModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add School</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-school-form" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                                            <input type="text" id="school_name" name="school_name" class="form-control" placeholder="Enter school name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" id="school_email" name="school_email" class="form-control" placeholder="Enter school email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="school_address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea id="school_address" name="school_address" class="form-control" placeholder="Enter school address" rows="2" required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="text" id="school_phone" name="school_phone" class="form-control" placeholder="Enter school phone" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_website" class="form-label">Website</label>
                                            <input type="url" id="school_website" name="school_website" class="form-control" placeholder="https://example.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_motto" class="form-label">Motto</label>
                                            <input type="text" id="school_motto" name="school_motto" class="form-control" placeholder="Enter school motto">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="no_of_times_school_opened" class="form-label">Times Opened <span class="text-danger">*</span></label>
                                            <input type="number" id="no_of_times_school_opened" name="no_of_times_school_opened" class="form-control" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_school_opened" class="form-label">Date School Opened</label>
                                            <input type="date" id="date_school_opened" name="date_school_opened" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_next_term_begins" class="form-label">Next Term Begins</label>
                                            <input type="date" id="date_next_term_begins" name="date_next_term_begins" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <!-- School Logo Section with Cropper -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="card-title mb-0">School Logo</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="school_logo" class="form-label">Upload School Logo</label>
                                                    <input type="file" id="school_logo" name="school_logo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                                    <small class="text-muted">For official documents, letterheads, etc. (Recommended: 300x300px)</small>
                                                </div>
                                                <!-- School Logo Cropper -->
                                                <div id="school-logo-cropper-container" class="d-none">
                                                    <div class="cropper-container mb-3">
                                                        <img id="school-logo-cropper" style="max-width: 100%; max-height: 300px;">
                                                    </div>
                                                    <div class="cropper-controls mb-3">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Width (px)</label>
                                                                <input type="number" id="school-crop-width" class="form-control" value="300" min="100" max="1000">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Height (px)</label>
                                                                <input type="number" id="school-crop-height" class="form-control" value="300" min="100" max="1000">
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <button type="button" id="school-crop-btn" class="btn btn-primary btn-sm me-2">Crop Image</button>
                                                            <button type="button" id="school-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="school-logo-preview" class="text-center mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="card-title mb-0">App Logo (For Website)</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="app_logo" class="form-label">Upload App Logo</label>
                                                    <input type="file" id="app_logo" name="app_logo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                                    <small class="text-muted">For website header, favicon, login page (Recommended: 200x200px, PNG)</small>
                                                </div>
                                                <!-- App Logo Cropper -->
                                                <div id="app-logo-cropper-container" class="d-none">
                                                    <div class="cropper-container mb-3">
                                                        <img id="app-logo-cropper" style="max-width: 100%; max-height: 300px;">
                                                    </div>
                                                    <div class="cropper-controls mb-3">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Width (px)</label>
                                                                <input type="number" id="app-crop-width" class="form-control" value="200" min="50" max="1000">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Height (px)</label>
                                                                <input type="number" id="app-crop-height" class="form-control" value="200" min="50" max="1000">
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <button type="button" id="app-crop-btn" class="btn btn-primary btn-sm me-2">Crop Image</button>
                                                            <button type="button" id="app-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="app-logo-preview" class="text-center mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                                        <label class="form-check-label" for="is_active">Set as Active School</label>
                                        <small class="text-muted d-block mt-1">Only one school can be active at a time</small>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="add-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add School</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Edit School Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit School</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-school-form" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                                            <input type="text" id="edit_school_name" name="school_name" class="form-control" placeholder="Enter school name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_school_email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" id="edit_school_email" name="school_email" class="form-control" placeholder="Enter school email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea id="edit_school_address" name="school_address" class="form-control" placeholder="Enter school address" rows="2" required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_school_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="text" id="edit_school_phone" name="school_phone" class="form-control" placeholder="Enter school phone" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_school_website" class="form-label">Website</label>
                                            <input type="url" id="edit_school_website" name="school_website" class="form-control" placeholder="https://example.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_school_motto" class="form-label">Motto</label>
                                            <input type="text" id="edit_school_motto" name="school_motto" class="form-control" placeholder="Enter school motto">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_no_of_times_school_opened" class="form-label">Times Opened <span class="text-danger">*</span></label>
                                            <input type="number" id="edit_no_of_times_school_opened" name="no_of_times_school_opened" class="form-control" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_date_school_opened" class="form-label">Date School Opened</label>
                                            <input type="date" id="edit_date_school_opened" name="date_school_opened" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_date_next_term_begins" class="form-label">Next Term Begins</label>
                                            <input type="date" id="edit_date_next_term_begins" name="date_next_term_begins" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <!-- School Logo Section with Cropper -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="card-title mb-0">School Logo</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="edit_school_logo" class="form-label">Upload School Logo</label>
                                                    <input type="file" id="edit_school_logo" name="school_logo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                                    <small class="text-muted">For official documents, letterheads, etc. (Recommended: 300x300px)</small>
                                                </div>
                                                <!-- School Logo Cropper -->
                                                <div id="edit-school-logo-cropper-container" class="d-none">
                                                    <div class="cropper-container mb-3">
                                                        <img id="edit-school-logo-cropper" style="max-width: 100%; max-height: 300px;">
                                                    </div>
                                                    <div class="cropper-controls mb-3">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Width (px)</label>
                                                                <input type="number" id="edit-school-crop-width" class="form-control" value="300" min="100" max="1000">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Height (px)</label>
                                                                <input type="number" id="edit-school-crop-height" class="form-control" value="300" min="100" max="1000">
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <button type="button" id="edit-school-crop-btn" class="btn btn-primary btn-sm me-2">Crop Image</button>
                                                            <button type="button" id="edit-school-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="edit-school-logo-preview" class="text-center mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="card-title mb-0">App Logo (For Website)</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="edit_app_logo" class="form-label">Upload App Logo</label>
                                                    <input type="file" id="edit_app_logo" name="app_logo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                                    <small class="text-muted">For website header, favicon, login page (Recommended: 200x200px, PNG)</small>
                                                </div>
                                                <!-- App Logo Cropper -->
                                                <div id="edit-app-logo-cropper-container" class="d-none">
                                                    <div class="cropper-container mb-3">
                                                        <img id="edit-app-logo-cropper" style="max-width: 100%; max-height: 300px;">
                                                    </div>
                                                    <div class="cropper-controls mb-3">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Width (px)</label>
                                                                <input type="number" id="edit-app-crop-width" class="form-control" value="200" min="50" max="1000">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Height (px)</label>
                                                                <input type="number" id="edit-app-crop-height" class="form-control" value="200" min="50" max="1000">
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <button type="button" id="edit-app-crop-btn" class="btn btn-primary btn-sm me-2">Crop Image</button>
                                                            <button type="button" id="edit-app-reset-crop-btn" class="btn btn-secondary btn-sm">Reset</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="edit-app-logo-preview" class="text-center mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                        <label class="form-check-label" for="edit_is_active">Set as Active School</label>
                                        <small class="text-muted d-block mt-1">Only one school can be active at a time</small>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update School</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Delete School Modal -->
            <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-md-5">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="bi bi-trash display-4"></i>
                                </div>
                                <div class="mt-4">
                                    <h3 class="mb-2">Are you sure?</h3>
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this school?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                <button type="button" class="btn w-sm btn-light btn-hover" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn w-sm btn-danger btn-hover" id="delete-record">Yes, Delete It!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
    </div>
</div>

<!-- Include Cropper.js -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize variables for cropper
    let schoolLogoCropper = null;
    let appLogoCropper = null;
    let editSchoolLogoCropper = null;
    let editAppLogoCropper = null;

    // Edit button click handler
    $(document).on('click', '.edit-item-btn', function() {
        const id = $(this).data('id');

        // Clear previous data
        $('#edit-school-form')[0].reset();
        $('#edit-school-logo-preview').html('');
        $('#edit-app-logo-preview').html('');

        // Show loading state
        $('#update-btn').prop('disabled', true).text('Loading...');

        // Fetch school data
        $.ajax({
            url: '/school-info/' + id + '/edit-json',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const school = response.school;

                    // Populate form fields
                    $('#edit-id-field').val(school.id);
                    $('#edit_school_name').val(school.school_name);
                    $('#edit_school_address').val(school.school_address);
                    $('#edit_school_phone').val(school.school_phone);
                    $('#edit_school_email').val(school.school_email);
                    $('#edit_school_motto').val(school.school_motto || '');
                    $('#edit_school_website').val(school.school_website || '');
                    $('#edit_no_of_times_school_opened').val(school.no_of_times_school_opened);
                    $('#edit_date_school_opened').val(school.date_school_opened || '');
                    $('#edit_date_next_term_begins').val(school.date_next_term_begins || '');
                    $('#edit_is_active').prop('checked', school.is_active);

                    // Display current logos
                    if (school.logo_url) {
                        $('#edit-school-logo-preview').html(
                            '<img src="' + school.logo_url + '" class="img-thumbnail" style="max-height: 100px;">' +
                            '<p class="text-muted mt-1 mb-0">Current Logo</p>'
                        );
                    }

                    if (school.app_logo_url) {
                        $('#edit-app-logo-preview').html(
                            '<img src="' + school.app_logo_url + '" class="img-thumbnail" style="max-height: 100px;">' +
                            '<p class="text-muted mt-1 mb-0">Current App Logo</p>'
                        );
                    }

                    // Show modal
                    $('#editModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load school data'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load school data. Please try again.'
                });
            },
            complete: function() {
                $('#update-btn').prop('disabled', false).text('Update School');
            }
        });
    });

    // Edit form submission
    $('#edit-school-form').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = $('#edit-id-field').val();

        // Show loading state
        $('#update-btn').prop('disabled', true).text('Updating...');
        $('#edit-alert-error-msg').addClass('d-none').text('');

        $.ajax({
            url: '/school-info/' + id,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    $('#edit-alert-error-msg').removeClass('d-none').text(response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating the school.';

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                $('#edit-alert-error-msg').removeClass('d-none').html(errorMessage);
            },
            complete: function() {
                $('#update-btn').prop('disabled', false).text('Update School');
            }
        });
    });

    // Add form submission
    $('#add-school-form').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Show loading state
        $('#add-btn').prop('disabled', true).text('Adding...');
        $('#add-alert-error-msg').addClass('d-none').text('');

        $.ajax({
            url: '/school-info',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#showModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    $('#add-alert-error-msg').removeClass('d-none').text(response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while adding the school.';

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                $('#add-alert-error-msg').removeClass('d-none').html(errorMessage);
            },
            complete: function() {
                $('#add-btn').prop('disabled', false).text('Add School');
            }
        });
    });

    // Delete functionality
    let deleteId = null;

    $(document).on('click', '.remove-item-btn', function() {
        deleteId = $(this).data('id');
        $('#deleteRecordModal').modal('show');
    });

    $('#delete-record').on('click', function() {
        if (!deleteId) return;

        $(this).prop('disabled', true).text('Deleting...');

        $.ajax({
            url: '/school-info/' + deleteId,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteRecordModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete school. Please try again.'
                });
            },
            complete: function() {
                $('#delete-record').prop('disabled', false).text('Yes, Delete It!');
                deleteId = null;
            }
        });
    });

    // Image cropper functionality
    initializeCroppers();

    function initializeCroppers() {
        // Add form croppers
        initializeCropper('school_logo', 'school-logo-cropper', 'school-logo-cropper-container', 'school-crop-btn', 'school-reset-crop-btn', 'school-crop-width', 'school-crop-height');
        initializeCropper('app_logo', 'app-logo-cropper', 'app-logo-cropper-container', 'app-crop-btn', 'app-reset-crop-btn', 'app-crop-width', 'app-crop-height');

        // Edit form croppers
        initializeCropper('edit_school_logo', 'edit-school-logo-cropper', 'edit-school-logo-cropper-container', 'edit-school-crop-btn', 'edit-school-reset-crop-btn', 'edit-school-crop-width', 'edit-school-crop-height');
        initializeCropper('edit_app_logo', 'edit-app-logo-cropper', 'edit-app-logo-cropper-container', 'edit-app-crop-btn', 'edit-app-reset-crop-btn', 'edit-app-crop-width', 'edit-app-crop-height');
    }

    function initializeCropper(inputId, cropperId, containerId, cropBtnId, resetBtnId, widthInputId, heightInputId) {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        let cropper = null;

        if (input) {
            input.addEventListener('change', function(e) {
                const files = e.target.files;

                if (files && files.length > 0) {
                    const file = files[0];
                    const reader = new FileReader();

                    reader.onload = function(event) {
                        container.classList.remove('d-none');
                        const image = document.getElementById(cropperId);
                        image.src = event.target.result;

                        // Destroy previous cropper instance
                        if (cropper) {
                            cropper.destroy();
                        }

                        // Initialize new cropper
                        cropper = new Cropper(image, {
                            aspectRatio: NaN,
                            viewMode: 1,
                            autoCropArea: 1,
                            responsive: true,
                            restore: false
                        });

                        // Set crop dimensions based on input values
                        const widthInput = document.getElementById(widthInputId);
                        const heightInput = document.getElementById(heightInputId);

                        if (widthInput && heightInput) {
                            widthInput.value = inputId.includes('school') ? 300 : 200;
                            heightInput.value = inputId.includes('school') ? 300 : 200;
                        }
                    };

                    reader.readAsDataURL(file);
                }
            });
        }

        // Crop button
        const cropBtn = document.getElementById(cropBtnId);
        if (cropBtn) {
            cropBtn.addEventListener('click', function() {
                if (cropper) {
                    const width = parseInt(document.getElementById(widthInputId).value) || 300;
                    const height = parseInt(document.getElementById(heightInputId).value) || 300;

                    const canvas = cropper.getCroppedCanvas({
                        width: width,
                        height: height
                    });

                    // Convert canvas to blob
                    canvas.toBlob(function(blob) {
                        // Create a new File from the blob
                        const file = new File([blob], 'cropped_' + input.files[0].name, {
                            type: blob.type,
                            lastModified: Date.now()
                        });

                        // Create a new DataTransfer object
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);

                        // Replace the file in the input
                        input.files = dataTransfer.files;

                        // Show preview
                        const previewDiv = inputId.includes('edit') ?
                            (inputId.includes('school') ? 'edit-school-logo-preview' : 'edit-app-logo-preview') :
                            (inputId.includes('school') ? 'school-logo-preview' : 'app-logo-preview');

                        const previewUrl = canvas.toDataURL();
                        $('#' + previewDiv).html(
                            '<img src="' + previewUrl + '" class="img-thumbnail" style="max-height: 100px;">' +
                            '<p class="text-muted mt-1 mb-0">Cropped Image (' + width + 'x' + height + 'px)</p>'
                        );

                        // Hide cropper container
                        container.classList.add('d-none');

                        // Destroy cropper
                        cropper.destroy();
                        cropper = null;
                    }, 'image/jpeg', 0.95);
                }
            });
        }

        // Reset button
        const resetBtn = document.getElementById(resetBtnId);
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (cropper) {
                    cropper.reset();
                }
            });
        }
    }

    // Modal cleanup
    $('#editModal').on('hidden.bs.modal', function() {
        $('#edit-school-form')[0].reset();
        $('#edit-school-logo-preview').html('');
        $('#edit-app-logo-preview').html('');
        $('#edit-alert-error-msg').addClass('d-none').text('');

        // Clear file inputs
        $('#edit_school_logo').val('');
        $('#edit_app_logo').val('');
    });

    $('#showModal').on('hidden.bs.modal', function() {
        $('#add-school-form')[0].reset();
        $('#school-logo-preview').html('');
        $('#app-logo-preview').html('');
        $('#add-alert-error-msg').addClass('d-none').text('');

        // Clear file inputs
        $('#school_logo').val('');
        $('#app_logo').val('');
    });

    // Chart initialization
    initializeStatusChart();

    function initializeStatusChart() {
        const canvas = document.getElementById('schoolsByStatusChart');
        if (!canvas) return;

        const statusData = JSON.parse(canvas.getAttribute('data-status'));

        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderColor: [
                        'rgb(40, 167, 69)',
                        'rgb(108, 117, 125)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed + ' schools';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Filter functionality
    window.filterData = function() {
        const status = $('#idStatus').val();
        const email = $('#idEmail').val();

        $('tbody tr').each(function() {
            const rowStatus = $(this).find('.status').data('status');
            const rowEmail = $(this).find('.email').data('email');
            let showRow = true;

            if (status !== 'all' && rowStatus !== status) {
                showRow = false;
            }

            if (email !== 'all' && rowEmail !== email) {
                showRow = false;
            }

            if (showRow) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    };

    // Search functionality
    $('.search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        $('tbody tr').each(function() {
            const name = $(this).find('.name').data('name').toLowerCase();
            const address = $(this).find('.name').data('address').toLowerCase();

            if (name.includes(searchTerm) || address.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Check all functionality
    $('#checkAll').on('change', function() {
        $('input[name="chk_child"]').prop('checked', this.checked);
        toggleRemoveActionsButton();
    });

    $(document).on('change', 'input[name="chk_child"]', function() {
        if ($('input[name="chk_child"]:checked').length === $('input[name="chk_child"]').length) {
            $('#checkAll').prop('checked', true);
        } else {
            $('#checkAll').prop('checked', false);
        }
        toggleRemoveActionsButton();
    });

    function toggleRemoveActionsButton() {
        if ($('input[name="chk_child"]:checked').length > 0) {
            $('#remove-actions').removeClass('d-none');
        } else {
            $('#remove-actions').addClass('d-none');
        }
    }

    window.deleteMultiple = function() {
        const selectedIds = [];
        $('input[name="chk_child"]:checked').each(function() {
            selectedIds.push($(this).closest('tr').find('.id').data('id'));
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one school to delete.'
            });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Delete Multiple Schools',
            text: 'Are you sure you want to delete ' + selectedIds.length + ' selected school(s)?',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/school-info/bulk-delete',
                    method: 'DELETE',
                    data: {
                        ids: selectedIds
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete schools. Please try again.'
                        });
                    }
                });
            }
        });
    };
});
</script>
@endsection
