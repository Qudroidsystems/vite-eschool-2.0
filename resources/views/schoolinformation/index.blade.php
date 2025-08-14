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
                            <canvas id="schoolsByStatusChart" height="100"></canvas>
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
                                                                <h6 class="mb-0"><a href="{{ route('school-information.show', $school->id) }}" class="text-reset products">{{ $school->school_name }}</a></h6>
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
                                                                    <a href="{{ route('school-information.show', $school->id) }}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Update schoolinformation')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan 
                                                            @can('Delete schoolinformation') 
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="noresult" style="display: block;">No results found</td>
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
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add School</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-school-form" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <div class="mb-3">
                                    <label for="school_name" class="form-label">School Name</label>
                                    <input type="text" id="school_name" name="school_name" class="form-control" placeholder="Enter school name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="school_address" class="form-label">Address</label>
                                    <textarea id="school_address" name="school_address" class="form-control" placeholder="Enter school address" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="school_phone" class="form-label">Phone</label>
                                    <input type="text" id="school_phone" name="school_phone" class="form-control" placeholder="Enter school phone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="school_email" class="form-label">Email</label>
                                    <input type="email" id="school_email" name="school_email" class="form-control" placeholder="Enter school email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="school_logo" class="form-label">Logo</label>
                                    <input type="file" id="school_logo" name="school_logo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                                </div>
                                <div class="mb-3">
                                    <label for="school_motto" class="form-label">Motto</label>
                                    <input type="text" id="school_motto" name="school_motto" class="form-control" placeholder="Enter school motto">
                                </div>
                                <div class="mb-3">
                                    <label for="school_website" class="form-label">Website</label>
                                    <input type="url" id="school_website" name="school_website" class="form-control" placeholder="Enter school website">
                                </div>
                                <div class="mb-3">
                                    <label for="no_of_times_school_opened" class="form-label">Number of Times School Opened</label>
                                    <input type="number" id="no_of_times_school_opened" name="no_of_times_school_opened" class="form-control" placeholder="Enter number of times school opened" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="date_school_opened" class="form-label">Date School Opened</label>
                                    <input type="date" id="date_school_opened" name="date_school_opened" class="form-control" placeholder="Select date school opened">
                                </div>
                                <div class="mb-3">
                                    <label for="date_next_term_begins" class="form-label">Date Next Term Begins</label>
                                    <input type="date" id="date_next_term_begins" name="date_next_term_begins" class="form-control" placeholder="Select date next term begins">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
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
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit School</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-school-form" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <div class="mb-3">
                                    <label for="edit_school_name" class="form-label">School Name</label>
                                    <input type="text" id="edit_school_name" name="school_name" class="form-control" placeholder="Enter school name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_address" class="form-label">Address</label>
                                    <textarea id="edit_school_address" name="school_address" class="form-control" placeholder="Enter school address" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_phone" class="form-label">Phone</label>
                                    <input type="text" id="edit_school_phone" name="school_phone" class="form-control" placeholder="Enter school phone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_email" class="form-label">Email</label>
                                    <input type="email" id="edit_school_email" name="school_email" class="form-control" placeholder="Enter school email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_logo" class="form-label">Logo</label>
                                    <input type="file" id="edit_school_logo" name="school_logo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_motto" class="form-label">Motto</label>
                                    <input type="text" id="edit_school_motto" name="school_motto" class="form-control" placeholder="Enter school motto">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_school_website" class="form-label">Website</label>
                                    <input type="url" id="edit_school_website" name="school_website" class="form-control" placeholder="Enter school website">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_no_of_times_school_opened" class="form-label">Number of Times School Opened</label>
                                    <input type="number" id="edit_no_of_times_school_opened" name="no_of_times_school_opened" class="form-control" placeholder="Enter number of times school opened" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_date_school_opened" class="form-label">Date School Opened</label>
                                    <input type="date" id="edit_date_school_opened" name="date_school_opened" class="form-control" placeholder="Select date school opened">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_date_next_term_begins" class="form-label">Date Next Term Begins</label>
                                    <input type="date" id="edit_date_next_term_begins" name="date_next_term_begins" class="form-control" placeholder="Select date next term begins">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                        <label class="form-check-label" for="edit_is_active">Active</label>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
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
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this record?</p>
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
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var ctx = document.getElementById("schoolsByStatusChart").getContext("2d");
                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: @json(array_keys($status_counts)),
                        datasets: [{
                            label: "Schools by Status",
                            data: @json(array_values($status_counts)),
                            backgroundColor: ["#28a745", "#6c757d"],
                            borderColor: ["#28a745", "#6c757d"],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Number of Schools"
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: "Status"
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top"
                            }
                        }
                    }
                });
            });
        </script>
    </div>
@endsection