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

                /* Progress Steps */
                .progress-steps {
                    display: flex;
                    justify-content: space-between;
                    position: relative;
                    margin-bottom: 30px;
                    counter-reset: step;
                }

                .progress-steps::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    height: 2px;
                    background: #e9ecef;
                    transform: translateY(-50%);
                    z-index: 1;
                }

                .progress-steps .step {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: #e9ecef;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    color: #6c757d;
                    position: relative;
                    z-index: 2;
                    border: 2px solid #e9ecef;
                }

                .progress-steps .step.active {
                    background: #405189;
                    color: white;
                    border-color: #405189;
                }

                /* Modern Modal Styles */
                .modern-modal {
                    border-radius: 12px;
                    overflow: hidden;
                }

                .modern-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px 30px;
                    border: none;
                }

                .modern-close {
                    background: rgba(255,255,255,0.1);
                    border-radius: 50%;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    border: none;
                    opacity: 1;
                }

                .modern-close:hover {
                    background: rgba(255,255,255,0.2);
                }

                .modern-body {
                    padding: 0;
                }

                /* Student header with photo */
                .student-header {
                    background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
                    padding: 30px;
                    text-align: center;
                    border-bottom: 1px solid #e9ecef;
                }

                .photo-container {
                    display: inline-block;
                    position: relative;
                }

                .photo-frame {
                    width: 150px;
                    height: 150px;
                    border-radius: 50%;
                    overflow: hidden;
                    border: 5px solid white;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    position: relative;
                }

                .student-photo {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                /* Form sections */
                .form-section {
                    padding: 20px 30px;
                    border-bottom: 1px solid #e9ecef;
                }

                .section-header {
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #f0f0f0;
                }

                .section-header h5 {
                    color: #495057;
                    font-weight: 600;
                }

                /* Form grid for better layout */
                .form-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                    gap: 20px;
                }

                .form-group {
                    margin-bottom: 15px;
                }

                .form-label {
                    font-size: 12px;
                    color: #6c757d;
                    text-transform: uppercase;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                    margin-bottom: 5px;
                    display: block;
                }

                .form-value {
                    padding: 8px 12px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    font-weight: 500;
                    color: #495057;
                    min-height: 38px;
                    display: flex;
                    align-items: center;
                    border: 1px solid #e9ecef;
                }

                /* Special value styles */
                .highlight {
                    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
                    border: 1px solid #667eea30;
                    color: #405189;
                    font-weight: 600;
                }

                .class-badge {
                    display: inline-block;
                    padding: 4px 12px;
                    background: #e7f4ff;
                    color: #0066cc;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                }

                /* Modern tabs */
                .modern-tabs {
                    background: #f8f9fa;
                    padding: 10px;
                    border-radius: 10px;
                    margin: 0 30px;
                    position: relative;
                    top: -15px;
                    border: 1px solid #e9ecef;
                }

                .modern-tabs .nav-link {
                    color: #6c757d;
                    padding: 12px 20px;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                    border: none;
                    position: relative;
                }

                .modern-tabs .nav-link.active {
                    background: white;
                    color: #405189;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                }

                .modern-tabs .nav-link i {
                    margin-right: 8px;
                    font-size: 16px;
                }

                /* Modern footer */
                .modern-footer {
                    background: #f8f9fa;
                    border-top: 1px solid #e9ecef;
                    padding: 15px 30px;
                }

                /* Full-width form groups */
                .full-width {
                    grid-column: 1 / -1;
                }

                .address-field {
                    min-height: 60px;
                    white-space: pre-wrap;
                }

                /* Status badges */
                .gender-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }

                .blood-group {
                    background: #fff5f5;
                    color: #e53e3e;
                    border: 1px solid #fed7d7;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-weight: 600;
                    font-size: 12px;
                }

                .occupation-badge {
                    background: #f0fff4;
                    color: #38a169;
                    border: 1px solid #c6f6d5;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                }

                /* Contact info styling */
                .contact {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .school-name {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .category-badges {
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                }

                .category-badge {
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    opacity: 0.5;
                    transition: all 0.3s ease;
                }

                .category-badge.active {
                    opacity: 1;
                }

                .category-badge.day {
                    background: #fff3cd;
                    color: #856404;
                    border: 1px solid #ffeaa7;
                }

                .category-badge.boarding {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .name-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 15px;
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
                                                    <select class="form-control" id="admissionYear" name="admissionYear" required onchange="updateAdmissionNumber()">
                                                        @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                    <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="CSSK/STD/YYYY/001" required>
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
                                <!-- Personal Details -->
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
                            <!-- Additional Information, Parent/Guardian Details, and Previous School Details -->
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
        <!-- Edit Student Modal -->
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

                            <!-- Progress Steps - Fixed: No active steps by default -->
                            <div class="progress-steps mb-4">
                                <div class="step">1</div>
                                <div class="step">2</div>
                                <div class="step">3</div>
                                <div class="step">4</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                   <!-- Academic Details section -->
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
                                <!-- Personal Details -->
                                <div class="col-md-6">
                                 <!-- Personal Details section -->
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
                            <!-- Additional Information section -->
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
                                        <img id="viewStudentPhoto" src="https://via.placeholder.com/150x150/6366f1/ffffff?text=PHOTO" alt="Student Photo" class="student-photo">
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
// ============================================================================
// FIXED VERSION - Student Management JavaScript
// ============================================================================

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
    const baseFormat = `TCC/${year}/`;

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
    const baseFormat = `TCC/${year}/`;

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

// FIXED: Generate placeholder image as data URL to avoid network issues
function generatePlaceholderImage(text = 'PHOTO') {
    const canvas = document.createElement('canvas');
    canvas.width = 150;
    canvas.height = 150;
    const ctx = canvas.getContext('2d');

    // Background gradient
    const gradient = ctx.createLinearGradient(0, 0, 150, 150);
    gradient.addColorStop(0, '#6366f1');
    gradient.addColorStop(1, '#8b5cf6');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 150, 150);

    // Text
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 24px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 75, 75);

    return canvas.toDataURL();
}

// FIXED: Get initials properly
function getStudentInitials(firstName, lastName) {
    const firstInitial = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
    const lastInitial = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
    return (firstInitial + lastInitial) || '??';
}

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

        document.getElementById('checkAll').checked = false;
        document.getElementById('remove-actions').classList.add('d-none');
    } else {
        tableView.classList.add('d-none');
        cardView.classList.remove('d-none');
        tableViewBtn.classList.remove('active');
        cardViewBtn.classList.add('active');

        if (document.getElementById('studentsCardsContainer').children.length === 0 && allStudents.length > 0) {
            renderStudentsCards(allStudents);
        }

        document.getElementById('checkAll').checked = false;
        document.getElementById('remove-actions').classList.add('d-none');
    }
}

// FIXED: Render students as cards
function renderStudentsCards(students) {
    console.log('Rendering students as cards:', students);
    const container = document.getElementById('studentsCardsContainer');
    if (!container) {
        console.error('studentsCardsContainer element not found');
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
        // FIXED: Get proper initials
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

        // FIXED: Get avatar URL with proper fallback
        let avatarUrl = defaultAvatar;
        if (student.picture && student.picture !== 'unnamed.jpg') {
            avatarUrl = `/storage/images/student_avatars/${student.picture}`;
        }

        const isActive = student.student_status === 'Active';
        const statusText = isActive ? 'Active' : 'Inactive';
        const statusClass = isActive ? 'status-active' : 'status-inactive';
        const studentType = student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : 'N/A';

        const regDate = student.created_at ? new Date(student.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : 'N/A';

        const cardHtml = `
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="student-card" data-id="${student.id}"
                     data-name="${student.lastname || ''} ${student.firstname || ''} ${student.othername || ''}"
                     data-admission="${student.admissionNo || ''}"
                     data-class="${student.schoolclassid || ''}"
                     data-status="${student.statusId || ''}"
                     data-gender="${student.gender || ''}"
                     data-student-status="${student.student_status || ''}">

                    <div class="checkbox-container">
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox" name="chk_child" value="${student.id}">
                        </div>
                    </div>

                    <span class="status-badge ${statusClass}">${statusText}</span>

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

                    <div class="avatar-container">
                        <div class="avatar-initials" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            ${displayInitials}
                        </div>
                        <img src="${avatarUrl}" alt="${student.firstname || ''} ${student.lastname || ''}"
                             class="avatar" style="position: absolute; top: 0; left: 0; display: none;"
                             onload="this.style.display='block'; this.previousElementSibling.style.display='none';"
                             onerror="this.style.display='none'; this.previousElementSibling.style.display='flex';">
                    </div>

                    <h6 class="student-name">${student.lastname || ''} ${student.firstname || ''}</h6>
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

// FIXED: View student details
function viewStudent(id) {
    console.log('View student:', id);
    if (!ensureAxios()) return;

    Swal.fire({
        title: 'Loading...',
        text: 'Fetching student details',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    axios.get(`/student/${id}/edit`)
        .then((response) => {
            Swal.close();
            console.log('Student data received for view:', response.data);
            let student = response.data.student || response.data;

            if (!student) {
                throw new Error('Student data is empty');
            }

            populateViewModal(student);

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
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Failed to load student data. Please try again.',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });
}

// FIXED: Function to populate view modal
function populateViewModal(student) {
    console.log('=== DEBUG: Populating View Modal ===');
    console.log('Student object:', student);

    // FIXED: Student Photo with proper fallback
    const photoElement = document.getElementById('viewStudentPhoto');
    if (photoElement) {
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

        if (student.picture && student.picture !== 'unnamed.jpg') {
            photoElement.src = `/storage/images/student_avatars/${student.picture}`;
            photoElement.style.display = 'block';
            photoElement.onerror = function() {
                // Fallback to initials
                this.style.display = 'none';
                const overlay = this.nextElementSibling;
                if (overlay) {
                    overlay.innerHTML = `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; font-size: 48px; font-weight: bold; color: white;">${displayInitials}</div>`;
                }
            };
        } else {
            // Show initials placeholder
            photoElement.style.display = 'none';
            const overlay = photoElement.nextElementSibling;
            if (overlay) {
                overlay.innerHTML = `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; font-size: 48px; font-weight: bold; color: white;">${displayInitials}</div>`;
            }
        }
    }

    // FIXED: Academic Details with null checks
    setElementText('viewAcademicYear', student.admissionYear || student.admission_year || '-');
    setElementText('viewRegistrationNo', student.admissionNo || student.admission_no || '-');

    if (student.admissionDate || student.admission_date) {
        const dateValue = student.admissionDate || student.admission_date;
        const date = new Date(dateValue);
        setElementText('viewAdmissionDate', date.toLocaleDateString());
    } else {
        setElementText('viewAdmissionDate', '-');
    }

    // FIXED: Class, Term, Session display
    const classText = student.schoolclass || '';
    const armText = student.arm || '';
    setElementText('viewClass', classText && armText ? `${classText} - ${armText}` : classText || armText || '-');

    setElementText('viewTerm', student.term_name || student.term || '-');
    setElementText('viewSession', student.session_name || student.session || '-');

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
        element.textContent = text || '-';
    } else {
        console.warn(`Element with ID '${id}' not found`);
    }
}

