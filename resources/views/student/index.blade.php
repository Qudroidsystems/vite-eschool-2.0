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
                /* ... (all existing CSS remains the same) ... */
            </style>
            <!-- ... (dashboard statistics and charts remain the same) ... -->

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
        <!-- Add Student Modal (remains the same) -->
        <!-- Edit Student Modal (remains the same) -->
        <!-- View Student Modal -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down modal-xl">
                <div class="modal-content modern-modal">
                    <!-- Header with Gradient -->
                    <div class="modal-header modern-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="header-text">
                                <h4 class="modal-title mb-0">Student Details</h4>
                                <p class="header-subtitle mb-0">Comprehensive Student Information</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body modern-body">
                        <div class="registration-form">
                            <!-- Student Photo Section -->
                            <div class="student-header">
                                <div class="photo-container">
                                    <div class="photo-frame">
                                        <img id="viewStudentPhoto" src="{{ asset('storage/images/student_avatars/unnamed.jpg') }}" alt="Student Photo" class="student-photo">
                                        <div class="photo-overlay">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progressive Tabs Navigation -->
                            <div class="form-navigation">
                                <nav class="nav nav-pills nav-justified modern-tabs" id="pills-tab" role="tablist">
                                    <button class="nav-link active" id="academic-tab" data-bs-toggle="pill" data-bs-target="#academic" type="button" role="tab">
                                        <i class="fas fa-school"></i>
                                        <span>Academic</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                    <button class="nav-link" id="personal-tab" data-bs-toggle="pill" data-bs-target="#personal" type="button" role="tab">
                                        <i class="fas fa-user"></i>
                                        <span>Personal</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                    <button class="nav-link" id="guardian-tab" data-bs-toggle="pill" data-bs-target="#guardian" type="button" role="tab">
                                        <i class="fas fa-users"></i>
                                        <span>Guardian</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                    <button class="nav-link" id="previous-tab" data-bs-toggle="pill" data-bs-target="#previous" type="button" role="tab">
                                        <i class="fas fa-history"></i>
                                        <span>Previous</span>
                                        <div class="tab-progress"></div>
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content modern-tabs-content" id="pills-tabContent">

                                <!-- Academic Details Tab -->
                                <div class="tab-pane fade show active" id="academic" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-school me-2"></i>Academic Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Academic Year</label>
                                                <div class="form-value" id="viewAcademicYear">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Registration No.</label>
                                                <div class="form-value highlight" id="viewRegistrationNo">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Admission Date</label>
                                                <div class="form-value" id="viewAdmissionDate">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Class</label>
                                                <div class="form-value class-badge" id="viewClass">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Term</label>
                                                <div class="form-value" id="viewTerm">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Session</label>
                                                <div class="form-value" id="viewSession">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Category</label>
                                                <div class="category-badges">
                                                    <span class="category-badge day" id="dayBadge">
                                                        <i class="fas fa-sun"></i> Day Student
                                                    </span>
                                                    <span class="category-badge boarding" id="boardingBadge">
                                                        <i class="fas fa-home"></i> Boarding
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Details Tab -->
                                <div class="tab-pane fade" id="personal" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-user me-2"></i>Personal Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <div class="name-container">
                                                    <div class="name-part">
                                                        <label class="form-label">Surname</label>
                                                        <div class="form-value" id="viewSurname">-</div>
                                                    </div>
                                                    <div class="name-part">
                                                        <label class="form-label">First Name</label>
                                                        <div class="form-value" id="viewFirstName">-</div>
                                                    </div>
                                                    <div class="name-part">
                                                        <label class="form-label">Middle Name</label>
                                                        <div class="form-value" id="viewMiddleName">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Gender</label>
                                                <div class="form-value gender-badge" id="viewGender">
                                                    <i class="fas fa-user"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Date of Birth</label>
                                                <div class="form-value" id="viewDateOfBirth">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Blood Group</label>
                                                <div class="form-value blood-group" id="viewBloodGroup">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mother Tongue</label>
                                                <div class="form-value" id="viewMotherTongue">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Religion</label>
                                                <div class="form-value" id="viewReligion">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Sport House</label>
                                                <div class="form-value" id="viewSportHouse">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mobile Number</label>
                                                <div class="form-value contact" id="viewMobileNumber">
                                                    <i class="fas fa-phone"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Email</label>
                                                <div class="form-value contact" id="viewEmail">
                                                    <i class="fas fa-envelope"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">NIN</label>
                                                <div class="form-value" id="viewNIN">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">City</label>
                                                <div class="form-value" id="viewCity">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">State</label>
                                                <div class="form-value" id="viewState">-</div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Permanent Address</label>
                                                <div class="form-value address-field" id="viewPermanentAddress">-</div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Future Ambition</label>
                                                <div class="form-value address-field" id="viewFutureAmbition">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guardian Details Tab -->
                                <div class="tab-pane fade" id="guardian" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-users me-2"></i>Guardian Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Father's Name</label>
                                                <div class="form-value" id="viewFatherName">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mother's Name</label>
                                                <div class="form-value" id="viewMotherName">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Occupation</label>
                                                <div class="form-value occupation-badge" id="viewOccupation">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">City</label>
                                                <div class="form-value" id="viewParentCity">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mobile Number</label>
                                                <div class="form-value contact" id="viewParentMobile">
                                                    <i class="fas fa-phone"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Email</label>
                                                <div class="form-value contact" id="viewParentEmail">
                                                    <i class="fas fa-envelope"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Address</label>
                                                <div class="form-value address-field" id="viewParentAddress">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Previous School Tab -->
                                <div class="tab-pane fade" id="previous" role="tabpanel">
                                    <div class="form-section">
                                        <div class="section-header">
                                            <h5><i class="fas fa-history me-2"></i>Previous School Information</h5>
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <label class="form-label">School Name</label>
                                                <div class="form-value school-name" id="viewSchoolName">
                                                    <i class="fas fa-school"></i> -
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Previous Class</label>
                                                <div class="form-value class-badge" id="viewPreviousClass">-</div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label">Reason for Leaving</label>
                                                <div class="form-value reason-field" id="viewReasonLeaving">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Footer -->
                    <div class="modal-footer modern-footer">
                        <div class="footer-actions">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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

