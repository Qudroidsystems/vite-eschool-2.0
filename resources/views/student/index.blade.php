@extends('layouts.master')
@section('content')
<?php
use Spatie\Permission\Models\Role;
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Students</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Student Management</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Students by Status Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByStatusChart" height="100"></canvas>
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

            <div id="studentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search by name or admission no">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idClass">
                                            <option value="all">Select Class</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idStatus">
                                            <option value="all">Select Status</option>
                                            <option value="1">Old Student</option>
                                            <option value="2">New Student</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <select class="form-control" id="idGender">
                                            <option value="all">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="totalStudents">0</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        @can('Delete student')
                                            <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @endcan
                                        @can('Create student')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Add Student</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                                                        <label class="form-check-label" for="checkAll"></label>
                                                    </div>
                                                </th>
                                                <th class="sort cursor-pointer" data-sort="name">Student</th>
                                                <th class="sort cursor-pointer" data-sort="admissionNo">Admission No</th>
                                                <th class="sort cursor-pointer" data-sort="class">Class</th>
                                                <th class="sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="sort cursor-pointer" data-sort="gender">Gender</th>
                                                <th class="sort cursor-pointer" data-sort="datereg">Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all" id="studentTableBody">
                                            <!-- JS renders rows here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold" id="showingCount">0</span> of <span class="fw-semibold" id="totalCount">0</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                                            <a class="page-item pagination-prev disabled" href="javascript:void(0);" id="prevPage">
                                                <i class="mdi mdi-chevron-left align-middle"></i>
                                            </a>
                                            <ul class="pagination listjs-pagination mb-0" id="paginationLinks"></ul>
                                            <a class="page-item pagination-next" href="javascript:void(0);" id="nextPage">
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
        </div>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Add Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="addStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.store') }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="registeredBy" value="{{ Auth::user()->id }}">
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <input type="file" id="avatar" name="avatar" class="form-control" accept=".png,.jpg,.jpeg">
                                <img id="addStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" style="max-width: 100px; margin-top: 10px; display: none;" />
                                <div class="form-text">Allowed file types: png, jpg, jpeg. Max size: 2MB.</div>
                            </div>
                            <div class="mb-3">
                                <label for="admissionNo" class="form-label">Admission No <span class="text-danger">*</span></label>
                                <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="Enter admission number" required>
                            </div>
                            <div class="mb-3">
                                <label for="tittle" class="form-label">Title <span class="text-danger">*</span></label>
                                <select id="title" name="title" class="form-control" required>
                                    <option value="">Select Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Miss">Miss</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Enter first name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter last name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="othername" class="form-label">Other Names</label>
                                <input type="text" id="othername" name="othername" class="form-control" placeholder="Enter other names">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required>
                                        <label class="form-check-label" for="genderMale">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required>
                                        <label class="form-check-label" for="genderFemale">Female</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="home_address" class="form-label">Home Address 1 <span class="text-danger">*</span></label>
                                <input type="text" id="home_address" name="home_address" class="form-control" placeholder="Enter home address" required>
                            </div>
                            <div class="mb-3">
                                <label for="home_address2" class="form-label">Home Address 2 <span class="text-danger">*</span></label>
                                <input type="text" id="home_address2" name="home_address2" class="form-control" placeholder="Enter home address 2" required>
                            </div>
                            <div class="mb-3">
                                <label for="dateofbirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="showage(this.value)">
                                <input type="hidden" id="addAgeInput" name="age">
                                <span id="addAge" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <label for="placeofbirth" class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" placeholder="Enter place of birth" required>
                            </div>
                            <div class="mb-3">
                                <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                <input type="text" id="nationality" name="nationality" class="form-control" placeholder="Enter nationality" required>
                            </div>
                            <div class="mb-3">
                                <label for="state" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                <select id="addState" name="state" class="form-control" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="local" class="form-label">Local Government <span class="text-danger">*</span></label>
                                <select id="addLocal" name="local" class="form-control" required>
                                    <option value="">Select Local Government</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="religion" class="form-label">Religion <span class="text-danger">*</span></label>
                                <select id="religion" name="religion" class="form-control" required>
                                    <option value="">Select Religion</option>
                                    <option value="Christianity">Christianity</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="last_school" class="form-label">Last School Attended <span class="text-danger">*</span></label>
                                <input type="text" id="last_school" name="last_school" class="form-control" placeholder="Enter last school attended" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastClass" class="form-label">Last Class Attended <span class="text-danger">*</span></label>
                                <input type="text" id="last_class" name="last_class" class="form-control" placeholder="Enter last class attended" required>
                            </div>
                            <div class="mb-3">
                                <label for="schoolclassid" class="form-label">Class <span class="text-danger">*</span></label>
                                <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclass as $class)
                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="termid" class="form-label">Term <span class="text-danger">*</span></label>
                                <select id="termid" name="termid" class="form-control" required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterm as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                <select id="sessionid" name="sessionid" class="form-control" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsession as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="statusId" id="statusOld" value="1" required>
                                        <label class="form-check-label" for="statusOld">Old Student</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="statusId" id="statusNew" value="2" required>
                                        <label class="form-check-label" for="statusNew">New Student</label>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="add-btn">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="editStudentForm" enctype="multipart/form-data" autocomplete="true" method="POST" action="{{ route('student.update', ':id') }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                            <input type="hidden" id="editStudentId" name="id">
                            <input type="hidden" name="registeredBy" value="{{ Auth::user()->id }}">
                            <div class="mb-3">
                                <label for="editAvatar" class="form-label">Avatar</label>
                                <input type="file" id="editAvatar" name="avatar" class="form-control" accept=".png,.jpg,.jpeg">
                                <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" style="max-width: 100px; margin-top: 10px;" />
                                <div class="form-text">Allowed file types: png, jpg, jpeg. Max size: 2MB.</div>
                            </div>
                            <div class="mb-3">
                                <label for="editAdmissionNo" class="form-label">Admission No <span class="text-danger">*</span></label>
                                <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="Enter admission number" required>
                            </div>
                            <div class="mb-3">
                                <label for="editTittle" class="form-label">Title <span class="text-danger">*</span></label>
                                <select id="editTittle" name="title" class="form-control" required>
                                    <option value="">Select Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Miss">Miss</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" id="editFirstname" name="firstname" class="form-control" placeholder="Enter first name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" id="editLastname" name="lastname" class="form-control" placeholder="Enter last name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="editOthername" class="form-label">Other Names</label>
                                <input type="text" id="editOthername" name="othername" class="form-control" placeholder="Enter other names">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="Male" required>
                                        <label class="form-check-label" for="editGenderMale">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="Female" required>
                                        <label class="form-check-label" for="editGenderFemale">Female</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="editHomeAddress" class="form-label">Home Address 1 <span class="text-danger">*</span></label>
                                <input type="text" id="editHomeAddress" name="home_address" class="form-control" placeholder="Enter home address" required>
                            </div>
                            <div class="mb-3">
                                <label for="editHomeAddress2" class="form-label">Home Address 2 <span class="text-danger">*</span></label>
                                <input type="text" id="editHomeAddress2" name="home_address2" class="form-control" placeholder="Enter home address 2" required>
                            </div>
                            <div class="mb-3">
                                <label for="editDOB" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="showage(this.value, 'editAge')">
                                <input type="hidden" id="editAgeInput" name="age">
                                <span id="editAge" class="text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <label for="editPlaceofbirth" class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                <input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" placeholder="Enter place of birth" required>
                            </div>
                            <div class="mb-3">
                                <label for="editNationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                <input type="text" id="editNationality" name="nationality" class="form-control" placeholder="Enter nationality" required>
                            </div>
                            <div class="mb-3">
                                <label for="editState" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                <select id="editState" name="state" class="form-control" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editLocal" class="form-label">Local Government <span class="text-danger">*</span></label>
                                <select id="editLocal" name="local" class="form-control" required>
                                    <option value="">Select Local Government</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editReligion" class="form-label">Religion <span class="text-danger">*</span></label>
                                <select id="editReligion" name="religion" class="form-control" required>
                                    <option value="">Select Religion</option>
                                    <option value="Christianity">Christianity</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editLastSchool" class="form-label">Last School Attended <span class="text-danger">*</span></label>
                                <input type="text" id="editLastSchool" name="last_school" class="form-control" placeholder="Enter last school attended" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastClass" class="form-label">Last Class <span class="text-danger">*</span></label>
                                <input type="text" id="editLastClass" name="last_class" class="form-control" placeholder="Enter last class" required>
                            </div>
                            <div class="mb-3">
                                <label for="editSchoolclassid" class="form-label">Class <span class="text-danger">*</span></label>
                                <select id="editSchoolclassid" name="schoolclassid" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclass as $class)
                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editTermid" class="form-label">Term <span class="text-danger">*</span></label>
                                <select id="editTermid" name="termid" class="form-control" required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterm as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                <select id="editSessionid" name="sessionid" class="form-control" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsession as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="statusId" id="editStatusOld" value="1" required>
                                        <label class="form-check-label" for="editStatusOld">Old Student</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="statusId" id="editStatusNew" value="2" required>
                                        <label class="form-check-label" for="editStatusNew">New Student</label>
                                    </div>
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

        <!-- Delete Student Modal -->
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

        <!-- Image View Modal -->
        <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Student Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="enlargedImage" src="" alt="Student Image" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Page-content -->

    <!-- Include external scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/init.js') }}"></script>

    <script>
        // Define permissions for JavaScript
        window.appPermissions = {
            canShowStudent: {{ Auth::user()->hasPermissionTo('View student') ? 'true' : 'false' }},
            canUpdateStudent: {{ Auth::user()->hasPermissionTo('Update student') ? 'true' : 'false' }},
            canDeleteStudent: {{ Auth::user()->hasPermissionTo('Delete student') ? 'true' : 'false' }}
        };

        document.addEventListener("DOMContentLoaded", function () {
            if (typeof axios === 'undefined') {
                console.error('Axios is not loaded');
                alert('Axios library is missing. Please check the script inclusion.');
                return;
            }
            if (!document.querySelector('meta[name="csrf-token"]')) {
                console.error('CSRF token meta tag is missing');
                alert('CSRF token is missing. Please ensure the CSRF meta tag is included in the layout.');
                return;
            }

            var ctx = document.getElementById("studentsByStatusChart").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ["Old Student", "New Student"],
                    datasets: [{
                        label: "Students by Status",
                        data: @json(array_values($status_counts)),
                        backgroundColor: ["#4e73df", "#1cc88a"],
                        borderColor: ["#4e73df", "#1cc88a"],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Number of Students"
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

            if (typeof Choices !== 'undefined') {
                document.querySelectorAll('[data-choices]').forEach(element => {
                    new Choices(element, {
                        searchEnabled: element.dataset.choicesSearchFalse !== undefined,
                        removeItemButton: element.dataset.choicesRemoveitem !== undefined
                    });
                });
            } else {
                console.warn('Choices.js is not loaded. Select elements will function as standard dropdowns.');
            }

            axios.get('/students/data')
                .then(response => {
                    console.log('Students data response:', response.data);
                })
                .catch(error => {
                    console.error('Error fetching /students/data:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Failed to fetch students. Check the console for details.');
                });

            initializeStudentList();
        });
    </script>
</div>
@endsection