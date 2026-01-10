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

            <style>
                .card {
                    border: none;
                    border-radius: 15px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    margin-bottom: 20px;
                }

                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
                }

                .card-body {
                    padding: 25px;
                    text-align: center;
                }

                .card-icon {
                    font-size: 3rem;
                    margin-bottom: 15px;
                    display: block;
                }

                .card-title {
                    font-size: 0.95rem;
                    font-weight: 600;
                    color: #6c757d;
                    margin-bottom: 10px;
                }

                .card-text {
                    font-size: 2.5rem;
                    font-weight: bold;
                    margin: 0;
                }

                /* Color schemes for different card types */
                .population-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
                .staff-card { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
                .old-student-card { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
                .new-student-card { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
                .active-card { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
                .inactive-card { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
                .male-card { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #333; }
                .female-card { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333; }
                .christian-card { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); color: white; }
                .muslim-card { background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%); color: white; }
                .other-religion-card { background: linear-gradient(135deg, #e3ffe7 0%, #d9e7ff 100%); color: #333; }

                /* Student Card Styling */
                .student-card {
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    margin-bottom: 20px;
                    background: white;
                    position: relative;
                    overflow: hidden;
                    height: 100%;
                }
                .student-card:hover {
                    border-color: #405189;
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                    transform: translateY(-5px);
                }
                .student-card.selected {
                    border-color: #405189;
                    background-color: rgba(64, 81, 137, 0.02);
                }
                .student-card .card-body {
                    padding: 20px;
                }
                .student-card .avatar-container {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 15px auto;
                    position: relative;
                }
                .student-card .avatar {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 3px solid #fff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: white;
                }
                .student-card .avatar-initials {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: white;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .student-card .student-name {
                    font-size: 16px;
                    font-weight: 600;
                    color: #495057;
                    margin-bottom: 5px;
                    text-align: center;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .student-card .student-admission {
                    font-size: 12px;
                    color: #6c757d;
                    margin-bottom: 8px;
                    text-align: center;
                }
                .student-card .student-details {
                    font-size: 12px;
                    color: #6c757d;
                    text-align: center;
                    line-height: 1.4;
                }
                .student-card .student-details div {
                    margin-bottom: 3px;
                }
                .student-card .action-buttons {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    display: flex;
                    gap: 5px;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .student-card:hover .action-buttons {
                    opacity: 1;
                }
                .student-card .action-btn {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                .student-card .view-btn {
                    background-color: rgba(13, 110, 253, 0.1);
                    color: #0d6efd;
                }
                .student-card .edit-btn {
                    background-color: rgba(25, 135, 84, 0.1);
                    color: #198754;
                }
                .student-card .delete-btn {
                    background-color: rgba(220, 53, 69, 0.1);
                    color: #dc3545;
                }
                .student-card .action-btn:hover {
                    transform: scale(1.1);
                }
                .student-card .checkbox-container {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .student-card:hover .checkbox-container {
                    opacity: 1;
                }
                .student-card .form-check-input {
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                }
                .student-card .status-badge {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    font-size: 10px;
                    padding: 3px 8px;
                    border-radius: 12px;
                    z-index: 1;
                }
                .student-card .status-active {
                    background-color: rgba(25, 135, 84, 0.1);
                    color: #198754;
                    border: 1px solid rgba(25, 135, 84, 0.2);
                }
                .student-card .status-inactive {
                    background-color: rgba(255, 193, 7, 0.1);
                    color: #ffc107;
                    border: 1px solid rgba(255, 193, 7, 0.2);
                }
                /* Empty state */
                .empty-state {
                    text-align: center;
                    padding: 40px 20px;
                }
                .empty-state i {
                    font-size: 48px;
                    color: #6c757d;
                    margin-bottom: 20px;
                }
                .empty-state h5 {
                    color: #6c757d;
                    margin-bottom: 10px;
                }
                .empty-state p {
                    color: #6c757d;
                    margin-bottom: 0;
                }
                /* Loading state */
                .loading-state {
                    text-align: center;
                    padding: 40px 20px;
                }
                .loading-state .spinner-border {
                    width: 3rem;
                    height: 3rem;
                    margin-bottom: 20px;
                }

                /* View toggle buttons */
                .btn-group .btn-outline-secondary.active {
                    background-color: #405189;
                    color: white;
                    border-color: #405189;
                }

                /* View containers */
                .view-container {
                    transition: all 0.3s ease;
                }
            </style>

            <div class="container">
                <h2 class="mb-4 text-center">School Dashboard Statistics</h2>

                <!-- Dashboard Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card population-card">
                            <div class="card-body">
                                <i class="fas fa-users card-icon"></i>
                                <h5 class="card-title">Total Population</h5>
                                <p class="card-text">{{ $total_population }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card staff-card">
                            <div class="card-body">
                                <i class="fas fa-chalkboard-teacher card-icon"></i>
                                <h5 class="card-title">Staff Count</h5>
                                <p class="card-text">{{ $staff_count }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card old-student-card">
                            <div class="card-body">
                                <i class="fas fa-user-graduate card-icon"></i>
                                <h5 class="card-title">Old Students</h5>
                                <p class="card-text">{{ $status_counts['Old Student'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card new-student-card">
                            <div class="card-body">
                                <i class="fas fa-user-plus card-icon"></i>
                                <h5 class="card-title">New Students</h5>
                                <p class="card-text">{{ $status_counts['New Student'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card active-card">
                            <div class="card-body">
                                <i class="fas fa-user-check card-icon"></i>
                                <h5 class="card-title">Active Students</h5>
                                <p class="card-text">{{ $student_status_counts['Active'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card inactive-card">
                            <div class="card-body">
                                <i class="fas fa-user-times card-icon"></i>
                                <h5 class="card-title">Inactive Students</h5>
                                <p class="card-text">{{ $student_status_counts['Inactive'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card male-card">
                            <div class="card-body">
                                <i class="fas fa-mars card-icon"></i>
                                <h5 class="card-title">Male Students</h5>
                                <p class="card-text">{{ $gender_counts['Male'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card female-card">
                            <div class="card-body">
                                <i class="fas fa-venus card-icon"></i>
                                <h5 class="card-title">Female Students</h5>
                                <p class="card-text">{{ $gender_counts['Female'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card christian-card">
                            <div class="card-body">
                                <i class="fas fa-cross card-icon"></i>
                                <h5 class="card-title">Christian Students</h5>
                                <p class="card-text">{{ $religion_counts['Christianity'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card muslim-card">
                            <div class="card-body">
                                <i class="fas fa-moon card-icon"></i>
                                <h5 class="card-title">Muslim Students</h5>
                                <p class="card-text">{{ $religion_counts['Islam'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card other-religion-card">
                            <div class="card-body">
                                <i class="fas fa-globe card-icon"></i>
                                <h5 class="card-title">Other Religions</h5>
                                <p class="card-text">{{ $religion_counts['Others'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Students by Active/Inactive Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByActiveStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Display Error Message -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Unified Students View Container -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1 d-flex align-items-center gap-2">
                                <div class="form-check me-2">
                                    <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="totalStudents">0</span></h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    <!-- View Toggle Buttons -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary active" id="tableViewBtn" onclick="toggleView('table')">
                                            <i class="fas fa-table"></i> Table
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="cardViewBtn" onclick="toggleView('card')">
                                            <i class="fas fa-th-large"></i> Cards
                                        </button>
                                    </div>

                                    @can('Delete student')
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i> Remove Selected
                                        </button>
                                    @endcan
                                    @can('Create student')
                                        <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                            <i class="bi bi-plus-circle align-baseline me-1"></i> Add Student
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter Bar -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="search-box">
                                        <input type="text" class="form-control search" id="search-input" placeholder="Search by name or admission no">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="schoolclass-filter" data-choices data-choices-search-false>
                                        <option value="all">All Classes</option>
                                        @foreach ($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" id="status-filter" data-choices data-choices-search-false>
                                        <option value="all">All Statuses</option>
                                        <option value="1">Old Student</option>
                                        <option value="2">New Student</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" id="gender-filter" data-choices data-choices-search-false>
                                        <option value="all">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                        <i class="bi bi-funnel align-baseline me-1"></i> Filter
                                    </button>
                                </div>
                            </div>

                            <!-- Table View (Default - Visible) -->
                            <div id="tableView" class="view-container">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="option" id="checkAllTable">
                                                        <label class="form-check-label" for="checkAllTable"></label>
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
                            </div>

                            <!-- Cards View (Hidden by default) -->
                            <div id="cardView" class="view-container d-none">
                                <div class="row" id="studentsCardsContainer">
                                    <!-- Students will be rendered here as cards -->
                                </div>
                            </div>

                            <!-- Pagination -->
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

        <!-- Add Student Modal (Keep your existing modal) -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i>
                            Student Registration
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="addStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <!-- Progress Steps -->
                            <div class="progress-steps mb-4">
                                <div class="step active">1</div>
                                <div class="step">2</div>
                                <div class="step">3</div>
                                <div class="step">4</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Section A: Academic Details -->
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="admissionAuto" value="auto" required onchange="toggleAdmissionInput()">
                                                        <label class="form-check-label" for="admissionAuto">
                                                            <i class="fas fa-magic me-1"></i>Auto Generate
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="admissionManual" value="manual" required onchange="toggleAdmissionInput()">
                                                        <label class="form-check-label" for="admissionManual">
                                                            <i class="fas fa-edit me-1"></i>Manual Entry
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="admissionNo" class="form-label">Admission Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-control" id="admissionYear" name="admissionYear" required onchange="updateAdmissionNumber()">
                                                        @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                    <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="CSSK/STD/YYYY/001" required>
                                                </div>
                                            </div>

                                            <!-- Rest of the Academic Details section remains unchanged -->
                                            <div class="mb-3">
                                                <label for="admissionDate" class="form-label">Admission Date <span class="text-danger">*</span></label>
                                                <input type="date" id="admissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="schoolclassid" class="form-label">Class <span class="text-danger">*</span></label>
                                                <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach ($schoolclasses as $class)
                                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="termid" class="form-label">Term <span class="text-danger">*</span></label>
                                                        <select id="termid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach ($schoolterms as $term)
                                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="sessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                                        <select id="sessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach ($schoolsessions as $session)
                                                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="statusOld" value="1" required>
                                                        <label class="form-check-label" for="statusOld">
                                                            <i class="fas fa-user-clock me-1"></i>Old Student
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="statusNew" value="2" required>
                                                        <label class="form-check-label" for="statusNew">
                                                            <i class="fas fa-user-plus me-1"></i>New Student
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Student Activity Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="statusActive" value="Active" required>
                                                        <label class="form-check-label" for="statusActive">
                                                            <i class="fas fa-check-circle text-success me-1"></i>Active
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="statusInactive" value="Inactive" required>
                                                        <label class="form-check-label" for="statusInactive">
                                                            <i class="fas fa-pause-circle text-warning me-1"></i>Inactive
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="student_category" class="form-label">Student Category <span class="text-danger">*</span></label>
                                                <select id="student_category" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Details and other sections remain unchanged -->
                                <div class="col-md-6">
                                    <!-- Section B: Student's Personal Details -->
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 text-center">
                                                <div class="upload-area border border-2 border-dashed border-primary rounded p-3">
                                                    <img id="addStudentAvatar" src="https://via.placeholder.com/120x120/667eea/ffffff?text=Photo" alt="Avatar Preview" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #667eea; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />
                                                    <div>
                                                        <label for="avatar" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-camera me-1"></i>Choose Photo
                                                        </label>
                                                        <input type="file" id="avatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this)">
                                                        <div class="form-text mt-2">Max 2MB (PNG, JPG, JPEG)</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">

                                                    <div class="mb-3">
                                                        <label for="title" class="form-label">Title</label>
                                                        <select id="title" name="title" class="form-control">
                                                            <option value="">Select</option>
                                                            <option value="Master">Master</option>
                                                            <option value="Miss">Miss</option>
                                                        </select>
                                                    </div>



                                                    <div class="mb-3">
                                                        <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                                        <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last name" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First name" required>
                                                    </div>
                                                </div>



                                            <div class="mb-3">
                                                <label for="othername" class="form-label">Other Names</label>
                                                <input type="text" id="othername" name="othername" class="form-control" placeholder="Middle name(s)">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required>
                                                        <label class="form-check-label" for="genderMale">
                                                            <i class="fas fa-male text-primary me-1"></i>Male
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required>
                                                        <label class="form-check-label" for="genderFemale">
                                                            <i class="fas fa-female text-danger me-1"></i>Female
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="dateofbirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value)">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Age <span class="text-danger">*</span></label>
                                                        <input type="number" id="addAgeInput" name="age" class="form-control" readonly required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="phone_number" class="form-label">Phone Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-phone"></i>
                                                    </span>
                                                    <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="+234 xxx xxx xxxx">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="placeofbirth" class="form-label">Place of Birth</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="input" id="placeofbirth" name="placeofbirth" class="form-control" placeholder="Place of birth">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com">
                                                </div>
                                            </div>

                                           <div class="mb-3">
                                                <label for="future_ambition" class="form-label">Future Ambition <span class="text-danger">*</span></label>
                                                <textarea id="future_ambition" name="future_ambition" class="form-control" rows="2" placeholder="Enter future ambition" required></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label for="permanent_address" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                                <textarea id="permanent_address" name="permanent_address" class="form-control" rows="2" placeholder="Enter permanent address" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information, Parent/Guardian Details, and Previous School Details remain unchanged -->
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Section C: Additional Details -->
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <div class="mb-3">
                                                        <label for="nationality" class="form-label">Nationality</label>
                                                        <input type="text" id="nationality" name="nationality" class="form-control" placeholder="Nationality" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="addState" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                                        <select id="addState" name="state" class="form-control" required>
                                                            <option value="">Select State</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="addLocal" class="form-label">Local Government <span class="text-danger">*</span></label>
                                                        <select id="addLocal" name="local" class="form-control" required>
                                                            <option value="">Select LGA</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="city" class="form-label">City</label>
                                                        <input type="text" id="city" name="city" class="form-control" placeholder="Enter city">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="religion" class="form-label">Religion <span class="text-danger">*</span></label>
                                                        <select id="religion" name="religion" class="form-control" required>
                                                            <option value="">Select Religion</option>
                                                            <option value="Christianity">Christianity</option>
                                                            <option value="Islam">Islam</option>
                                                            <option value="Others">Others</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="blood_group" class="form-label">Blood Group</label>
                                                        <select id="blood_group" name="blood_group" class="form-control">
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
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="mother_tongue" class="form-label">Mother Tongue</label>
                                                        <input type="text" id="mother_tongue" name="mother_tongue" class="form-control" placeholder="Native language">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="nin_number" class="form-label">NIN Number</label>
                                                        <input type="text" id="nin_number" name="nin_number" class="form-control" placeholder="11-digit NIN" maxlength="11">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="sport_house" class="form-label">School House</label>
                                                        <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                                            <option value="">Select School House</option>
                                                            @foreach ($schoolhouses as $schoolhouse)
                                                                <option value="{{ $schoolhouse->id }}">{{ $schoolhouse->house }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- Section D: Parent/Guardian Details -->
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="father_name" class="form-label">Father's Name</label>
                                                <input type="text" id="father_name" name="father_name" class="form-control" placeholder="Father's full name">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="father_phone" class="form-label">Father's Phone</label>
                                                        <input type="text" id="father_phone" name="father_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="father_occupation" class="form-label">Father's Occupation</label>
                                                        <input type="text" id="father_occupation" name="father_occupation" class="form-control" placeholder="Occupation">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="father_city" class="form-label">Father's City</label>
                                                <input type="text" id="father_city" name="father_city" class="form-control" placeholder="City of residence">
                                            </div>

                                            <div class="mb-3">
                                                <label for="mother_name" class="form-label">Mother's Name</label>
                                                <input type="text" id="mother_name" name="mother_name" class="form-control" placeholder="Mother's full name">
                                            </div>

                                            <div class="mb-3">
                                                <label for="mother_phone" class="form-label">Mother's Phone</label>
                                                <input type="text" id="mother_phone" name="mother_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>

                                            <div class="mb-3">
                                                <label for="parent_email" class="form-label">Parent's Email</label>
                                                <input type="email" id="parent_email" name="parent_email" class="form-control" placeholder="parent@example.com">
                                            </div>

                                            <div class="mb-3">
                                                <label for="parent_address" class="form-label">Parent's Address</label>
                                                <textarea id="parent_address" name="parent_address" class="form-control" rows="2" placeholder="Parent's address"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section E: Previous School Details -->
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="last_school" class="form-label">Last School Attended</label>
                                                <input type="text" id="last_school" name="last_school" class="form-control" placeholder="Previous school name">
                                            </div>

                                            <div class="mb-3">
                                                <label for="last_class" class="form-label">Last Class Attended</label>
                                                <input type="text" id="last_class" name="last_class" class="form-control" placeholder="e.g., JSS 2">
                                            </div>

                                            <div class="mb-3">
                                                <label for="reason_for_leaving" class="form-label">Reason for Leaving</label>
                                                <textarea id="reason_for_leaving" name="reason_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving previous school"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="add-btn">
                                <i class="fas fa-save me-1"></i>Register Student
                            </button>
                            <button type="button" class="btn btn-success" onclick="printStudentDetails()">
                                <i class="fas fa-print me-1"></i>Print PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal (Keep your existing modal) -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user-edit me-2"></i>Edit Student
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form class="tablelist-form" id="editStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.update', ':id') }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body p-4">
                            <input type="hidden" id="editStudentId" name="id">

                            <!-- Progress Steps -->
                            <div class="progress-steps mb-4">
                                <div class="step active">1</div>
                                <div class="step">2</div>
                                <div class="step">3</div>
                                <div class="step">4</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                   <!-- Updated Academic Details section in Edit Student Modal -->
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionAuto" value="auto" required onchange="toggleAdmissionInput('edit')">
                                                        <label class="form-check-label" for="editAdmissionAuto">
                                                            <i class="fas fa-magic me-1"></i>Auto Generate
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionManual" value="manual" required onchange="toggleAdmissionInput('edit')">
                                                        <label class="form-check-label" for="editAdmissionManual">
                                                            <i class="fas fa-edit me-1"></i>Manual Entry
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="editAdmissionNo" class="form-label">Admission Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-control" id="editAdmissionYear" name="admissionYear" required onchange="updateAdmissionNumber('edit')">
                                                        @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                    <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="CSSK/STD/YYYY/001" required>
                                                </div>
                                            </div>

                                            <!-- MISSING FIELD: Admission Date -->
                                            <div class="mb-3">
                                                <label for="editAdmissionDate" class="form-label">Admission Date <span class="text-danger">*</span></label>
                                                <input type="date" id="editAdmissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editSchoolclassid" class="form-label">Class <span class="text-danger">*</span></label>
                                                <select id="editSchoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach ($schoolclasses as $class)
                                                        <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editTermid" class="form-label">Term <span class="text-danger">*</span></label>
                                                        <select id="editTermid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach ($schoolterms as $term)
                                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editSessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                                        <select id="editSessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach ($schoolsessions as $session)
                                                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Student Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="editStatusOld" value="1" required>
                                                        <label class="form-check-label" for="editStatusOld">
                                                            <i class="fas fa-user-clock me-1"></i>Old Student
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="statusId" id="editStatusNew" value="2" required>
                                                        <label class="form-check-label" for="editStatusNew">
                                                            <i class="fas fa-user-plus me-1"></i>New Student
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Student Activity Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="editStatusActive" value="Active" required>
                                                        <label class="form-check-label" for="editStatusActive">
                                                            <i class="fas fa-check-circle text-success me-1"></i>Active
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="student_status" id="editStatusInactive" value="Inactive" required>
                                                        <label class="form-check-label" for="editStatusInactive">
                                                            <i class="fas fa-pause-circle text-warning me-1"></i>Inactive
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="editStudentCategory" class="form-label">Student Category <span class="text-danger">*</span></label>
                                                <select id="editStudentCategory" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Personal Details and other sections remain unchanged -->
                                <div class="col-md-6">
                                 <!-- Updated Personal Details section with Future Ambition -->
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <div class="upload-area border border-2 border-dashed border-primary rounded p-3">
                                                <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #667eea; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />
                                                <div>
                                                    <label for="editAvatar" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-camera me-1"></i>Choose Photo
                                                    </label>
                                                    <input type="file" id="editAvatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this, 'editStudentAvatar')">
                                                    <div class="form-text mt-2">Max 2MB (PNG, JPG, JPEG)</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="editTitle" class="form-label">Title</label>
                                                    <select id="editTitle" name="title" class="form-control">
                                                        <option value="">Select</option>
                                                        <option value="Master">Master</option>
                                                        <option value="Miss">Miss</option>
                                                    </select>
                                                </div>
                                            </div>
                                             <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="editLastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="editLastname" name="lastname" class="form-control" placeholder="Last name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="editFirstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="editFirstname" name="firstname" class="form-control" placeholder="First name" required>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="mb-3">
                                            <label for="editOthername" class="form-label">Other Names</label>
                                            <input type="text" id="editOthername" name="othername" class="form-control" placeholder="Middle name(s)">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="Male" required>
                                                    <label class="form-check-label" for="editGenderMale">
                                                        <i class="fas fa-male text-primary me-1"></i>Male
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="Female" required>
                                                    <label class="form-check-label" for="editGenderFemale">
                                                        <i class="fas fa-female text-danger me-1"></i>Female
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editDOB" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                    <input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value, 'editAgeInput')">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Age <span class="text-danger">*</span></label>
                                                    <input type="number" id="editAgeInput" name="age" class="form-control" readonly required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPhoneNumber" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                                <input type="text" id="editPhoneNumber" name="phone_number" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPlaceofbirth" class="form-label">Place of Birth</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" placeholder="Place of birth">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editEmail" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <input type="email" id="editEmail" name="email" class="form-control" placeholder="student@example.com">
                                            </div>
                                        </div>

                                        <!-- MISSING FIELD: Future Ambition -->
                                        <div class="mb-3">
                                            <label for="editFutureAmbition" class="form-label">Future Ambition <span class="text-danger">*</span></label>
                                            <textarea id="editFutureAmbition" name="future_ambition" class="form-control" rows="2" placeholder="Enter future ambition" required></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPermanentAddress" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                            <textarea id="editPermanentAddress" name="permanent_address" class="form-control" rows="2" placeholder="Enter permanent address" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <div class="col-md-6">
                            <!-- Updated Additional Information section with School House -->
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="mb-3">
                                                    <label for="editNationality" class="form-label">Nationality</label>
                                                    <input type="text" id="editNationality" name="nationality" class="form-control" placeholder="Nationality" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editState" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                                    <select id="editState" name="state" class="form-control" required>
                                                        <option value="">Select State</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editLocal" class="form-label">Local Government of Origin<span class="text-danger">*</span></label>
                                                    <select id="editLocal" name="local" class="form-control" required>
                                                        <option value="">Select LGA</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editCity" class="form-label">City</label>
                                                    <input type="text" id="editCity" name="city" class="form-control" placeholder="Enter city">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editReligion" class="form-label">Religion <span class="text-danger">*</span></label>
                                                    <select id="editReligion" name="religion" class="form-control" required>
                                                        <option value="">Select Religion</option>
                                                        <option value="Christianity">Christianity</option>
                                                        <option value="Islam">Islam</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editBloodGroup" class="form-label">Blood Group</label>
                                                    <select id="editBloodGroup" name="blood_group" class="form-control">
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
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editMotherTongue" class="form-label">Mother Tongue</label>
                                                    <input type="text" id="editMotherTongue" name="mother_tongue" class="form-control" placeholder="Native language">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editNinNumber" class="form-label">NIN Number</label>
                                                    <input type="text" id="editNinNumber" name="nin_number" class="form-control" placeholder="11-digit NIN" maxlength="11">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <!-- MISSING FIELD: School House -->
                                                <div class="mb-3">
                                                    <label for="editSchoolHouse" class="form-label">School House</label>
                                                    <select id="editSchoolHouse" name="school_house" class="form-control">
                                                        <option value="">Select School House</option>
                                                        @foreach ($schoolhouses as $schoolhouse)
                                                            <option value="{{ $schoolhouse->id }}">{{ $schoolhouse->house }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                    <!-- Section D: Parent/Guardian Details -->
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent/Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="editFatherName" class="form-label">Father's Name</label>
                                                <input type="text" id="editFatherName" name="father_name" class="form-control" placeholder="Father's full name">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editFatherPhone" class="form-label">Father's Phone</label>
                                                        <input type="text" id="editFatherPhone" name="father_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="editFatherOccupation" class="form-label">Father's Occupation</label>
                                                        <input type="text" id="editFatherOccupation" name="father_occupation" class="form-control" placeholder="Occupation">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="editFatherCity" class="form-label">Father's City</label>
                                                <input type="text" id="editFatherCity" name="father_city" class="form-control" placeholder="City of residence">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editMotherName" class="form-label">Mother's Name</label>
                                                <input type="text" id="editMotherName" name="mother_name" class="form-control" placeholder="Mother's full name">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editMotherPhone" class="form-label">Mother's Phone</label>
                                                <input type="text" id="editMotherPhone" name="mother_phone" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editParentEmail" class="form-label">Parent's Email</label>
                                                <input type="email" id="editParentEmail" name="parent_email" class="form-control" placeholder="parent@example.com">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editParentAddress" class="form-label">Parent's Address</label>
                                                <textarea id="editParentAddress" name="parent_address" class="form-control" rows="2" placeholder="Parent's address"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section E: Previous School Details -->
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="editLastSchool" class="form-label">Last School Attended</label>
                                                <input type="text" id="editLastSchool" name="last_school" class="form-control" placeholder="Previous school name">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editLastClass" class="form-label">Last Class Attended</label>
                                                <input type="text" id="editLastClass" name="last_class" class="form-control" placeholder="e.g., JSS 2">
                                            </div>

                                            <div class="mb-3">
                                                <label for="editReasonForLeaving" class="form-label">Reason for Leaving</label>
                                                <textarea id="editReasonForLeaving" name="reason_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving previous school"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="edit-btn">
                                <i class="fas fa-save me-1"></i>Update Student
                            </button>
                            <button type="button" class="btn btn-success" onclick="printStudentDetails('edit')">
                                <i class="fas fa-print me-1"></i>Print PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Student Modal (Keep your existing modal) -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- Keep your existing view modal HTML here -->
        </div>
    </div>
</div>

<script>
// Initialize admission number on page load
updateAdmissionNumber();
updateAdmissionNumber('edit');

// Update admission number based on year selection
function updateAdmissionNumber(prefix = '') {
    const yearSelect = document.getElementById(`${prefix}admissionYear`);
    const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
    const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`);

    if (!yearSelect || !admissionNoInput) return;

    const year = yearSelect.value;
    const baseFormat = `CSSK/STD/${year}/`;

    if (admissionMode && admissionMode.value === 'auto') {
        admissionNoInput.readOnly = true;
        fetch(`/students/last-admission-number?year=${year}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                admissionNoInput.value = data.admissionNo;
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to generate admission number',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to generate admission number',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            admissionNoInput.value = `${baseFormat}0871`;
        });
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
            admissionNoInput.value = `${baseFormat}0871`;
        } else if (!admissionNoInput.value.startsWith(baseFormat)) {
            const numericPart = admissionNoInput.value.split('/').pop() || '0871';
            const numericValue = Math.max(871, parseInt(numericPart) || 871);
            admissionNoInput.value = `${baseFormat}${numericValue.toString().padStart(4, '0')}`;
        }
    }
}

// Toggle admission input based on mode
window.toggleAdmissionInput = function(prefix = '') {
    const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`);
    const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
    const yearSelect = document.getElementById(`${prefix}admissionYear`);

    if (!admissionMode || !admissionNoInput || !yearSelect) return;

    const year = yearSelect.value;
    const baseFormat = `CSSK/STD/${year}/`;

    if (admissionMode.value === 'auto') {
        admissionNoInput.readOnly = true;
        fetch(`/students/last-admission-number?year=${year}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                admissionNoInput.value = data.admissionNo;
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to generate admission number',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to generate admission number',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            admissionNoInput.value = `${baseFormat}0871`;
        });
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
            admissionNoInput.value = `${baseFormat}0871`;
        } else if (!admissionNoInput.value.startsWith(baseFormat)) {
            const numericPart = admissionNoInput.value.split('/').pop() || '0871';
            const numericValue = Math.max(871, parseInt(numericPart) || 871);
            admissionNoInput.value = `${baseFormat}${numericValue.toString().padStart(4, '0')}`;
        }
    }
};

// Add event listeners for year selection
document.getElementById('admissionYear')?.addEventListener('change', () => updateAdmissionNumber());
document.getElementById('editAdmissionYear')?.addEventListener('change', () => updateAdmissionNumber('edit'));

// Ensure Axios and CSRF token
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error('Error: Axios is not defined');
        Swal.fire({
            title: "Error!",
            text: "Axios library is missing",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return false;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('Error: CSRF token not found');
        Swal.fire({
            title: "Error!",
            text: "CSRF token is missing",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return false;
    }
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    return true;
}

// Ensure Choices.js
function ensureChoices() {
    if (typeof Choices === 'undefined') {
        console.warn('Choices.js is not defined, using basic select');
        return false;
    }
    return true;
}

let studentList;
let allStudents = [];
const itemsPerPage = 100;
const defaultAvatar = '{{ asset("storage/images/student_avatars/unnamed.jpg") }}';

// View toggle function
function toggleView(viewType) {
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');

    if (viewType === 'table') {
        tableView.classList.remove('d-none');
        cardView.classList.add('d-none');
        tableViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');

        // Update checkboxes
        document.getElementById('checkAll').checked = false;
        document.getElementById('remove-actions').classList.add('d-none');
    } else {
        tableView.classList.add('d-none');
        cardView.classList.remove('d-none');
        tableViewBtn.classList.remove('active');
        cardViewBtn.classList.add('active');

        // Render cards if not already rendered
        if (document.getElementById('studentsCardsContainer').children.length === 0 && allStudents.length > 0) {
            renderStudentsCards(allStudents);
        }

        // Update checkboxes
        document.getElementById('checkAll').checked = false;
        document.getElementById('remove-actions').classList.add('d-none');
    }
}

// Render students as cards
function renderStudentsCards(students) {
    console.log('Rendering students as cards:', students);
    const container = document.getElementById('studentsCardsContainer');
    if (!container) {
        console.error('studentsCardsContainer element not found');
        Swal.fire({
            title: "Error!",
            text: "Students container element not found",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    container.innerHTML = '';

    if (students.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h5>No students found</h5>
                    <p>Try adjusting your filters or add a new student</p>
                </div>
            </div>
        `;
        updateCounts(0);
        return;
    }

    students.forEach(student => {
        console.log('Rendering student card:', student);

        // Get initials for avatar
        const firstName = student.firstname || '';
        const lastName = student.lastname || '';
        const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || '??';

        // Get avatar URL
        let avatarUrl = defaultAvatar;
        if (student.picture) {
            avatarUrl = `/storage/images/student_avatars/${student.picture}`;
        }

        // Determine status
        const isActive = student.student_status === 'Active';
        const statusText = isActive ? 'Active' : 'Inactive';
        const statusClass = isActive ? 'status-active' : 'status-inactive';

        // Get student type
        const studentType = student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : 'N/A';

        // Format registration date
        const regDate = student.created_at ? new Date(student.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : 'N/A';

        const cardHtml = `
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="student-card" data-id="${student.id}"
                     data-name="${student.lastname} ${student.firstname} ${student.othername}"
                     data-admission="${student.admissionNo}"
                     data-class="${student.schoolclassid}"
                     data-status="${student.statusId}"
                     data-gender="${student.gender}"
                     data-student-status="${student.student_status}">

                    <!-- Checkbox for bulk selection -->
                    <div class="checkbox-container">
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox" name="chk_child" value="${student.id}">
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <span class="status-badge ${statusClass}">${statusText}</span>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="action-btn view-btn" title="View Details" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit-btn" title="Edit" onclick="editStudent(${student.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-btn" title="Delete" onclick="deleteStudent(${student.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <!-- Avatar with initials fallback -->
                    <div class="avatar-container">
                        ${student.picture ?
                            `<img src="${avatarUrl}" alt="${student.firstname} ${student.lastname}" class="avatar"
                                 onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\"avatar-initials\">${initials}</div>';">` :
                            `<div class="avatar-initials">${initials}</div>`
                        }
                    </div>

                    <!-- Student Information -->
                    <h6 class="student-name">${student.lastname} ${student.firstname}</h6>
                    <p class="student-admission">${student.admissionNo || 'No Admission No'}</p>

                    <div class="student-details">
                        <div><strong>Class:</strong> ${student.schoolclass || 'N/A'} ${student.arm || ''}</div>
                        <div><strong>Type:</strong> ${studentType}</div>
                        <div><strong>Gender:</strong> ${student.gender || 'N/A'}</div>
                        <div><strong>Registered:</strong> ${regDate}</div>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += cardHtml;
    });

    initializeStudentCheckboxes();
    updateCounts(students.length);
}

// Update counts display
function updateCounts(count) {
    document.getElementById('totalStudents').textContent = count;
    document.getElementById('totalCount').textContent = count;
    document.getElementById('showingCount').textContent = count;
}

// Initialize student checkboxes for card view
function initializeStudentCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const card = checkbox.closest('.student-card');
                if (card) {
                    card.classList.toggle('selected', this.checked);
                }
            });
            document.getElementById('remove-actions').classList.toggle('d-none', !this.checked);
        });
    }

    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.student-card');
            if (card) {
                card.classList.toggle('selected', this.checked);
            }

            // Update checkAll state
            const allChecked = document.querySelectorAll('.student-checkbox').length ===
                             document.querySelectorAll('.student-checkbox:checked').length;
            const someChecked = document.querySelectorAll('.student-checkbox:checked').length > 0;

            if (checkAll) {
                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
            }

            document.getElementById('remove-actions').classList.toggle('d-none', !someChecked);
        });
    });
}

// Card view specific functions
function viewStudent(id) {
    console.log('View student from card view:', id);
    if (!ensureAxios()) return;

    axios.get(`/student/${id}/view`).then((response) => {
        console.log('Student data received for view:', response.data);
        const student = response.data.student || response.data;
        if (!student) {
            throw new Error('Student data is empty');
        }
        populateViewModal(student);
        // Show the view modal
        const viewModal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
        viewModal.show();
    }).catch((error) => {
        console.error('Error viewing student:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to load student data',
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
    });
}

function editStudent(id) {
    console.log('Edit student from card view:', id);
    if (!ensureAxios()) return;

    axios.get(`/student/${id}/edit`).then((response) => {
        console.log('Student data received for edit:', response.data);
        const student = response.data.student;
        if (!student) {
            throw new Error('Student data is empty');
        }

        // Populate the edit form
        populateEditForm(student);

        // Show the edit modal
        const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        editModal.show();
    }).catch((error) => {
        console.error('Error editing student:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to load student data',
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
    });
}

function deleteStudent(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            axios.delete(`/student/${id}/destroy`).then(() => {
                // Remove the card
                const card = document.querySelector(`.student-card[data-id="${id}"]`);
                if (card) {
                    card.closest('.col-xl-3').remove();
                }
                // Refresh the list
                fetchStudents();
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Student has been deleted',
                    icon: 'success',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }).catch((error) => {
                console.error('Error deleting student:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'Failed to delete student',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            });
        }
    });
}

// Fetch students from the server
function fetchStudents() {
    if (!ensureAxios()) return;
    console.log('Fetching students from /students/data');
    axios.get('/students/data')
        .then((response) => {
            console.log('Students data received:', response.data);
            if (!response.data.success || !Array.isArray(response.data.students)) {
                throw new Error(response.data.message || 'Invalid response format');
            }
            allStudents = response.data.students.map(student => ({
                id: student.id || '',
                admissionNo: student.admissionNo || '',
                firstname: student.firstname || '',
                lastname: student.lastname || '',
                othername: student.othername || '',
                gender: student.gender || '',
                statusId: student.statusId || '',
                student_status: student.student_status || '',
                created_at: student.created_at || '',
                picture: student.picture || '',
                schoolclass: student.schoolclass || '',
                arm: student.arm || '',
                schoolclassid: student.schoolclassid || ''
            }));
            console.log('Processed students:', allStudents.length);

            // Update counts
            document.querySelector('#totalStudents').textContent = allStudents.length;
            document.querySelector('#totalCount').textContent = allStudents.length;
            document.getElementById('showingCount').textContent = allStudents.length;

            // Check which view is active and render accordingly
            const tableView = document.getElementById('tableView');
            const isTableView = !tableView.classList.contains('d-none');

            if (isTableView) {
                renderStudents(allStudents);
            } else {
                renderStudentsCards(allStudents);
            }
        })
        .catch((error) => {
            console.error('Error fetching students:', error);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || error.message || "Failed to load students.",
                icon: "error",
                customClass: { confirmButton: "btn btn-primary" },
                buttonsStyling: false
            });
            renderStudents([]);
            renderStudentsCards([]);
        });
}

// Render students in the table
function renderStudents(students) {
    console.log('Rendering students:', students);
    const tbody = document.getElementById('studentTableBody');
    if (!tbody) {
        console.error('studentTableBody element not found');
        Swal.fire({
            title: "Error!",
            text: "Table body element not found",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }
    tbody.innerHTML = '';
    if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="8" class="text-center">No students found</td>`;
        tbody.appendChild(row);
        initializeList();
        return;
    }
    students.forEach(student => {
        console.log('Rendering student:', student);
        const studentImage = student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar;

        const actionButtons = [
            `<li><a href="javascript:void(0);" class="btn btn-subtle-info btn-icon btn-sm view-item-btn" data-id="${student.id}" data-bs-toggle="modal" data-bs-target="#viewStudentModal" title="View Details"><i class="ph-eye"></i></a></li>`,
            `<li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="${student.id}" data-bs-toggle="modal" data-bs-target="#editStudentModal" title="Edit"><i class="ph-pencil"></i></a></li>`,
            `<li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="${student.id}" title="Delete"><i class="ph-trash"></i></a></li>`
        ];
        console.log('Action buttons for student:', actionButtons);
        const row = document.createElement('tr');
        row.setAttribute('data-id', student.id);
        row.innerHTML = `
            <td class="id" data-id="${student.id}">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="chk_child">
                </div>
            </td>
            <td class="name" data-name="${student.lastname} ${student.firstname} ${student.othername}">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-3">
                        <img src="${studentImage}" alt="" class="rounded-circle avatar-sm student-image" style="object-fit:cover;" data-bs-toggle="modal" data-bs-target="#imageViewModal" data-image="${studentImage}"/>
                    </div>
                    <div>
                        <h6 class="mb-0">
                            <a href="/student/${student.id}" class="text-reset products">
                                <b>${student.lastname}</b> ${student.firstname} ${student.othername}
                            </a>
                        </h6>
                    </div>
                </div>
            </td>
            <td class="admissionNo" data-admissionNo="${student.admissionNo}">${student.admissionNo}</td>
            <td class="class" data-class="${student.schoolclassid}">${student.schoolclass} - ${student.arm}</td>
            <td class="status" data-status="${student.statusId}">${student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : ''}</td>
            <td class="gender" data-gender="${student.gender}">${student.gender}</td>
            <td class="datereg">${student.created_at ? new Date(student.created_at).toISOString().split('T')[0] : ''}</td>
            <td>
                <ul class="d-flex gap-2 list-unstyled mb-0">
                    ${actionButtons.join('')}
                </ul>
            </td>
        `;
        tbody.appendChild(row);
    });
    console.log('Table rows after rendering:', tbody.innerHTML);
    initializeList();
    initializeCheckboxes();
}

// Initialize List.js for pagination and sorting
function initializeList() {
    if (typeof List === 'undefined') {
        console.error('List.js is not loaded');
        Swal.fire({
            title: "Error!",
            text: "List.js library is missing",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }
    const options = {
        valueNames: ['name', 'admissionNo', 'class', 'status', 'gender', 'datereg'],
        page: itemsPerPage,
        pagination: true,
        item: `
            <tr>
                <td class="id">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="chk_child">
                    </div>
                </td>
                <td class="name"></td>
                <td class="admissionNo"></td>
                <td class="class"></td>
                <td class="status"></td>
                <td class="gender"></td>
                <td class="datereg"></td>
                <td><ul class="d-flex gap-2 list-unstyled mb-0"></ul></td>
            </tr>
        `
    };
    try {
        studentList = new List('studentList', options);
        studentList.on('updated', function () {
            updatePagination();
            document.getElementById('showingCount').textContent = studentList.visibleItems.length;
            document.getElementById('totalCount').textContent = studentList.items.length;
            document.getElementById('totalStudents').textContent = studentList.items.length;
        });
    } catch (error) {
        console.error('List.js initialization error:', error.message);
        Swal.fire({
            title: "Error!",
            text: "Failed to initialize table. Check console for details.",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
    }
}

// Update pagination controls
function updatePagination() {
    if (!studentList) return;
    const totalItems = studentList.items.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const currentPage = studentList.page ? Math.ceil(studentList.i / itemsPerPage) : 1;
    const paginationLinks = document.getElementById('paginationLinks');
    paginationLinks.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
        li.addEventListener('click', () => {
            studentList.show((i - 1) * itemsPerPage + 1, itemsPerPage);
        });
        paginationLinks.appendChild(li);
    }

    document.getElementById('prevPage').classList.toggle('disabled', currentPage === 1);
    document.getElementById('nextPage').classList.toggle('disabled', currentPage === totalPages);
    document.getElementById('prevPage').onclick = currentPage > 1 ? () => studentList.show((currentPage - 2) * itemsPerPage + 1, itemsPerPage) : null;
    document.getElementById('nextPage').onclick = currentPage < totalPages ? () => studentList.show(currentPage * itemsPerPage + 1, itemsPerPage) : null;
}

// Filter function for both views
function filterData() {
    const search = document.querySelector('#search-input')?.value.toLowerCase() || '';
    const classId = document.getElementById('schoolclass-filter')?.value || 'all';
    const statusId = document.getElementById('status-filter')?.value || 'all';
    const gender = document.getElementById('gender-filter')?.value || 'all';

    console.log('Filtering with:', { search, classId, statusId, gender });

    // Check which view is active
    const tableView = document.getElementById('tableView');
    const isTableView = !tableView.classList.contains('d-none');

    if (isTableView) {
        // Table view filtering (using List.js)
        if (studentList) {
            studentList.filter(item => {
                const name = item.values().name?.toLowerCase() || '';
                const admissionNo = item.values().admissionNo?.toLowerCase() || '';
                const classValue = item.elm.querySelector('.class')?.dataset.class || '';
                const statusValue = item.elm.querySelector('.status')?.dataset.status || '';
                const genderValue = item.elm.querySelector('.gender')?.dataset.gender || '';

                const matchesSearch = name.includes(search) || admissionNo.includes(search);
                const matchesClass = classId === 'all' || classValue === classId;
                const matchesStatus = statusId === 'all' || statusValue === statusId;
                const matchesGender = gender === 'all' || genderValue === gender;

                return matchesSearch && matchesClass && matchesStatus && matchesGender;
            });
        }
    } else {
        // Card view filtering
        const cards = document.querySelectorAll('.student-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name')?.toLowerCase() || '';
            const admission = card.getAttribute('data-admission')?.toLowerCase() || '';
            const classValue = card.getAttribute('data-class') || '';
            const statusValue = card.getAttribute('data-status') || '';
            const genderValue = card.getAttribute('data-gender') || '';

            const matchesSearch = name.includes(search) || admission.includes(search);
            const matchesClass = classId === 'all' || classValue === classId;
            const matchesStatus = statusId === 'all' || statusValue === statusId;
            const matchesGender = gender === 'all' || genderValue === gender;

            const isVisible = matchesSearch && matchesClass && matchesStatus && matchesGender;
            const column = card.closest('.col-xl-3');

            if (column) {
                column.style.display = isVisible ? 'block' : 'none';
            }

            if (isVisible) {
                visibleCount++;
            }
        });

        document.getElementById('showingCount').textContent = visibleCount;
    }
}

// Delete multiple students (works for both views)
function deleteMultiple() {
    // Check which view is active
    const tableView = document.getElementById('tableView');
    const isTableView = !tableView.classList.contains('d-none');

    let ids = [];

    if (isTableView) {
        // Table view selection
        ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
            .map(checkbox => checkbox.closest('tr').querySelector('.id').dataset.id);
    } else {
        // Card view selection
        ids = Array.from(document.querySelectorAll('.student-checkbox:checked'))
            .map(checkbox => checkbox.value);
    }

    if (ids.length === 0) {
        Swal.fire({
            title: "Error!",
            text: "Please select at least one student",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: `You are about to delete ${ids.length} student(s). This action cannot be undone!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete them!",
        cancelButtonText: "Cancel",
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-secondary"
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            axios.post('/students/destroy-multiple', { ids })
                .then((response) => {
                    if (response.data.success) {
                        // Remove the items based on view type
                        ids.forEach(id => {
                            if (isTableView) {
                                const row = document.querySelector(`tr[data-id="${id}"]`);
                                if (row) row.remove();
                            } else {
                                const card = document.querySelector(`.student-card[data-id="${id}"]`);
                                if (card) {
                                    card.closest('.col-xl-3').remove();
                                }
                            }
                        });

                        // Refresh counts
                        fetchStudents();

                        Swal.fire({
                            title: "Deleted!",
                            text: response.data.message || `Successfully deleted ${ids.length} student(s)`,
                            icon: "success",
                            customClass: { confirmButton: "btn btn-primary" },
                            buttonsStyling: false
                        });
                    } else {
                        throw new Error(response.data.message || 'Failed to delete students');
                    }
                })
                .catch((error) => {
                    console.error('Error deleting students:', error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete students",
                        icon: "error",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Initialize checkboxes for multiple selection (table view)
function initializeCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        document.getElementById('remove-actions').classList.toggle('d-none', !this.checked);
    });

    document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allChecked = document.querySelectorAll('input[name="chk_child"]').length ===
                document.querySelectorAll('input[name="chk_child"]:checked').length;
            checkAll.checked = allChecked;
            document.getElementById('remove-actions').classList.toggle('d-none',
                document.querySelectorAll('input[name="chk_child"]:checked').length === 0);
        });
    });
}

// Populate states and LGAs
function populateStates(stateSelectId, lgaSelectId) {
    const stateSelect = document.getElementById(stateSelectId);
    const lgaSelect = document.getElementById(lgaSelectId);
    if (!stateSelect || !lgaSelect) return;

    fetch('/states_lgas.json')
        .then(response => response.json())
        .then(data => {
            stateSelect.innerHTML = '<option value="">Select State</option>';
            data.forEach(state => {
                const option = document.createElement('option');
                option.value = state.state;
                option.textContent = state.state;
                stateSelect.appendChild(option);
            });

            if (ensureChoices()) {
                const choicesState = new Choices(stateSelect, { searchEnabled: true });
                const choicesLga = new Choices(lgaSelect, { searchEnabled: true });
            }

            stateSelect.addEventListener('change', function () {
                lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                const selectedState = data.find(state => state.state === this.value);
                if (selectedState) {
                    selectedState.lgas.forEach(lga => {
                        const option = document.createElement('option');
                        option.value = lga;
                        option.textContent = lga;
                        lgaSelect.appendChild(option);
                    });
                    if (ensureChoices()) {
                        new Choices(lgaSelect, { searchEnabled: true });
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading states and LGAs:', error);
        });
}

// Populate LGAs based on selected state
function populateLGAs(state, lgaSelectId) {
    const lgaSelect = document.getElementById(lgaSelectId);
    if (!lgaSelect) return;

    fetch('/states_lgas.json')
        .then(response => response.json())
        .then(data => {
            lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
            const selectedState = data.find(s => s.state === state);
            if (selectedState) {
                selectedState.lgas.forEach(lga => {
                    const option = document.createElement('option');
                    option.value = lga;
                    option.textContent = lga;
                    lgaSelect.appendChild(option);
                });
                if (ensureChoices()) {
                    new Choices(lgaSelect, { searchEnabled: true });
                }
            }
        })
        .catch(error => {
            console.error('Error loading LGAs:', error);
        });
}

// Age calculation function
window.showage = function (date, displayId = 'addAge') {
    if (date) {
        const dateString = date.includes('T') ? date.split('T')[0] : date;
        const dob = new Date(dateString);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        const ageInputId = displayId === 'addAge' ? 'addAgeInput' : 'editAgeInput';
        const ageInput = document.getElementById(ageInputId);
        if (ageInput) {
            ageInput.value = age;
        } else {
            console.warn(`Age input element with ID '${ageInputId}' not found`);
        }
    } else {
        const ageInputId = displayId === 'addAge' ? 'addAgeInput' : 'editAgeInput';
        const ageInput = document.getElementById(ageInputId);
        if (ageInput) {
            ageInput.value = '';
        } else {
            console.warn(`Age input element with ID '${ageInputId}' not found`);
        }
    }
};

// Populate edit form with student data
function populateEditForm(student) {
    // Updated fields array with the missing fields
    const fields = [
        { id: 'editStudentId', value: student.id },
        { id: 'editAdmissionNo', value: student.admissionNo },
        { id: 'editAdmissionYear', value: student.admissionYear },
        { id: 'editAdmissionDate', value: student.admissionDate ? student.admissionDate.split('T')[0] : '' },
        { id: 'editTitle', value: student.title || '' },
        { id: 'editFirstname', value: student.firstname },
        { id: 'editLastname', value: student.lastname },
        { id: 'editOthername', value: student.othername || '' },
        { id: 'editPresentAddress', value: student.present_address || '' },
        { id: 'editPermanentAddress', value: student.permanent_address || '' },
        { id: 'editDOB', value: student.dateofbirth ? student.dateofbirth.split('T')[0] : '' },
        { id: 'editPlaceofbirth', value: student.placeofbirth || '' },
        { id: 'editNationality', value: student.nationality || '' },
        { id: 'editReligion', value: student.religion || '' },
        { id: 'editLastSchool', value: student.last_school || '' },
        { id: 'editLastClass', value: student.last_class || '' },
        { id: 'editSchoolclassid', value: student.schoolclassid || '' },
        { id: 'editTermid', value: student.termid || '' },
        { id: 'editSessionid', value: student.sessionid || '' },
        { id: 'editPhoneNumber', value: student.phone_number || '' },
        { id: 'editEmail', value: student.email || '' },
        { id: 'editFutureAmbition', value: student.future_ambition || '' },
        { id: 'editCity', value: student.city || '' },
        { id: 'editState', value: student.state || '' },
        { id: 'editLocal', value: student.local || '' },
        { id: 'editNinNumber', value: student.nin_number || '' },
        { id: 'editBloodGroup', value: student.blood_group || '' },
        { id: 'editMotherTongue', value: student.mother_tongue || '' },
        { id: 'editFatherName', value: student.father_name || '' },
        { id: 'editFatherPhone', value: student.father_phone || '' },
        { id: 'editFatherOccupation', value: student.father_occupation || '' },
        { id: 'editFatherCity', value: student.father_city || '' },
        { id: 'editMotherName', value: student.mother_name || '' },
        { id: 'editMotherPhone', value: student.mother_phone || '' },
        { id: 'editParentEmail', value: student.parent_email || '' },
        { id: 'editParentAddress', value: student.parent_address || '' },
        { id: 'editStudentCategory', value: student.student_category || '' },
        { id: 'editLastSchool', value: student.last_school || '' },
        { id: 'editLastClass', value: student.last_class || '' },
        { id: 'editReasonForLeaving', value: student.reason_for_leaving || '' },
        { id: 'editSchoolHouse', value: student.school_house || student.sport_house || '' }
    ];

    fields.forEach(({ id, value }) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
        } else {
            console.warn(`Element with ID '${id}' not found`);
        }
    });

    const genderRadios = document.querySelectorAll('input[name="gender"]');
    genderRadios.forEach(radio => {
        radio.checked = (radio.value === student.gender);
    });

    const statusRadios = document.querySelectorAll('input[name="statusId"]');
    statusRadios.forEach(radio => {
        radio.checked = (parseInt(radio.value) === parseInt(student.statusId));
    });

    const studentStatusRadios = document.querySelectorAll('input[name="student_status"]');
    studentStatusRadios.forEach(radio => {
        radio.checked = (radio.value === student.student_status);
    });

    const avatarElement = document.getElementById('editStudentAvatar');
    if (avatarElement) {
        avatarElement.src = student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar;
        avatarElement.setAttribute('data-original-src', student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar);
    } else {
        console.warn('Avatar element with ID "editStudentAvatar" not found');
    }

    // State and LGA handling
    const stateSelect = document.getElementById('editState');
    const lgaSelect = document.getElementById('editLocal');

    if (stateSelect && lgaSelect) {
        // First, load the states/LGA data
        fetch('/states_lgas.json')
            .then(response => response.json())
            .then(data => {
                // Clear and populate states
                stateSelect.innerHTML = '<option value="">Select State</option>';
                data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.state;
                    option.textContent = state.state;
                    stateSelect.appendChild(option);
                });

                // Set the student's state if available
                if (student.state) {
                    stateSelect.value = student.state;

                    // Find the selected state's LGAs
                    const selectedStateData = data.find(state => state.state === student.state);
                    if (selectedStateData) {
                        // Clear and populate LGAs for the selected state
                        lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                        selectedStateData.lgas.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga;
                            option.textContent = lga;
                            lgaSelect.appendChild(option);
                        });

                        // Set the student's LGA if available
                        if (student.local) {
                            lgaSelect.value = student.local;
                        }
                    }
                } else {
                    // If no state is selected, clear LGAs
                    lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                }

                // Add event listener for state changes
                stateSelect.addEventListener('change', function() {
                    lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                    const selectedState = data.find(state => state.state === this.value);
                    if (selectedState) {
                        selectedState.lgas.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga;
                            option.textContent = lga;
                            lgaSelect.appendChild(option);
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error loading states and LGAs for edit modal:', error);
                Swal.fire({
                    title: 'Warning!',
                    text: 'Could not load states and LGAs data',
                    icon: 'warning',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            });
    } else {
        console.warn('State or LGA select elements not found in edit modal');
    }

    if (student.dateofbirth) {
        showage(student.dateofbirth, 'editAge');
    }

    const form = document.getElementById('editStudentForm');
    if (form) {
        form.action = `/student/${student.id}`;
    }
}

// Populate view modal with student data
function populateViewModal(student) {
    // Student Photo
    const photoElement = document.getElementById('viewStudentPhoto');
    if (photoElement) {
        photoElement.src = student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar;
    }

    // Section A: Academic Details
    document.getElementById('viewAcademicYear').textContent = student.admissionYear || '';
    document.getElementById('viewRegistrationNo').textContent = student.admissionNo || '';
    document.getElementById('viewAdmissionDate').textContent = student.admissionDate ? new Date(student.admissionDate).toLocaleDateString() : '';
    document.getElementById('viewClass').textContent = (student.schoolclass && student.arm) ? `${student.schoolclass} - ${student.arm}` : '';
    document.getElementById('viewTerm').textContent = student.term_name || '';

    // Category checkboxes
    const categoryDay = document.getElementById('viewCategoryDay');
    const categoryBorder = document.getElementById('viewCategoryBorder');
    if (categoryDay && categoryBorder) {
        categoryDay.checked = student.student_category === 'Day';
        categoryBorder.checked = student.student_category === 'Boarding';
    }

    // Section B: Student Details
    document.getElementById('viewSurname').textContent = student.lastname || '';
    document.getElementById('viewFirstName').textContent = student.firstname || '';
    document.getElementById('viewMiddleName').textContent = student.othername || '';
    document.getElementById('viewGender').textContent = student.gender || '';
    document.getElementById('viewBloodGroup').textContent = student.blood_group || '';
    document.getElementById('viewDateOfBirth').textContent = student.dateofbirth ? new Date(student.dateofbirth).toLocaleDateString() : '';
    document.getElementById('viewMotherTongue').textContent = student.mother_tongue || '';
    document.getElementById('viewReligion').textContent = student.religion || '';
    document.getElementById('viewSportHouse').textContent = student.schoolhouse || student.sport_house || '';
    document.getElementById('viewMobileNumber').textContent = student.phone_number || '';
    document.getElementById('viewEmail').textContent = student.email || '';
    document.getElementById('viewNIN').textContent = student.nin_number || '';
    document.getElementById('viewCity').textContent = student.city || '';
    document.getElementById('viewState').textContent = student.state || '';
    document.getElementById('viewPermanentAddress').textContent = student.permanent_address || '';
    document.getElementById('viewFutureAmbition').textContent = student.future_ambition || '';

    // Section C: Guardian Details
    document.getElementById('viewFatherName').textContent = student.father_name || '';
    document.getElementById('viewMotherName').textContent = student.mother_name || '';
    document.getElementById('viewOccupation').textContent = student.father_occupation || '';
    document.getElementById('viewParentCity').textContent = student.father_city || '';
    document.getElementById('viewParentMobile').textContent = student.father_phone || student.mother_phone || '';
    document.getElementById('viewParentEmail').textContent = student.parent_email || '';
    document.getElementById('viewParentAddress').textContent = student.parent_address || '';

    // Section D: Previous School Details
    document.getElementById('viewSchoolName').textContent = student.last_school || '';
    document.getElementById('viewPreviousClass').textContent = student.last_class || '';
    document.getElementById('viewReasonLeaving').textContent = student.reason_for_leaving || '';
}

// Initialize the student list
function initializeStudentList() {
    // Populate states and LGAs
    populateStates('addState', 'addLocal');
    populateStates('editState', 'editLocal');
    fetchStudents();

    // Initialize view toggle
    document.getElementById('tableViewBtn')?.addEventListener('click', () => toggleView('table'));
    document.getElementById('cardViewBtn')?.addEventListener('click', () => toggleView('card'));

    // Filter event listeners
    document.querySelector('#search-input')?.addEventListener('input', filterData);
    document.getElementById('schoolclass-filter')?.addEventListener('change', filterData);
    document.getElementById('status-filter')?.addEventListener('change', filterData);
    document.getElementById('gender-filter')?.addEventListener('change', filterData);

    // Avatar upload for Add Student modal
    document.getElementById('avatar')?.addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('addStudentAvatar');
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: "Error!",
                    text: "File size exceeds 2MB limit.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-info" },
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.src = defaultAvatar;
                return;
            }
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Error!",
                    text: "Only PNG, JPG, and JPEG files are allowed.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-info" },
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.src = defaultAvatar;
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = defaultAvatar;
            preview.style.display = 'block';
        }
    });

    // Avatar upload for Edit Student modal
    document.getElementById('editAvatar')?.addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('editStudentAvatar');
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: "Error!",
                    text: "File size exceeds 2MB limit.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-info" },
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.src = preview.getAttribute('data-original-src') || defaultAvatar;
                return;
            }
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Error!",
                    text: "Only PNG, JPG, and JPEG files are allowed.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-info" },
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.src = preview.getAttribute('data-original-src') || defaultAvatar;
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = preview.getAttribute('data-original-src') || defaultAvatar;
        }
    });

    // Table row click events (Edit/Delete)
    document.getElementById('studentTableBody')?.addEventListener('click', function(e) {
        if (e.target.closest('.view-item-btn')) {
            const button = e.target.closest('.view-item-btn');
            const id = button.getAttribute('data-id');
            console.log('View button clicked for student ID:', id);
            if (!ensureAxios()) return;

            // Try to get more detailed student data with relationships
            axios.get(`/student/${id}/view`).then((response) => {
                console.log('Student data received for view:', response.data);
                const student = response.data.student || response.data;
                if (!student) {
                    throw new Error('Student data is empty');
                }

                // Populate the view modal with student data
                populateViewModal(student);

            }).catch((error) => {
                console.error('View endpoint failed, trying edit endpoint:', error);
                // Fallback to edit endpoint if view endpoint doesn't exist
                axios.get(`/student/${id}/edit`).then((response) => {
                    console.log('Student data received for view (fallback):', response.data);
                    const student = response.data.student;
                    if (!student) {
                        throw new Error('Student data is empty');
                    }

                    // Enhanced data merging from multiple sources
                    const currentStudent = allStudents.find(s => s.id == id);
                    if (currentStudent) {
                        student.schoolclass = student.schoolclass || currentStudent.schoolclass;
                        student.arm = student.arm || currentStudent.arm;
                    }

                    // Try to get term name from the terms dropdown data
                    const termSelect = document.getElementById('termid') || document.getElementById('editTermid');
                    if (termSelect && student.termid) {
                        const termOption = termSelect.querySelector(`option[value="${student.termid}"]`);
                        if (termOption) {
                            student.term_name = termOption.textContent;
                            console.log('Found term name from dropdown:', student.term_name);
                        }
                    }

                    // Try to get school house name from the houses dropdown data
                    const houseSelects = [
                        document.getElementById('school_house'),
                        document.getElementById('sport_house'),
                        document.querySelector('select[name="school_house"]'),
                        document.querySelector('select[name="sport_house"]')
                    ];

                    for (const houseSelect of houseSelects) {
                        if (houseSelect && (student.school_house_id || student.sport_house_id)) {
                            const houseId = student.school_house_id || student.sport_house_id;
                            const houseOption = houseSelect.querySelector(`option[value="${houseId}"]`);
                            if (houseOption) {
                                student.school_house = houseOption.textContent;
                                console.log('Found house name from dropdown:', student.school_house);
                                break;
                            }
                        }
                    }

                    // Log all available student data for debugging
                    console.log('Complete student data for debugging:', student);
                    console.log('Available term-related fields:', {
                        termid: student.termid,
                        term_name: student.term_name,
                        term: student.term,
                        schoolterm_name: student.schoolterm_name,
                        schoolterm: student.schoolterm
                    });
                    console.log('Available house-related fields:', {
                        school_house_id: student.school_house_id,
                        sport_house_id: student.sport_house_id,
                        school_house: student.school_house,
                        sport_house: student.sport_house,
                        house: student.house,
                        schoolhouse: student.schoolhouse
                    });

                    // Populate the view modal with student data
                    populateViewModal(student);

                }).catch((fallbackError) => {
                    console.error('Error fetching student for view:', {
                        message: fallbackError.message,
                        status: fallbackError.response?.status,
                        data: fallbackError.response?.data
                    });
                    Swal.fire({
                        title: 'Error!',
                        text: fallbackError.response?.data?.message || 'Failed to load student data. Check console for details.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                });
            });
        }

        if (e.target.closest('.edit-item-btn')) {
            const button = e.target.closest('.edit-item-btn');
            const id = button.getAttribute('data-id');
            console.log('Edit button clicked for student ID:', id);
            if (!ensureAxios()) return;

            axios.get(`/student/${id}/edit`).then((response) => {
                console.log('Student data received:', response.data);
                const student = response.data.student;
                if (!student) {
                    throw new Error('Student data is empty');
                }

                populateEditForm(student);
            }).catch((error) => {
                console.error('Error fetching student:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'Failed to load student data. Check console for details.',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            });
        }

        if (e.target.closest('.remove-item-btn')) {
            const button = e.target.closest('.remove-item-btn');
            const id = button.getAttribute('data-id');
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) {
                console.error(`Row with data-id="${id}" not found`);
                Swal.fire({
                    title: 'Error!',
                    text: 'Table row not found for deletion',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
                return;
            }
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed && ensureAxios()) {
                    axios.delete(`/student/${id}/destroy`).then(() => {
                        row.remove();
                        studentList.reIndex();
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Student has been deleted',
                            icon: 'success',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    }).catch((error) => {
                        console.error('Error deleting student:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: error.response?.data?.message || 'Failed to delete student',
                            icon: 'error',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    });
                }
            });
        }
    });

    // Add Student form submission
    document.getElementById('addStudentForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const formData = new FormData(this);
        console.log('Submitting addStudentForm with data:');
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        axios.post('/student', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        }).then((response) => {
            console.log('Add student response:', response.data);
            if (!response.data.success) {
                throw new Error(response.data.message || 'Failed to add student');
            }
            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student added successfully',
                icon: 'success',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            }).then(() => {
                fetchStudents();
                document.getElementById('addStudentForm').reset();
                document.getElementById('addStudentAvatar').src = defaultAvatar;
                document.getElementById('addStudentModal').querySelector('.btn-close').click();
            });
        }).catch((error) => {
            console.error('Error adding student:', {
                message: error.message,
                status: error.response?.status,
                data: error.response?.data
            });
            let errorMessage = error.response?.data?.message || 'Failed to add student. Check console for details.';
            if (error.response?.status === 422 && error.response?.data?.errors) {
                errorMessage = Object.values(error.response.data.errors).flat().join('\n');
            }
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });
    });

    // Edit Student form submission
    document.getElementById('editStudentForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const id = document.getElementById('editStudentId').value;
        const formData = new FormData(this);
        console.log('Submitting editStudentForm with data:');
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        axios.post(`/student/${id}`, formData, {
            headers: { 'X-HTTP-Method-Override': 'PATCH', 'Content-Type': 'multipart/form-data' }
        }).then((response) => {
            console.log('Edit student response:', response.data);
            if (!response.data.success) {
                throw new Error(response.data.message || 'Failed to update student');
            }
            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student updated successfully',
                icon: 'success',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            }).then(() => {
                fetchStudents();
                document.getElementById('editStudentModal').querySelector('.btn-close').click();
            });
        }).catch((error) => {
            console.error('Error updating student:', {
                message: error.message,
                status: error.response?.status,
                data: error.response?.data
            });
            let errorMessage = error.response?.data?.message || 'Failed to update student. Check console for details.';
            if (error.response?.status === 422 && error.response?.data?.errors) {
                errorMessage = Object.values(error.response.data.errors).flat().join('\n');
            }
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });
    });

    // Image view modal
    document.getElementById('imageViewModal')?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imageSrc = button.getAttribute('data-image');
        const modalImage = this.querySelector('#enlargedImage');
        modalImage.src = imageSrc;
    });

    // Debug permissions
    console.log('Permissions:', window.appPermissions || 'Not defined');
}

// Call initializeStudentList on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeStudentList();
});

// Add event listeners for view toggle buttons
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tableViewBtn')?.addEventListener('click', () => toggleView('table'));
    document.getElementById('cardViewBtn')?.addEventListener('click', () => toggleView('card'));
});
</script>
@endsection