// FIXED: Edit student function
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

            populateEditForm(student);

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

// FIXED: Populate edit form
function populateEditForm(student) {
    console.log('Populating edit form with student:', student);

    const fields = [
        { id: 'editStudentId', value: student.id },
        { id: 'editAdmissionNo', value: student.admissionNo || student.admission_no || '' },
        { id: 'editAdmissionYear', value: student.admissionYear || '' },
        { id: 'editAdmissionDate', value: student.admissionDate ? student.admissionDate.split('T')[0] : '' },
        { id: 'editTitle', value: student.title || '' },
        { id: 'editFirstname', value: student.firstname || student.first_name || '' },
        { id: 'editLastname', value: student.lastname || student.last_name || '' },
        { id: 'editOthername', value: student.othername || student.other_name || student.middle_name || '' },
        { id: 'editPermanentAddress', value: student.permanent_address || '' },
        { id: 'editDOB', value: student.dateofbirth ? student.dateofbirth.split('T')[0] : '' },
        { id: 'editPlaceofbirth', value: student.placeofbirth || '' },
        { id: 'editNationality', value: student.nationality || '' },
        { id: 'editReligion', value: student.religion || '' },
        { id: 'editLastSchool', value: student.last_school || '' },
        { id: 'editLastClass', value: student.last_class || '' },
        { id: 'editSchoolclassid', value: student.schoolclassid || student.class_id || '' },
        { id: 'editTermid', value: student.termid || student.term_id || '' },
        { id: 'editSessionid', value: student.sessionid || student.session_id || '' },
        { id: 'editPhoneNumber', value: student.phone_number || student.phone || '' },
        { id: 'editEmail', value: student.email || '' },
        { id: 'editFutureAmbition', value: student.future_ambition || '' },
        { id: 'editCity', value: student.city || '' },
        { id: 'editState', value: student.state || '' },
        { id: 'editLocal', value: student.local || '' },
        { id: 'editNinNumber', value: student.nin_number || student.nin || '' },
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
        { id: 'editSchoolHouse', value: student.schoolhouse || student.school_house || student.sport_house || '' },
        { id: 'editReasonForLeaving', value: student.reason_for_leaving || '' }
    ];

    fields.forEach(({ id, value }) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            console.log(`Set ${id} to:`, value);
        } else {
            console.warn(`Element with ID '${id}' not found`);
        }
    });

    // Set gender
    const genderRadios = document.querySelectorAll('#editStudentModal input[name="gender"]');
    if (genderRadios.length > 0) {
        const studentGender = student.gender || '';
        genderRadios.forEach(radio => {
            radio.checked = (radio.value === studentGender);
        });
        console.log('Set gender to:', studentGender);
    }
    // Set status
    const statusRadios = document.querySelectorAll('#editStudentModal input[name="statusId"]');
    if (statusRadios.length > 0) {
        const studentStatusId = student.statusId || student.status_id || '';
        statusRadios.forEach(radio => {
            radio.checked = (parseInt(radio.value) === parseInt(studentStatusId));
        });
        console.log('Set statusId to:', studentStatusId);
    }

    // Set student activity status
    const studentStatusRadios = document.querySelectorAll('#editStudentModal input[name="student_status"]');
    if (studentStatusRadios.length > 0) {
        const studentActivityStatus = student.student_status || student.status || '';
        studentStatusRadios.forEach(radio => {
            radio.checked = (radio.value === studentActivityStatus);
        });
        console.log('Set student_status to:', studentActivityStatus);
    }

    // FIXED: Set avatar with initials fallback
    const avatarElement = document.getElementById('editStudentAvatar');
    if (avatarElement) {
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

        if (student.picture && student.picture !== 'unnamed.jpg') {
            const avatarUrl = `/storage/images/student_avatars/${student.picture}`;
            avatarElement.src = avatarUrl;
            avatarElement.style.display = 'block';
            avatarElement.onerror = function() {
                // Create initials overlay
                this.style.display = 'none';
                const container = this.parentElement;
                if (container) {
                    const initialsDiv = document.createElement('div');
                    initialsDiv.className = 'avatar-initials';
                    initialsDiv.style.cssText = 'width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; color: white; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 4px solid #667eea; box-shadow: 0 4px 8px rgba(0,0,0,0.1);';
                    initialsDiv.textContent = displayInitials;
                    container.appendChild(initialsDiv);
                }
            };
        } else {
            // Show initials
            avatarElement.style.display = 'none';
            const container = avatarElement.parentElement;
            if (container) {
                let initialsDiv = container.querySelector('.avatar-initials');
                if (!initialsDiv) {
                    initialsDiv = document.createElement('div');
                    initialsDiv.className = 'avatar-initials';
                    initialsDiv.style.cssText = 'width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; color: white; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 4px solid #667eea; box-shadow: 0 4px 8px rgba(0,0,0,0.1);';
                    container.appendChild(initialsDiv);
                }
                initialsDiv.textContent = displayInitials;
            }
        }
    }

    // Calculate age if date of birth exists
    if (student.dateofbirth) {
        calculateAge(student.dateofbirth, 'editAgeInput');
    }

    // Update form action
    const form = document.getElementById('editStudentForm');
    if (form && student.id) {
        form.action = `/student/${student.id}`;
        console.log('Updated form action to:', form.action);
    }
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
                    const card = document.querySelector(`.student-card[data-id="${id}"]`);
                    if (card) {
                        card.closest('.col-xl-3').remove();
                    }
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.remove();
                    }
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

