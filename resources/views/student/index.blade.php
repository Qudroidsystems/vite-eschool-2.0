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
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
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
                                <label for="student_category" class="form-label">Student Category <span class="text-danger">*</span></label>
                                <select id="student_category" name="student_category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Day">Day</option>
                                    <option value="Boarding">Boarding</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nin_number" class="form-label">NIN Number</label>
                                <input type="text" id="nin_number" name="nin_number" class="form-control" placeholder="Enter NIN number (11 digits)">
                            </div>
                            <div class="mb-3">
                                <label for="blood_group" class="form-label">Blood Group <span class="text-danger">*</span></label>
                                <select id="blood_group" name="blood_group" class="form-control" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="mother_tongue" class="form-label">Mother Tongue <span class="text-danger">*</span></label>
                                <input type="text" id="mother_tongue" name="mother_tongue" class="form-control" placeholder="Enter mother tongue" required>
                            </div>
                            <div class="mb-3">
                                <label for="reason_for_leaving" class="form-label">Reason for Leaving Previous School <span class="text-danger">*</span></label>
                                <input type="text" id="reason_for_leaving" name="reason_for_leaving" class="form-control" placeholder="Enter reason for leaving" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_school" class="form-label">Last School Attended <span class="text-danger">*</span></label>
                                <input type="text" id="last_school" name="last_school" class="form-control" placeholder="Enter last school attended" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_class" class="form-label">Last Class Attended <span class="text-danger">*</span></label>
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
                                <label for="editStudentCategory" class="form-label">Student Category <span class="text-danger">*</span></label>
                                <select id="editStudentCategory" name="student_category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Day">Day</option>
                                    <option value="Boarding">Boarding</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editNinNumber" class="form-label">NIN Number</label>
                                <input type="text" id="editNinNumber" name="nin_number" class="form-control" placeholder="Enter NIN number (11 digits)">
                            </div>
                            <div class="mb-3">
                                <label for="editBloodGroup" class="form-label">Blood Group <span class="text-danger">*</span></label>
                                <select id="editBloodGroup" name="blood_group" class="form-control" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editMotherTongue" class="form-label">Mother Tongue <span class="text-danger">*</span></label>
                                <input type="text" id="editMotherTongue" name="mother_tongue" class="form-control" placeholder="Enter mother tongue" required>
                            </div>
                            <div class="mb-3">
                                <label for="editReasonForLeaving" class="form-label">Reason for Leaving Previous School <span class="text-danger">*</span></label>
                                <input type="text" id="editReasonForLeaving" name="reason_for_leaving" class="form-control" placeholder="Enter reason for leaving" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastSchool" class="form-label">Last School Attended <span class="text-danger">*</span></label>
                                <input type="text" id="editLastSchool" name="last_school" class="form-control" placeholder="Enter last school attended" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastClass" class="form-label">Last Class Attended <span class="text-danger">*</span></label>
                                <input type="text" id="editLastClass" name="last_class" class="form-control" placeholder="Enter last class attended" required>
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
                            <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="edit-btn">Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // Fetch states and LGAs for Add Student Modal
    document.addEventListener('DOMContentLoaded', function () {
        fetch('/states_lgas.json')
            .then(response => response.json())
            .then(data => {
                const stateSelect = document.getElementById('addState');
                const localSelect = document.getElementById('addLocal');
                const editStateSelect = document.getElementById('editState');
                const editLocalSelect = document.getElementById('editLocal');

                // Populate states
                data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.state;
                    option.textContent = state.state;
                    stateSelect.appendChild(option);
                    editStateSelect.appendChild(option.cloneNode(true));
                });

                // Handle state change for Add Student Modal
                stateSelect.addEventListener('change', function () {
                    localSelect.innerHTML = '<option value="">Select Local Government</option>';
                    const selectedState = data.find(state => state.state === this.value);
                    if (selectedState) {
                        selectedState.lgas.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga;
                            option.textContent = lga;
                            localSelect.appendChild(option);
                        });
                    }
                });

                // Handle state change for Edit Student Modal
                editStateSelect.addEventListener('change', function () {
                    editLocalSelect.innerHTML = '<option value="">Select Local Government</option>';
                    const selectedState = data.find(state => state.state === this.value);
                    if (selectedState) {
                        selectedState.lgas.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga;
                            option.textContent = lga;
                            editLocalSelect.appendChild(option);
                        });
                    }
                });
            });

        // Avatar preview for Add Student Modal
        document.getElementById('avatar').addEventListener('change', function (e) {
            const addStudentAvatar = document.getElementById('addStudentAvatar');
            if (e.target.files && e.target.files[0]) {
                addStudentAvatar.src = URL.createObjectURL(e.target.files[0]);
                addStudentAvatar.style.display = 'block';
            } else {
                addStudentAvatar.src = "{{ asset('theme/layouts/assets/media/avatars/blank.png') }}";
                addStudentAvatar.style.display = 'none';
            }
        });

        // Avatar preview for Edit Student Modal
        document.getElementById('editAvatar').addEventListener('change', function (e) {
            const editStudentAvatar = document.getElementById('editStudentAvatar');
            if (e.target.files && e.target.files[0]) {
                editStudentAvatar.src = URL.createObjectURL(e.target.files[0]);
                editStudentAvatar.style.display = 'block';
            } else {
                editStudentAvatar.src = "{{ asset('theme/layouts/assets/media/avatars/blank.png') }}";
                editStudentAvatar.style.display = 'block';
            }
        });

        // Age calculation
        window.showage = function (date, displayId = 'addAge') {
            if (date) {
                const dob = new Date(date);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const monthDiff = today.getMonth() - dob.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                document.getElementById(displayId).textContent = age + ' years';
                document.getElementById(displayId === 'addAge' ? 'addAgeInput' : 'editAgeInput').value = age;
            } else {
                document.getElementById(displayId).textContent = '';
                document.getElementById(displayId === 'addAge' ? 'addAgeInput' : 'editAgeInput').value = '';
            }
        };
    });
</script>

@endsection