// Render students as cards - FIXED "OM" issue
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
        console.log('Processing student for card:', student);

        // Get initials for avatar - FIXED: Check if firstname/lastname exist
        const firstName = student.firstname || '';
        const lastName = student.lastname || '';

        // Calculate initials properly - FIXED LOGIC
        let displayInitials = '??';
        if (firstName || lastName) {
            const firstInitial = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
            const lastInitial = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
            displayInitials = (firstInitial + lastInitial) || '??';
        }

        // Get avatar URL - handle different possible field names
        let avatarUrl = defaultAvatar;
        if (student.picture && student.picture !== 'unnamed.jpg') {
            avatarUrl = `/storage/images/student_avatars/${student.picture}`;
        } else if (student.avatar && student.avatar !== 'unnamed.jpg') {
            avatarUrl = `/storage/images/student_avatars/${student.avatar}`;
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

        // Get class name - handle both formats
        const className = student.class_name || student.schoolclass || 'N/A';
        const classArm = student.arm || '';
        const fullClassName = className + (classArm ? ' - ' + classArm : '');

        // Create card HTML
        const cardHtml = `
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="student-card" data-id="${student.id}"
                     data-name="${student.lastname} ${student.firstname} ${student.othername || ''}"
                     data-admission="${student.admissionNo || ''}"
                     data-class="${student.schoolclassid || ''}"
                     data-status="${student.statusId || ''}"
                     data-gender="${student.gender || ''}"
                     data-student-status="${student.student_status || ''}">

                    <!-- Checkbox for multiple selection -->
                    <div class="checkbox-container">
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox" name="chk_child" value="${student.id}">
                        </div>
                    </div>

                    <!-- Status badge -->
                    <span class="status-badge ${statusClass}">${statusText}</span>

                    <!-- Action buttons -->
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

                    <!-- Avatar with fallback to initials -->
                    <div class="avatar-container">
                        <img src="${avatarUrl}" alt="${student.firstname} ${student.lastname}"
                             class="avatar" onerror="handleAvatarError(this, '${displayInitials}')">
                    </div>

                    <!-- Student name -->
                    <h6 class="student-name">${student.lastname} ${student.firstname}</h6>

                    <!-- Admission number -->
                    <p class="student-admission">${student.admissionNo || 'No Admission No'}</p>

                    <!-- Student details -->
                    <div class="student-details">
                        <div><strong>Class:</strong> ${fullClassName}</div>
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

// Handle avatar error
function handleAvatarError(imgElement, initials) {
    console.log('Avatar image failed to load, showing initials:', initials);
    const avatarContainer = imgElement.parentElement;
    imgElement.style.display = 'none';

    // Create initials div
    const initialsDiv = document.createElement('div');
    initialsDiv.className = 'avatar-initials';
    initialsDiv.textContent = initials;
    initialsDiv.style.display = 'flex';

    avatarContainer.appendChild(initialsDiv);
}

// Update counts display
function updateCounts(count) {
    const totalStudents = document.getElementById('totalStudents');
    const totalCount = document.getElementById('totalCount');
    const showingCount = document.getElementById('showingCount');

    if (totalStudents) totalStudents.textContent = count;
    if (totalCount) totalCount.textContent = count;
    if (showingCount) showingCount.textContent = count;
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

// View student details - FIXED VERSION
function viewStudent(id) {
    console.log('View student:', id);
    if (!ensureAxios()) return;

    // Show loading state
    Swal.fire({
        title: 'Loading...',
        text: 'Fetching student details',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Try your working endpoint first
    axios.get(`/student/${id}/edit`)
        .then((response) => {
            Swal.close();
            console.log('Student data received for view:', response.data);

            let student = response.data.student || response.data;

            if (!student) {
                throw new Error('Student data is empty');
            }

            // Ensure we have class, term, and session relationships
            if (response.data.schoolclass) {
                student.schoolclass = response.data.schoolclass.schoolclass;
                student.arm = response.data.schoolclass.arm;
            }

            if (response.data.term) {
                student.term_name = response.data.term.name;
            }

            if (response.data.session) {
                student.session_name = response.data.session.name;
            }

            // Populate the view modal
            populateViewModal(student);

            // Show the view modal
            const viewModalElement = document.getElementById('viewStudentModal');
            if (viewModalElement) {
                const viewModal = new bootstrap.Modal(viewModalElement);
                viewModal.show();
            } else {
                console.error('View modal element not found');
                Swal.fire({
                    title: 'Error!',
                    text: 'View modal not found',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch((error) => {
            console.error('Error fetching student for view:', error);

            // Fallback: try the show endpoint
            axios.get(`/student/${id}`)
                .then((response) => {
                    Swal.close();
                    console.log('Student data received (fallback):', response.data);
                    let student = response.data.student || response.data.data || response.data;

                    if (!student) {
                        throw new Error('Student data is empty');
                    }

                    populateViewModal(student);

                    const viewModalElement = document.getElementById('viewStudentModal');
                    if (viewModalElement) {
                        const viewModal = new bootstrap.Modal(viewModalElement);
                        viewModal.show();
                    }
                })
                .catch((fallbackError) => {
                    Swal.close();
                    console.error('Fallback also failed:', fallbackError);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load student data. Please try again.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                });
        });
}

// Function to populate view modal - FIXED with all fields
function populateViewModal(student) {
    console.log('=== DEBUG: Populating View Modal ===');
    console.log('Student object:', student);

    // Student Photo - FIXED: Use local image to avoid network errors
    const photoElement = document.getElementById('viewStudentPhoto');
    if (photoElement) {
        // First set to default local image
        photoElement.src = defaultAvatar;

        // Then try to load actual photo if it exists
        if (student.picture && student.picture !== 'unnamed.jpg') {
            const actualPhoto = new Image();
            actualPhoto.src = `/storage/images/student_avatars/${student.picture}`;
            actualPhoto.onload = function() {
                photoElement.src = this.src;
            };
            actualPhoto.onerror = function() {
                // Keep default image
                console.log('Student photo not found, keeping default');
            };
        }
    }

    // Academic Details
    setElementText('viewAcademicYear', student.admissionYear || student.admission_year || '-');
    setElementText('viewRegistrationNo', student.admissionNo || student.admission_no || '-');

    if (student.admissionDate) {
        const date = new Date(student.admissionDate);
        setElementText('viewAdmissionDate', date.toLocaleDateString());
    } else {
        setElementText('viewAdmissionDate', '-');
    }

    // Class information - FIXED: Get from relationships
    let className = '-';
    if (student.schoolclass && student.arm) {
        className = `${student.schoolclass} - ${student.arm}`;
    } else if (student.class_name) {
        className = student.class_name;
    } else if (student.schoolclass) {
        className = student.schoolclass;
    }
    setElementText('viewClass', className);

    // Term information - FIXED: Get from relationships
    let termName = '-';
    if (student.term_name) {
        termName = student.term_name;
    } else if (student.term && student.term.name) {
        termName = student.term.name;
    } else if (student.term) {
        termName = student.term;
    }
    setElementText('viewTerm', termName);

    // Session information - FIXED: Get from relationships
    let sessionName = '-';
    if (student.session_name) {
        sessionName = student.session_name;
    } else if (student.session && student.session.name) {
        sessionName = student.session.name;
    } else if (student.session) {
        sessionName = student.session;
    }
    setElementText('viewSession', sessionName);

    // Category badges
    const dayBadge = document.getElementById('dayBadge');
    const boardingBadge = document.getElementById('boardingBadge');
    if (dayBadge && boardingBadge) {
        dayBadge.classList.remove('active');
        boardingBadge.classList.remove('active');

        if (student.student_category === 'Day') {
            dayBadge.classList.add('active');
        } else if (student.student_category === 'Boarding') {
            boardingBadge.classList.add('active');
        }
    }

    // Personal Details
    setElementText('viewSurname', student.lastname || student.last_name || '-');
    setElementText('viewFirstName', student.firstname || student.first_name || '-');
    setElementText('viewMiddleName', student.othername || student.other_name || student.middle_name || '-');

    const genderElement = document.getElementById('viewGender');
    if (genderElement) {
        const gender = student.gender || '-';
        if (gender === 'Male') {
            genderElement.innerHTML = '<i class="fas fa-male"></i> Male';
        } else if (gender === 'Female') {
            genderElement.innerHTML = '<i class="fas fa-female"></i> Female';
        } else {
            genderElement.innerHTML = '<i class="fas fa-user"></i> -';
        }
    }

    if (student.dateofbirth) {
        const dob = new Date(student.dateofbirth);
        setElementText('viewDateOfBirth', dob.toLocaleDateString());
    } else {
        setElementText('viewDateOfBirth', '-');
    }

    setElementText('viewBloodGroup', student.blood_group || '-');
    setElementText('viewMotherTongue', student.mother_tongue || '-');
    setElementText('viewReligion', student.religion || '-');
    setElementText('viewSportHouse', student.school_house || student.sport_house || '-');

    const mobileElement = document.getElementById('viewMobileNumber');
    if (mobileElement) {
        const phone = student.phone_number || '-';
        mobileElement.innerHTML = phone !== '-' ?
            `<i class="fas fa-phone"></i> ${phone}` :
            '<i class="fas fa-phone"></i> -';
    }

    const emailElement = document.getElementById('viewEmail');
    if (emailElement) {
        const email = student.email || '-';
        emailElement.innerHTML = email !== '-' ?
            `<i class="fas fa-envelope"></i> ${email}` :
            '<i class="fas fa-envelope"></i> -';
    }

    setElementText('viewNIN', student.nin_number || '-');
    setElementText('viewCity', student.city || '-');
    setElementText('viewState', student.state || '-');
    setElementText('viewPermanentAddress', student.permanent_address || '-');
    setElementText('viewFutureAmbition', student.future_ambition || '-');

    // Guardian Details
    setElementText('viewFatherName', student.father_name || '-');
    setElementText('viewMotherName', student.mother_name || '-');
    setElementText('viewOccupation', student.father_occupation || '-');
    setElementText('viewParentCity', student.father_city || '-');

    const parentMobileElement = document.getElementById('viewParentMobile');
    if (parentMobileElement) {
        const parentPhone = student.father_phone || student.mother_phone || '-';
        parentMobileElement.innerHTML = parentPhone !== '-' ?
            `<i class="fas fa-phone"></i> ${parentPhone}` :
            '<i class="fas fa-phone"></i> -';
    }

    const parentEmailElement = document.getElementById('viewParentEmail');
    if (parentEmailElement) {
        const parentEmail = student.parent_email || '-';
        parentEmailElement.innerHTML = parentEmail !== '-' ?
            `<i class="fas fa-envelope"></i> ${parentEmail}` :
            '<i class="fas fa-envelope"></i> -';
    }

    setElementText('viewParentAddress', student.parent_address || '-');

    // Previous School Details
    const schoolElement = document.getElementById('viewSchoolName');
    if (schoolElement) {
        const schoolName = student.last_school || '-';
        schoolElement.innerHTML = schoolName !== '-' ?
            `<i class="fas fa-school"></i> ${schoolName}` :
            '<i class="fas fa-school"></i> -';
    }

    setElementText('viewPreviousClass', student.last_class || '-');
    setElementText('viewReasonLeaving', student.reason_for_leaving || '-');
}

// Helper function to set element text
function setElementText(id, text) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = text;
    } else {
        console.warn(`Element with ID '${id}' not found`);
    }
}

function editStudent(id) {
    console.log('Edit student:', id);
    if (!ensureAxios()) return;

    axios.get(`/student/${id}/edit`)
        .then((response) => {
            console.log('Student data received for edit:', response.data);
            let student = response.data.student || response.data;

            if (!student) {
                throw new Error('Student data is empty');
            }

            // Populate the edit form
            populateEditForm(student);

            // Show the edit modal
            const editModalElement = document.getElementById('editStudentModal');
            if (editModalElement) {
                const editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
            }
        })
        .catch((error) => {
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
            axios.delete(`/student/${id}/destroy`)
                .then(() => {
                    // Remove the card
                    const card = document.querySelector(`.student-card[data-id="${id}"]`);
                    if (card) {
                        card.closest('.col-xl-3').remove();
                    }
                    // Remove the table row
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.remove();
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
                })
                .catch((error) => {
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

// Fetch students from the server - UPDATED to get relationships
function fetchStudents() {
    if (!ensureAxios()) return;
    console.log('Fetching students from /students/data');

    axios.get('/students/data')
        .then((response) => {
            console.log('Full API response:', response.data);

            let studentsArray = [];

            // Handle different response formats
            if (Array.isArray(response.data)) {
                studentsArray = response.data;
            } else if (response.data.students && Array.isArray(response.data.students)) {
                studentsArray = response.data.students;
            } else if (response.data.data && Array.isArray(response.data.data)) {
                studentsArray = response.data.data;
            } else if (response.data.success && Array.isArray(response.data.data)) {
                studentsArray = response.data.data;
            } else {
                console.log('Unexpected response format, trying to extract students:', response.data);
                // Try to extract students from the response
                studentsArray = Object.values(response.data).filter(item =>
                    item && (item.id || item.student_id)
                );
            }

            console.log('Students array:', studentsArray);

            if (studentsArray.length > 0) {
                console.log('First student data:', studentsArray[0]);
            }

            allStudents = studentsArray.map(student => ({
                id: student.id || student.student_id || '',
                admissionNo: student.admissionNo || student.admission_no || student.admission_number || '',
                firstname: student.firstname || student.first_name || '',
                lastname: student.lastname || student.last_name || '',
                othername: student.othername || student.other_name || student.middle_name || '',
                gender: student.gender || '',
                statusId: student.statusId || student.status_id || student.student_status_id || '',
                student_status: student.student_status || student.status || '',
                created_at: student.created_at || student.created_date || student.registration_date || '',
                picture: student.picture || student.avatar || student.profile_picture || '',
                // Get class information from relationships
                schoolclass: student.schoolclass || student.class || student.class_name ||
                           (student.schoolclass_id && student.schoolclass_id.schoolclass) ||
                           (student.schoolclass && student.schoolclass.schoolclass) || '',
                arm: student.arm || student.section ||
                    (student.schoolclass_id && student.schoolclass_id.arm) ||
                    (student.schoolclass && student.schoolclass.arm) || '',
                class_name: student.class_name ||
                           (student.schoolclass && student.schoolclass.schoolclass) || '',
                schoolclassid: student.schoolclassid || student.class_id ||
                             (student.schoolclass_id && student.schoolclass_id.id) || ''
            }));

            console.log('Processed students:', allStudents);
            console.log('Processed students count:', allStudents.length);

            // Update counts
            updateCounts(allStudents.length);

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
                text: "Failed to load students. Please try again.",
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
    console.log('Rendering students in table:', students);
    const tbody = document.getElementById('studentTableBody');
    if (!tbody) {
        console.error('studentTableBody element not found');
        return;
    }

    tbody.innerHTML = '';

    if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="8" class="text-center">No students found</td>`;
        tbody.appendChild(row);
        updatePagination();
        return;
    }

    students.forEach(student => {
        const studentImage = student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar;

        // Get class name for display
        const className = student.class_name || student.schoolclass || 'N/A';
        const classArm = student.arm || '';
        const displayClassName = className + (classArm ? ' - ' + classArm : '');

        const row = document.createElement('tr');
        row.setAttribute('data-id', student.id);
        row.innerHTML = `
            <td class="id" data-id="${student.id}">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="chk_child">
                </div>
            </td>
            <td class="name" data-name="${student.lastname} ${student.firstname} ${student.othername || ''}">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-3">
                        <img src="${studentImage}" alt="" class="rounded-circle avatar-sm student-image" style="object-fit:cover; width: 50px; height: 50px;"/>
                    </div>
                    <div>
                        <h6 class="mb-0">
                            <b>${student.lastname}</b> ${student.firstname} ${student.othername || ''}
                        </h6>
                    </div>
                </div>
            </td>
            <td class="admissionNo" data-admissionno="${student.admissionNo}">${student.admissionNo}</td>
            <td class="class" data-class="${student.schoolclassid}">${displayClassName}</td>
            <td class="status" data-status="${student.statusId}">${student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : ''}</td>
            <td class="gender" data-gender="${student.gender}">${student.gender}</td>
            <td class="datereg">${student.created_at ? new Date(student.created_at).toISOString().split('T')[0] : ''}</td>
            <td>
                <ul class="d-flex gap-2 list-unstyled mb-0">
                    <li><a href="javascript:void(0);" class="btn btn-subtle-info btn-icon btn-sm view-item-btn" data-id="${student.id}" onclick="viewStudent(${student.id})" title="View Details"><i class="ph-eye"></i></a></li>
                    <li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="${student.id}" onclick="editStudent(${student.id})" title="Edit"><i class="ph-pencil"></i></a></li>
                    <li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="${student.id}" onclick="deleteStudent(${student.id})" title="Delete"><i class="ph-trash"></i></a></li>
                </ul>
            </td>
        `;
        tbody.appendChild(row);
    });

    updatePagination();
    initializeCheckboxes();
}

// ... (rest of the functions remain the same - updatePagination, filterData, deleteMultiple, initializeCheckboxes, populateEditForm, showage, initializeStudentList) ...

// Initialize the student list
function initializeStudentList() {
    console.log('Initializing student list...');

    // Initial fetch of students
    fetchStudents();

    // Initialize view toggle
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');

    if (tableViewBtn) {
        tableViewBtn.addEventListener('click', () => toggleView('table'));
    }

    if (cardViewBtn) {
        cardViewBtn.addEventListener('click', () => toggleView('card'));
    }

    // Filter event listeners
    const searchInput = document.querySelector('#search-input');
    const schoolClassFilter = document.getElementById('schoolclass-filter');
    const statusFilter = document.getElementById('status-filter');
    const genderFilter = document.getElementById('gender-filter');

    if (searchInput) {
        searchInput.addEventListener('input', filterData);
    }

    if (schoolClassFilter) {
        schoolClassFilter.addEventListener('change', filterData);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterData);
    }

    if (genderFilter) {
        genderFilter.addEventListener('change', filterData);
    }
}

// Call initializeStudentList on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing student list...');
    initializeStudentList();
});
</script>
@endsection
