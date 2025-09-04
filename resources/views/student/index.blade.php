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
                
                /* body {
                    background-color: #f8f9fa;
                    padding: 20px;
                }
                
                .container {
                    max-width: 1200px;
                } */
            </style>
        </head>
        <body>
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
                                    <div class="col-md-3">
                                        <select class="form-control select2" id="class-term-filter" name="class_term" data-placeholder="Select Class and Term">
                                            <option value="all">All Class/Term</option>
                                            @foreach ($schoolclasses as $class)
                                                @foreach ($schoolterms as $term)
                                                    <option value="{{ $class->id }}-{{ $term->id }}">{{ $class->schoolclass }} - {{ $class->arm }} / {{ $term->name }}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="status-filter" data-choices data-choices-search-false>
                                            <option value="all">All Statuses</option>
                                            <option value="1">Old Student</option>
                                            <option value="2">New Student</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="gender-filter" data-choices data-choices-search-false>
                                            <option value="all">All Genders</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="student-status-filter" data-choices data-choices-search-false>
                                            <option value="all">All Student Statuses</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filter</button>
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
                                            <select class="form-control" id="admissionYear" name="admissionYear" required>
                                                @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                @endfor
                                            </select>
                                            <span class="input-group-text bg-primary text-white">CSSK/STD/</span>
                                            <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="001" required>
                                        </div>
                                    </div>

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
                                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
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
                                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
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
                                            <option value="Border">Boarding Student</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <select id="title" name="title" class="form-control">
                                                    <option value="">Select</option>
                                                    <option value="Master">Master</option>
                                                    <option value="Miss">Miss</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last name" required>
                                            </div>
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
                                        <label for="present_address" class="form-label">Present Address <span class="text-danger">*</span></label>
                                        <textarea id="present_address" name="present_address" class="form-control" rows="2" placeholder="Enter current address" required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="permanent_address" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                        <textarea id="permanent_address" name="permanent_address" class="form-control" rows="2" placeholder="Enter permanent address" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Section C: Additional Details -->
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                </div>
                                <div class="mb-3">
                                    <label for="placeofbirth" class="form-label">Nationality</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="input" id="nataionality" name="nationality" class="form-control" placeholder="Nationality">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="state" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                                <select id="addState" name="state" class="form-control" required>
                                                    <option value="">Select State</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="local" class="form-label">Local Government <span class="text-danger">*</span></label>
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
                                                <label for="sport_house" class="form-label">Sport House</label>
                                                <input type="text" id="sport_house" name="sport_house" class="form-control" placeholder="House name">
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

<!-- CSS Styles -->
<style>
    .modal-xl {
        max-width: 90%;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .upload-area:hover {
        background-color: #f8f9ff;
        border-color: #5a67d8 !important;
    }
    .input-group-text {
        border-color: #667eea;
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    .progress-steps::before {
        content: '';
        position: absolute;
        top: 12px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #e9ecef;
        z-index: -1;
    }
    .step {
        background-color: #e9ecef;
        color: #6c757d;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        position: relative;
        background-color: white;
        border: 2px solid #e9ecef;
    }
    .step.active {
        background-color: #667eea;
        color: white;
        border-color: #667eea;
    }
    .step.completed {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }
    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
</style>

<!-- JavaScript Functions -->
<script>
    // Image preview function
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('addStudentAvatar').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Age calculation function
    function calculateAge(dateOfBirth) {
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        document.getElementById('addAgeInput').value = age;
    }

    // Toggle admission input based on mode
    function toggleAdmissionInput() {
        const autoMode = document.getElementById('admissionAuto').checked;
        const admissionNoInput = document.getElementById('admissionNo');
        
        if (autoMode) {
            admissionNoInput.disabled = true;
            admissionNoInput.value = 'AUTO';
            admissionNoInput.placeholder = 'Will be auto-generated';
        } else {
            admissionNoInput.disabled = false;
            admissionNoInput.value = '';
            admissionNoInput.placeholder = 'Enter number (e.g., 001)';
        }
    }

    // Load states and LGAs
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/states_lgas.json')
            .then(response => response.json())
            .then(data => {
                const stateSelect = document.getElementById('addState');
                const localSelect = document.getElementById('addLocal');

                data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.state;
                    option.textContent = state.state;
                    stateSelect.appendChild(option);
                });

                stateSelect.addEventListener('change', function() {
                    localSelect.innerHTML = '<option value="">Select LGA</option>';
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
            })
            .catch(error => {
                console.error('Error loading states and LGAs:', error);
            });
    });

    // Print student details function
    function printStudentDetails() {
        // Implementation for printing student details
        alert('Print functionality would be implemented here');
    }