// Fetch students from the server
function fetchStudents() {
    if (!ensureAxios()) return;
    console.log('Fetching students from /students/data');

    axios.get('/students/data')
        .then((response) => {
            console.log('Full API response:', response.data);

            let studentsArray = [];

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
                schoolclass: student.schoolclass || student.class || student.class_name || '',
                arm: student.arm || student.section || '',
                schoolclassid: student.schoolclassid || student.class_id || ''
            }));

            console.log('Processed students:', allStudents);
            console.log('Processed students count:', allStudents.length);

            updateCounts(allStudents.length);

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
        const studentImage = student.picture && student.picture !== 'unnamed.jpg' ?
            `/storage/images/student_avatars/${student.picture}` : defaultAvatar;

        const row = document.createElement('tr');
        row.setAttribute('data-id', student.id);
        row.innerHTML = `
            <td class="id" data-id="${student.id}">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="chk_child">
                </div>
            </td>
            <td class="name" data-name="${student.lastname || ''} ${student.firstname || ''} ${student.othername || ''}">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-3">
                        <img src="${studentImage}" alt="" class="rounded-circle avatar-sm student-image" style="object-fit:cover; width: 50px; height: 50px;"/>
                    </div>
                    <div>
                        <h6 class="mb-0">
                            <b>${student.lastname || ''}</b> ${student.firstname || ''} ${student.othername || ''}
                        </h6>
                    </div>
                </div>
            </td>
            <td class="admissionNo" data-admissionno="${student.admissionNo || ''}">${student.admissionNo || ''}</td>
            <td class="class" data-class="${student.schoolclassid || ''}">${student.schoolclass || ''} ${student.arm ? ' - ' + student.arm : ''}</td>
            <td class="status" data-status="${student.statusId || ''}">${student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : ''}</td>
            <td class="gender" data-gender="${student.gender || ''}">${student.gender || ''}</td>
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

// FIXED: Calculate age function
window.calculateAge = function(dateValue, targetId) {
    if (!dateValue) return;

    const dateString = dateValue.includes('T') ? dateValue.split('T')[0] : dateValue;
    const dob = new Date(dateString);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    const ageInput = document.getElementById(targetId);
    if (ageInput) {
        ageInput.value = age;
    }
};

// Update pagination controls
function updatePagination() {
    const totalItems = allStudents.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const currentPage = 1;
    const paginationLinks = document.getElementById('paginationLinks');

    if (!paginationLinks) return;

    paginationLinks.innerHTML = '';

    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
        li.addEventListener('click', () => {
            const startIndex = (i - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageStudents = allStudents.slice(startIndex, endIndex);

            const tableView = document.getElementById('tableView');
            const isTableView = !tableView.classList.contains('d-none');

            if (isTableView) {
                renderStudents(pageStudents);
            } else {
                renderStudentsCards(pageStudents);
            }

            document.getElementById('showingCount').textContent = pageStudents.length;
        });
        paginationLinks.appendChild(li);
    }

    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');

    if (prevPage) {
        prevPage.classList.toggle('disabled', currentPage === 1);
    }

    if (nextPage) {
        nextPage.classList.toggle('disabled', currentPage === totalPages);
    }
}

// Filter function for both views
function filterData() {
    const search = document.querySelector('#search-input')?.value.toLowerCase() || '';
    const classId = document.getElementById('schoolclass-filter')?.value || 'all';
    const statusId = document.getElementById('status-filter')?.value || 'all';
    const gender = document.getElementById('gender-filter')?.value || 'all';

    console.log('Filtering with:', { search, classId, statusId, gender });

    const filteredStudents = allStudents.filter(student => {
        const name = `${student.lastname || ''} ${student.firstname || ''} ${student.othername || ''}`.toLowerCase();
        const admissionNo = (student.admissionNo || '').toLowerCase();

        const matchesSearch = name.includes(search) || admissionNo.includes(search);
        const matchesClass = classId === 'all' || student.schoolclassid == classId;
        const matchesStatus = statusId === 'all' || student.statusId == statusId;
        const matchesGender = gender === 'all' || student.gender === gender;

        return matchesSearch && matchesClass && matchesStatus && matchesGender;
    });

    const tableView = document.getElementById('tableView');
    const isTableView = !tableView.classList.contains('d-none');

    if (isTableView) {
        renderStudents(filteredStudents);
    } else {
        renderStudentsCards(filteredStudents);
    }

    document.getElementById('showingCount').textContent = filteredStudents.length;
}

// Delete multiple students
function deleteMultiple() {
    const tableView = document.getElementById('tableView');
    const isTableView = !tableView.classList.contains('d-none');

    let ids = [];

    if (isTableView) {
        ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
            .map(checkbox => {
                const row = checkbox.closest('tr');
                return row ? row.getAttribute('data-id') : null;
            })
            .filter(id => id !== null);
    } else {
        ids = Array.from(document.querySelectorAll('.student-checkbox:checked'))
            .map(checkbox => checkbox.value)
            .filter(id => id !== null);
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
            const deletePromises = ids.map(id =>
                axios.delete(`/student/${id}/destroy`)
            );

            Promise.all(deletePromises)
                .then(() => {
                    fetchStudents();

                    Swal.fire({
                        title: "Deleted!",
                        text: `Successfully deleted ${ids.length} student(s)`,
                        icon: "success",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
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

// Initialize checkboxes for multiple selection
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

// Initialize the student list
function initializeStudentList() {
    console.log('Initializing student list...');

    fetchStudents();

    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');

    if (tableViewBtn) {
        tableViewBtn.addEventListener('click', () => toggleView('table'));
    }

    if (cardViewBtn) {
        cardViewBtn.addEventListener('click', () => toggleView('card'));
    }

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