</script>

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
                            <!-- Section A: Academic Details -->
                            <h6 class="mb-3">Section A: Academic Details</h6>
                            <div class="mb-3">
                                <label class="form-label">Admission Number Mode <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionAuto" value="auto" required onchange="toggleAdmissionInput('edit')">
                                        <label class="form-check-label" for="editAdmissionAuto">Auto</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionManual" value="manual" required onchange="toggleAdmissionInput('edit')">
                                        <label class="form-check-label" for="editAdmissionManual">Manual</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="editAdmissionNo" class="form-label">Admission No <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control" id="editAdmissionYear" name="admissionYear" required>
                                        @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                    <span class="input-group-text">CSSK/STD/</span>
                                    <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="Enter number (e.g., 001)" required>
                                </div>
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
                            <div class="mb-3">
                                <label for="editTermid" class="form-label">Term <span class="text-danger">*</span></label>
                                <select id="editTermid" name="termid" class="form-control" required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterms as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSessionid" class="form-label">Session <span class="text-danger">*</span></label>
                                <select id="editSessionid" name="sessionid" class="form-control" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsessions as $session)
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
                            <div class="mb-3">
                                <label for="editStudentCategory" class="form-label">Student Category <span class="text-danger">*</span></label>
                                <select id="editStudentCategory" name="student_category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Day">Day</option>
                                    <option value="Boarding">Boarding</option>
                                </select>
                            </div>

                            <!-- Section B: Student’s Details -->
                            <h6 class="mb-3 mt-4">Section B: Student’s Details</h6>
                            <div class="mb-3">
                                <label for="editAvatar" class="form-label">Avatar</label>
                                <input type="file" id="editAvatar" name="avatar" class="form-control" accept=".png,.jpg,.jpeg">
                                <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" style="max-width: 100px; margin-top: 10px;" />
                                <div class="form-text">Allowed file types: png, jpg, jpeg. Max size: 2MB.</div>
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
                                <label for="editPhoneNumber" class="form-label">Phone Number</label>
                                <input type="text" id="editPhoneNumber" name="phone_number" class="form-control" placeholder="Enter phone number">
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

                            <!-- Section C: Parent's Details/Guardian's Details -->
                            <h6 class="mb-3 mt-4">Section C: Parent's Details/Guardian's Details</h6>
                            <div class="mb-3">
                                <label for="editFatherTitle" class="form-label">Father's Title</label>
                                <select id="editFatherTitle" name="father_title" class="form-control">
                                    <option value="">Select Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Prof">Prof</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editFather" class="form-label">Father's Name</label>
                                <input type="text" id="editFather" name="father" class="form-control" placeholder="Enter father's name">
                            </div>
                            <div class="mb-3">
                                <label for="editFatherPhone" class="form-label">Father's Phone</label>
                                <input type="text" id="editFatherPhone" name="father_phone" class="form-control" placeholder="Enter father's phone">
                            </div>
                            <div class="mb-3">
                                <label for="editFatherOccupation" class="form-label">Father's Occupation</label>
                                <input type="text" id="editFatherOccupation" name="father_occupation" class="form-control" placeholder="Enter father's occupation">
                            </div>
                            <div class="mb-3">
                                <label for="editMotherTitle" class="form-label">Mother's Title</label>
                                <select id="editMotherTitle" name="mother_title" class="form-control">
                                    <option value="">Select Title</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Prof">Prof</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editMother" class="form-label">Mother's Name</label>
                                <input type="text" id="editMother" name="mother" class="form-control" placeholder="Enter mother's name">
                            </div>
                            <div class="mb-3">
                                <label for="editMotherPhone" class="form-label">Mother's Phone</label>
                                <input type="text" id="editMotherPhone" name="mother_phone" class="form-control" placeholder="Enter mother's phone">
                            </div>
                            <div class="mb-3">
                                <label for="editParentAddress" class="form-label">Parent's Address</label>
                                <input type="text" id="editParentAddress" name="parent_address" class="form-control" placeholder="Enter parent's address">
                            </div>
                            <div class="mb-3">
                                <label for="editOfficeAddress" class="form-label">Office Address</label>
                                <input type="text" id="editOfficeAddress" name="office_address" class="form-control" placeholder="Enter office address">
                            </div>

                            <!-- Section D: Previous School’s Details -->
                            <h6 class="mb-3 mt Commodore Sans Serif-4">Section D: Previous School’s Details</h6>
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
                            <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="edit-btn">Update Student</button>
                            <button type="button" class="btn btn-success" onclick="printStudentDetails('edit')">Print PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fetch states and LGAs
    fetch('/states_lgas.json')
        .then(response => response.json())
        .then(data => {
            const stateSelect = document.getElementById('addState');
            const localSelect = document.getElementById('addLocal');
            const editStateSelect = document.getElementById('editState');
            const editLocalSelect = document.getElementById('editLocal');

            data.forEach(state => {
                const option = document.createElement('option');
                option.value = state.state;
                option.textContent = state.state;
                stateSelect.appendChild(option);
                editStateSelect.appendChild(option.cloneNode(true));
            });

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

    // Avatar preview
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

    // Admission number handling
    window.toggleAdmissionInput = function (prefix = '') {
    const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`).value;
    const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
    const admissionYearSelect = document.getElementById(`${prefix}admissionYear`);

    if (admissionMode === 'auto') {
        admissionNoInput.readOnly = true;
        fetch('/students/last-admission-number', {
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
                alert('Error generating admission number: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate admission number');
        });
    } else {
        admissionNoInput.readOnly = false;
        admissionNoInput.value = '';
    }
};

    // Print student details
    window.printStudentDetails = function (prefix = '') {
        const form = document.getElementById(`${prefix}StudentForm`);
        const formData = new FormData(form);
        fetch('/generate-student-pdf', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'student_details.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error generating PDF:', error);
            alert('Failed to generate PDF');
        });
    };
});


document.addEventListener('DOMContentLoaded', function () {
    // Ensure Axios and CSRF token are available
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

    let studentList;
    let allStudents = [];
    const itemsPerPage = 10;

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
                console.log('Processed students:', allStudents);
                document.querySelector('#totalStudents').textContent = allStudents.length;
                document.querySelector('#totalCount').textContent = allStudents.length;
                renderStudents(allStudents);
            })
            .catch((error) => {
                console.error('Error fetching students:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data,
                    url: '/students/data'
                });
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || error.message || "Failed to load students. Check console for details.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false
                });
                renderStudents([]);
            });
    }

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
            const studentImage = student.picture 
                ? '/storage/' + student.picture 
                : '/storage/student_avatars/unnamed.jpg';
            const row = document.createElement('tr');
            const actionButtons = [];
            if (window.appPermissions?.canShowStudent) {
                actionButtons.push(`<li><a href="/student/${student.id}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a></li>`);
            }
            if (window.appPermissions?.canUpdateStudent) {
                actionButtons.push(`<li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-bs-toggle="modal" data-bs-target="#editStudentModal" data-id="${student.id}"><i class="ph-pencil"></i></a></li>`);
            }
            if (window.appPermissions?.canDeleteStudent) {
                actionButtons.push(`<li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="${student.id}"><i class="ph-trash"></i></a></li>`);
            }
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
        initializeList();
        initializeCheckboxes();
    }

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

    function filterData() {
        if (!studentList) return;
        const search = document.querySelector('#search-input')?.value.toLowerCase() || '';
        const classId = document.getElementById('schoolclass-filter')?.value || 'all';
        const statusId = document.getElementById('status-filter')?.value || 'all';
        const gender = document.getElementById('gender-filter')?.value || 'all';
        const studentStatus = document.getElementById('student-status-filter')?.value || 'all';

        console.log('Filtering with:', { search, classId, statusId, gender, studentStatus });

        studentList.filter(item => {
            const name = item.values().name?.toLowerCase() || '';
            const admissionNo = item.values().admissionNo?.toLowerCase() || '';
            const classValue = item.elm.querySelector('.class')?.dataset.class || '';
            const statusValue = item.elm.querySelector('.status')?.dataset.status || '';
            const genderValue = item.elm.querySelector('.gender')?.dataset.gender || '';
            const studentStatusValue = item.elm.querySelector('.status')?.dataset.student_status || '';

            const matchesSearch = name.includes(search) || admissionNo.includes(search);
            const matchesClass = classId === 'all' || classValue === classId;
            const matchesStatus = statusId === 'all' || statusValue === statusId;
            const matchesGender = gender === 'all' || genderValue === gender;
            const matchesStudentStatus = studentStatus === 'all' || studentStatusValue === studentStatus;

            return matchesSearch && matchesClass && matchesStatus && matchesGender && matchesStudentStatus;
        });
    }

    function deleteMultiple() {
        const ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
            .map(checkbox => checkbox.closest('tr').querySelector('.id').dataset.id);

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
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-light" },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed && ensureAxios()) {
                axios.post('/students/destroy-multiple', { ids }).then(() => {
                    ids.forEach(id => {
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) row.remove();
                    });
                    studentList.reIndex();
                    Swal.fire({
                        title: "Deleted!",
                        text: "Students have been deleted",
                        icon: "success",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
                    document.getElementById('checkAll').checked = false;
                    document.getElementById('remove-actions').classList.add('d-none');
                }).catch((error) => {
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

    function populateStates(stateSelectId, lgaSelectId) {
        fetch('/states_lgas.json')
            .then(response => response.json())
            .then(data => {
                const stateSelect = document.getElementById(stateSelectId);
                const lgaSelect = document.getElementById(lgaSelectId);
                if (!stateSelect || !lgaSelect) return;

                stateSelect.innerHTML = '<option value="">Select State</option>';
                data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.state;
                    option.textContent = state.state;
                    stateSelect.appendChild(option);
                });

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
                    }
                });
            })
            .catch(error => {
                console.error('Error loading states and LGAs:', error);
            });
    }

    function populateLGAs(state, lgaSelectId) {
        fetch('/states_lgas.json')
            .then(response => response.json())
            .then(data => {
                const lgaSelect = document.getElementById(lgaSelectId);
                if (!lgaSelect) return;
                lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                const selectedState = data.find(s => s.state === state);
                if (selectedState) {
                    selectedState.lgas.forEach(lga => {
                        const option = document.createElement('option');
                        option.value = lga;
                        option.textContent = lga;
                        lgaSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading LGAs:', error);
            });
    }

    function initializeStudentList() {
        populateStates('addState', 'addLocal');
        populateStates('editState', 'editLocal');
        fetchStudents();

        document.querySelector('#search-input')?.addEventListener('input', filterData);
        document.getElementById('schoolclass-filter')?.addEventListener('change', filterData);
        document.getElementById('status-filter')?.addEventListener('change', filterData);
        document.getElementById('gender-filter')?.addEventListener('change', filterData);
        document.getElementById('student-status-filter')?.addEventListener('change', filterData);

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
                    preview.style.display = 'none';
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
                    preview.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '/theme/layouts/assets/media/avatars/blank.png';
                preview.style.display = 'none';
            }
        });

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
                    preview.src = preview.getAttribute('data-original-src') || '/theme/layouts/assets/media/avatars/blank.png';
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
                    preview.src = preview.getAttribute('data-original-src') || '/theme/layouts/assets/media/avatars/blank.png';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = preview.getAttribute('data-original-src') || '/theme/layouts/assets/media/avatars/blank.png';
            }
        });

        document.getElementById('studentTableBody')?.addEventListener('click', function(e) {
            if (e.target.closest('.edit-item-btn')) {
                const button = e.target.closest('.edit-item-btn');
                const id = button.getAttribute("data-id");
                console.log("Edit button clicked for student ID:", id);
                if (!ensureAxios()) return;

                axios.get(`/student/${id}/edit`).then((response) => {
                    console.log("Student data received:", response.data);
                    const student = response.data.student;
                    if (!student) {
                        throw new Error("Student data is empty");
                    }

                    const fields = [
                        { id: "editStudentId", value: student.id },
                        { id: "editAdmissionNo", value: student.admissionNo },
                        { id: "editAdmissionYear", value: student.admissionYear },
                        { id: "editTittle", value: student.title || '' },
                        { id: "editFirstname", value: student.firstname },
                        { id: "editLastname", value: student.lastname },
                        { id: "editOthername", value: student.othername || '' },
                        { id: "editHomeAddress", value: student.present_address },
                        { id: "editHomeAddress2", value: student.permanent_address },
                        { id: "editDOB", value: student.dateofbirth },
                        { id: "editPlaceofbirth", value: student.placeofbirth || '' },
                        { id: "editNationality", value: student.nationality || '' },
                        { id: "editReligion", value: student.religion || '' },
                        { id: "editLastSchool", value: student.last_school || '' },
                        { id: "editLastClass", value: student.last_class || '' },
                        { id: "editSchoolclassid", value: student.schoolclassid || '' },
                        { id: "editTermid", value: student.termid || '' },
                        { id: "editSessionid", value: student.sessionid || '' },
                        { id: "editPhoneNumber", value: student.phone_number || '' },
                        { id: "editNinNumber", value: student.nin_number || '' },
                        { id: "editBloodGroup", value: student.blood_group || '' },
                        { id: "editMotherTongue", value: student.mother_tongue || '' },
                        { id: "editFatherTitle", value: student.father_title || '' },
                        { id: "editFather", value: student.father_name || '' },
                        { id: "editFatherPhone", value: student.father_phone || '' },
                        { id: "editFatherOccupation", value: student.father_occupation || '' },
                        { id: "editMotherTitle", value: student.mother_title || '' },
                        { id: "editMother", value: student.mother_name || '' },
                        { id: "editMotherPhone", value: student.mother_phone || '' },
                        { id: "editParentAddress", value: student.parent_address || '' },
                        { id: "editOfficeAddress", value: student.office_address || '' },
                        { id: "editStudentCategory", value: student.student_category || '' },
                        { id: "editReasonForLeaving", value: student.reason_for_leaving || '' }
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

                    const avatarElement = document.getElementById("editStudentAvatar");
                    if (avatarElement) {
                        avatarElement.src = student.picture ? `/storage/${student.picture}` : '/storage/student_avatars/unnamed.jpg';
                        avatarElement.setAttribute('data-original-src', student.picture ? `/storage/${student.picture}` : '/storage/student_avatars/unnamed.jpg');
                    }

                    const stateSelect = document.getElementById("editState");
                    const lgaSelect = document.getElementById("editLocal");
                    if (student.state && stateSelect) {
                        stateSelect.value = student.state;
                        setTimeout(() => {
                            populateLGAs(student.state, 'editLocal');
                            setTimeout(() => {
                                if (lgaSelect) {
                                    lgaSelect.value = student.local || '';
                                }
                            }, 200);
                        }, 100);
                    } else if (lgaSelect) {
                        lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                    }

                    if (student.dateofbirth) {
                        showage(student.dateofbirth, 'editAge');
                    }

                    const form = document.getElementById('editStudentForm');
                    if (form) {
                        form.action = `/student/${id}`;
                    }
                }).catch((error) => {
                    console.error("Error fetching student:", {
                        message: error.message,
                        status: error.response?.status,
                        data: error.response?.data
                    });
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to load student data. Check console for details.",
                        icon: "error",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
                });
            }

            if (e.target.closest('.remove-item-btn')) {
                const button = e.target.closest('.remove-item-btn');
                const id = button.getAttribute('data-id');
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-light" },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed && ensureAxios()) {
                        axios.delete(`/student/${id}/destroy`).then(() => {
                            const row = button.closest('tr');
                            if (row) row.remove();
                            studentList.reIndex();
                            Swal.fire({
                                title: "Deleted!",
                                text: "Student has been deleted",
                                icon: "success",
                                customClass: { confirmButton: "btn btn-primary" },
                                buttonsStyling: false
                            });
                        }).catch((error) => {
                            console.error('Error deleting student:', error);
                            Swal.fire({
                                title: "Error!",
                                text: error.response?.data?.message || "Failed to delete student",
                                icon: "error",
                                customClass: { confirmButton: "btn btn-primary" },
                                buttonsStyling: false
                            });
                        });
                    }
                });
            }
        });

        document.getElementById('addStudentForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!ensureAxios()) return;

            const formData = new FormData(this);
            console.log('Submitting addStudentForm with data:');
            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }

            axios.post(this.action, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then((response) => {
                console.log('Add student response:', response.data);
                Swal.fire({
                    title: "Success!",
                    text: "Student added successfully",
                    icon: "success",
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false
                }).then(() => {
                    fetchStudents();
                    document.getElementById('addStudentForm').reset();
                    document.getElementById('addStudentAvatar').src = '/theme/layouts/assets/media/avatars/blank.png';
                    document.getElementById('addStudentModal').querySelector('.btn-close').click();
                });
            }).catch((error) => {
                console.error('Error adding student:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "Failed to add student. Check console for details.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false
                });
            });
        });

        document.getElementById('editStudentForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!ensureAxios()) return;

            const id = document.getElementById('editStudentId').value;
            const formData = new FormData(this);
            console.log('Submitting editStudentForm with data:');
            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }

            axios.post(this.action, formData, {
                headers: { 'X-HTTP-Method-Override': 'PATCH', 'Content-Type': 'multipart/form-data' }
            }).then((response) => {
                console.log('Edit student response:', response.data);
                Swal.fire({
                    title: "Success!",
                    text: "Student updated successfully",
                    icon: "success",
                    customClass: { confirmButton: "btn btn-primary" },
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
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "Failed to update student. Check console for details.",
                    icon: "error",
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false
                });
            });
        });

        document.getElementById('imageViewModal')?.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const imageSrc = button.getAttribute('data-image');
            const modalImage = this.querySelector('#enlargedImage');
            modalImage.src = imageSrc;
        });
    }

    // Initialize the student list
    initializeStudentList();
});
</script>

@endsection