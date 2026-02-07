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

                                      <!-- Print/Export Report Button -->
                                    <button type="button" class="btn btn-soft-success" data-bs-toggle="modal" data-bs-target="#printStudentReportModal">
                                        <i class="ri-printer-line align-bottom me-1"></i> Print / Export Report
                                    </button>
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


    <!-- ================================================= -->
    <!--        PRINT / EXPORT REPORT MODAL               -->
    <!-- ================================================= -->
<!-- Print/Export Report Modal -->
<div class="modal fade" id="printStudentReportModal" tabindex="-1" aria-labelledby="printStudentReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-soft-success">
                <h5 class="modal-title" id="printStudentReportModalLabel">
                    <i class="ri-printer-line me-2"></i> Generate Student Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="printReportForm">
                    <!-- Filters Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id">
                                <option value="">— All Classes —</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">— All —</option>
                                <option value="1">Old Students</option>
                                <option value="2">New Students</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Term and Session Filters -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Term</label>
                            <select class="form-select" name="term_id">
                                <option value="">— All Terms —</option>
                                @foreach ($schoolterms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Session</label>
                            <select class="form-select" name="session_id">
                                <option value="">— All Sessions —</option>
                                @foreach ($schoolsessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Column Selection with Drag & Drop -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="ri-draggable me-1"></i> Select & Arrange Columns (Drag to reorder)
                        </label>
                        <div class="row g-3" id="columnsContainer">
                            <input type="hidden" name="columns_order" id="columnsOrderInput" value="">
                            @php
                                $availableColumns = [
                                    'photo'          => 'Photo',
                                    'admissionNo'    => 'Admission No',
                                    'lastname'       => 'Last Name',
                                    'firstname'      => 'First Name',
                                    'othername'      => 'Other Name',
                                    'gender'         => 'Gender',
                                    'dateofbirth'    => 'Date of Birth',
                                    'age'            => 'Age',
                                    'class'          => 'Class / Arm',
                                    'status'         => 'Student Status',
                                    'admission_date' => 'Admission Date',
                                    'phone_number'   => 'Phone Number',
                                    'state'          => 'State of Origin',
                                    'local'          => 'LGA',
                                    'religion'       => 'Religion',
                                    'blood_group'    => 'Blood Group',
                                    'father_name'    => "Father's Name",
                                    'mother_name'    => "Mother's Name",
                                    'guardian_phone' => 'Guardian Phone',
                                    'term'           => 'Term',
                                    'session'        => 'Session',
                                ];
                            @endphp
                            @foreach ($availableColumns as $key => $label)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check border rounded p-2 mb-2 bg-light draggable-item" data-column="{{ $key }}">
                                        <div class="d-flex align-items-center">
                                            <span class="drag-handle me-2 cursor-move">
                                                <i class="ri-draggable"></i>
                                            </span>
                                            <input class="form-check-input column-checkbox" type="checkbox" name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
                                                {{ in_array($key, ['admissionNo','lastname','firstname','class','gender']) ? 'checked' : '' }}>
                                            <label class="form-check-label w-100 cursor-move" for="col_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Drag columns to arrange their order in the report</small>
                    </div>

                    <!-- Report Header Options -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ri-file-info-line me-2"></i> Report Header Options</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" name="include_header" id="includeHeader" checked>
                                        <label class="form-check-label" for="includeHeader">
                                            <i class="ri-building-line me-1"></i> Include School Header
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" name="include_logo" id="includeLogo" checked>
                                        <label class="form-check-label" for="includeLogo">
                                            <i class="ri-image-line me-1"></i> Include School Logo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="orientation" class="form-label">Page Orientation</label>
                                        <select class="form-select" name="orientation" id="orientation">
                                            <option value="portrait">Portrait</option>
                                            <option value="landscape">Landscape</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Format -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Export Format</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_pdf" value="pdf" checked>
                                <label class="form-check-label" for="format_pdf">
                                    <i class="ri-file-pdf-2-line text-danger me-1"></i> PDF
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_excel" value="excel">
                                <label class="form-check-label" for="format_excel">
                                    <i class="ri-file-excel-2-line text-success me-1"></i> Excel
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="alert alert-info small mb-0">
                        <div class="d-flex align-items-center">
                            <i class="ri-information-fill me-2"></i>
                            <div>
                                <strong>Preview:</strong>
                                <span id="columnOrderPreview">admissionNo, lastname, firstname, class, gender</span>
                                <br>
                                <small>Only students matching the selected filters will be included.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="generateReportBtn">
                    <i class="ri-printer-line me-1"></i> Generate & Download
                </button>
                <!-- Debug button - remove in production -->
                <button type="button" class="btn btn-warning btn-sm d-none" onclick="debugColumnOrdering()" id="debugBtn">
                    <i class="ri-bug-line me-1"></i> Debug
                </button>
            </div>
        </div>
    </div>
</div>


<style>
  /* Drag and Drop Styles */
.cursor-move {
    cursor: move !important;
}

.drag-handle {
    cursor: move;
    opacity: 0.5;
    transition: opacity 0.2s;
    display: inline-flex;
    align-items: center;
}

.drag-handle:hover {
    opacity: 1;
}

.draggable-item {
    user-select: none;
    transition: all 0.3s ease;
    position: relative;
}

.draggable-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
    background-color: #f8f9fa !important;
}

.draggable-item.drag-over {
    background-color: #e9ecef !important;
    border-color: #405189 !important;
}

/* Sortable.js specific classes */
.sortable-ghost {
    opacity: 0.4;
    background-color: #f8f9fa !important;
    transform: rotate(2deg);
}

.sortable-chosen {
    background-color: #405189 !important;
    color: white !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transform: scale(1.02);
    z-index: 1000;
}

.sortable-chosen .form-check-label {
    color: white !important;
}

.sortable-chosen .drag-handle {
    color: white !important;
}

.sortable-drag {
    opacity: 0.8;
}

#columnsContainer {
    min-height: 200px;
}

/* Preview styling */
#columnOrderPreview {
    font-weight: 500;
    color: #405189;
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
    margin-top: 2px;
}
</style>

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
                                                    <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                                    <small class="form-text text-muted w-100 mt-1">Format: TCC/YYYY/0001 (e.g., TCC/2024/0871)</small>
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
                                                <label for="othername" class="form-label">Other Names<span class="text-danger">*</span></label>
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
                                                        <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value, 'addAgeInput')">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="addAgeInput" class="form-label">Age <span class="text-danger">*</span></label>
                                                        <input type="number" id="addAgeInput" name="age" class="form-control" readonly required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="placeofbirth" class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </span>
                                                    <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" placeholder="e.g., Lagos, Nigeria" required>
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
                                                        <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                                        <input type="text" id="nationality" name="nationality" class="form-control" placeholder="e.g., Nigerian" required>
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
                                                        <label for="school_house" class="form-label">School House <span class="text-danger">*</span></label>
                                                        <select id="school_house" name="schoolhouseid" class="form-control" required>
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
                                                    <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                                    <small class="form-text text-muted w-100 mt-1">Format: TCC/YYYY/0001 (e.g., TCC/2024/0871)</small>
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
                                            <input type="text" id="editOthername" name="othername" class="form-control" placeholder="Middle name(s)" required>
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
                                                    <label for="editAgeInput" class="form-label">Age <span class="text-danger">*</span></label>
                                                    <input type="number" id="editAgeInput" name="age" class="form-control" readonly required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editPlaceofbirth" class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" placeholder="e.g., Lagos, Nigeria" required>
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
                                                    <label for="editNationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                                    <input type="text" id="editNationality" name="nationality" class="form-control" placeholder="e.g., Nigerian" required>
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
                                                    <label for="editLocal" class="form-label">Local Government <span class="text-danger">*</span></label>
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
                                                    <label for="editSchoolHouse" class="form-label">School House <span class="text-danger">*</span></label>
                                                    <select id="editSchoolHouse" name="schoolhouseid" class="form-control" required>
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
                                                <label for="editLastSchool" class="form-label">Last School Attended<span class="text-danger">*</span></label>
                                                <input type="text" id="editLastSchool" name="last_school" class="form-control" placeholder="Previous school name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editLastClass" class="form-label">Last Class Attended<span class="text-danger">*</span></label>
                                                <input type="text" id="editLastClass" name="last_class" class="form-control" placeholder="e.g., JSS 2" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editReasonForLeaving" class="form-label">Reason for Leaving<span class="text-danger">*</span></label>
                                                <textarea id="editReasonForLeaving" name="reason_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving previous school" required></textarea>
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
                                                <label class="form-label">State of Origin</label>
                                                <div class="form-value" id="viewState">-</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Local Government</label>
                                                <div class="form-value" id="viewLocal">-</div>
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
// GLOBAL VARIABLES AND INITIALIZATION
// ============================================================================

let allStudents = [];
const itemsPerPage = 100;
const defaultAvatar = '{{ asset("storage/images/student_avatars/unnamed.jpg") }}';
let columnSortable = null;

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

// ============================================================================
// ADMISSION NUMBER FUNCTIONS
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

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

// Get student initials
function getStudentInitials(firstName, lastName) {
    const firstInitial = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
    const lastInitial = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
    return (firstInitial + lastInitial) || '??';
}

// Calculate age
window.calculateAge = function(dateValue, targetId) {
    if (!dateValue) {
        console.error('No date value provided');
        return;
    }

    try {
        const dateString = dateValue.includes('T') ? dateValue.split('T')[0] : dateValue;
        const dob = new Date(dateString);

        if (isNaN(dob.getTime())) {
            console.error('Invalid date:', dateValue);
            return;
        }

        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        const ageInput = document.getElementById(targetId);
        if (ageInput) {
            ageInput.value = age;
        } else {
            console.error('Target element not found:', targetId);
        }
    } catch (error) {
        console.error('Error calculating age:', error);
    }
};

// Preview image
function previewImage(input, targetId = 'addStudentAvatar') {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(targetId);
            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';

                // Remove initials overlay if it exists
                const initialsDiv = img.nextElementSibling;
                if (initialsDiv && initialsDiv.classList.contains('avatar-initials')) {
                    initialsDiv.style.display = 'none';
                }
            }
        };
        reader.readAsDataURL(file);
    }
}

// ============================================================================
// VIEW TOGGLE FUNCTIONS
// ============================================================================

// Toggle between table and card view
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

        // Render table view
        renderStudents(allStudents);
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

// ============================================================================
// STUDENT RENDERING FUNCTIONS
// ============================================================================

// Render students as cards
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
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

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

// Render students in table
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

// Update counts display
function updateCounts(count) {
    const totalStudents = document.getElementById('totalStudents');
    const totalCount = document.getElementById('totalCount');
    const showingCount = document.getElementById('showingCount');

    if (totalStudents) totalStudents.textContent = count;
    if (totalCount) totalCount.textContent = count;
    if (showingCount) showingCount.textContent = count;
}

// ============================================================================
// STUDENT CRUD OPERATIONS
// ============================================================================

// View student details
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

// Edit student
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

// Delete student
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

// ============================================================================
// MODAL POPULATION FUNCTIONS
// ============================================================================

// Populate view modal
function populateViewModal(student) {
    console.log('=== DEBUG: Populating View Modal ===');
    console.log('Student object:', student);

    // Helper function to set element text
    function setElementText(id, text) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = text || '-';
        } else {
            console.warn(`Element with ID '${id}' not found`);
        }
    }

    // Student Photo
    const photoElement = document.getElementById('viewStudentPhoto');
    if (photoElement) {
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

        if (student.picture && student.picture !== 'unnamed.jpg') {
            photoElement.src = `/storage/images/student_avatars/${student.picture}`;
            photoElement.style.display = 'block';
            photoElement.onerror = function() {
                this.style.display = 'none';
                const overlay = this.nextElementSibling;
                if (overlay) {
                    overlay.innerHTML = `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; font-size: 48px; font-weight: bold; color: white;">${displayInitials}</div>`;
                }
            };
        } else {
            photoElement.style.display = 'none';
            const overlay = photoElement.nextElementSibling;
            if (overlay) {
                overlay.innerHTML = `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; font-size: 48px; font-weight: bold; color: white;">${displayInitials}</div>`;
            }
        }
    }

    // Academic Details
    setElementText('viewAcademicYear', student.admissionYear || student.admission_year || '-');
    setElementText('viewRegistrationNo', student.admissionNo || student.admission_no || '-');

    if (student.admissionDate || student.admission_date) {
        const dateValue = student.admissionDate || student.admission_date;
        const date = new Date(dateValue);
        setElementText('viewAdmissionDate', date.toLocaleDateString());
    } else {
        setElementText('viewAdmissionDate', '-');
    }

    const classText = student.schoolclass || '';
    const armText = student.arm || '';
    setElementText('viewClass', classText && armText ? `${classText} - ${armText}` : classText || armText || '-');

    setElementText('viewTerm', student.term_name || student.term || '-');
    setElementText('viewSession', student.session_name || student.session || '-');
    setElementText('viewState', student.state || '-');
    setElementText('viewLocal', student.local || '-');

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

// Populate edit form
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

    // Set avatar
    const avatarElement = document.getElementById('editStudentAvatar');
    if (avatarElement) {
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

        if (student.picture && student.picture !== 'unnamed.jpg') {
            const avatarUrl = `/storage/images/student_avatars/${student.picture}`;
            avatarElement.src = avatarUrl;
            avatarElement.style.display = 'block';
            avatarElement.onerror = function() {
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

// ============================================================================
// FILTER AND SEARCH FUNCTIONS
// ============================================================================

// Filter data
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

// ============================================================================
// CHECKBOX FUNCTIONS
// ============================================================================

// Initialize checkboxes for table view
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

// ============================================================================
// PAGINATION FUNCTIONS
// ============================================================================

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

// ============================================================================
// DATA FETCHING FUNCTIONS
// ============================================================================

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
                schoolclassid: student.schoolclassid || student.class_id || '',
                state: student.state || '',
                local: student.local || ''
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

// ============================================================================
// REPORT MODAL DRAG AND DROP FUNCTIONS
// ============================================================================

// Initialize column ordering
function initializeColumnOrdering() {
    console.log('Initializing column ordering...');

    const columnContainer = document.getElementById('columnsContainer');
    const hiddenOrderInput = document.getElementById('columnsOrderInput');

    if (!columnContainer || !hiddenOrderInput) {
        console.error('Column container or hidden input not found');
        return;
    }

    // Function to update column order
    function updateColumnOrder() {
        console.log('Updating column order...');

        // Get all checked checkboxes in their current DOM order
        const columnItems = columnContainer.querySelectorAll('.draggable-item');
        const order = [];
        const selectedLabels = [];

        columnItems.forEach(item => {
            const checkbox = item.querySelector('.column-checkbox');
            if (checkbox && checkbox.checked) {
                order.push(checkbox.value);

                // Get the label text
                const label = item.querySelector('.form-check-label');
                if (label) {
                    selectedLabels.push(label.textContent.trim());
                }
            }
        });

        console.log('New order:', order);
        console.log('Selected labels:', selectedLabels);

        hiddenOrderInput.value = order.join(',');

        // Update preview
        updatePreview();
    }

    // Check if Sortable.js is loaded
    if (typeof Sortable !== 'undefined') {
        console.log('Sortable.js loaded, version:', Sortable.version);

        // Destroy existing instance if any
        if (columnSortable) {
            columnSortable.destroy();
        }

        // Initialize Sortable.js
        columnSortable = new Sortable(columnContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            filter: '.column-checkbox',
            onStart: function() {
                console.log('Drag started');
            },
            onEnd: function() {
                console.log('Drag ended');
                updateColumnOrder();
            },
            onSort: function() {
                console.log('Items sorted');
            }
        });

        console.log('Sortable.js initialized successfully');
    } else {
        console.error('Sortable.js not loaded!');
        // Fallback to native drag and drop
        initializeNativeDragDrop();
    }

    // Update order when checkboxes change
    columnContainer.querySelectorAll('.column-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('Checkbox changed:', this.value, this.checked);
            updateColumnOrder();
        });
    });

    // Initial update
    updateColumnOrder();
}

// Native drag and drop fallback
function initializeNativeDragDrop() {
    console.log('Initializing native drag and drop...');

    const container = document.getElementById('columnsContainer');
    const draggables = container.querySelectorAll('.draggable-item');

    let draggedItem = null;

    draggables.forEach(item => {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.column);
        });

        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            container.querySelectorAll('.draggable-item').forEach(item => {
                item.classList.remove('drag-over');
            });
            draggedItem = null;
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        item.addEventListener('dragenter', function(e) {
            e.preventDefault();
            if (this !== draggedItem) {
                this.classList.add('drag-over');
            }
        });

        item.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            if (this !== draggedItem) {
                // Remove drag-over class
                this.classList.remove('drag-over');

                // Get all items
                const allItems = Array.from(container.querySelectorAll('.draggable-item'));
                const draggedIndex = allItems.indexOf(draggedItem);
                const targetIndex = allItems.indexOf(this);

                // Move the dragged item
                if (draggedIndex < targetIndex) {
                    this.parentElement.after(draggedItem.parentElement);
                } else {
                    this.parentElement.before(draggedItem.parentElement);
                }

                // Update the order
                updateColumnOrder();
            }
        });
    });
}

// ============================================================================
// REPORT GENERATION FUNCTIONS
// ============================================================================

// Update preview
function updatePreview() {
    console.log('Updating preview...');

    const form = document.getElementById('printReportForm');
    if (!form) {
        console.error('Report form not found');
        return;
    }

    // Get selected columns in current order
    const columnItems = document.querySelectorAll('#columnsContainer .draggable-item');
    const selectedColumns = [];
    const selectedLabels = [];

    columnItems.forEach(item => {
        const checkbox = item.querySelector('.column-checkbox');
        if (checkbox && checkbox.checked) {
            selectedColumns.push(checkbox.value);

            // Get the label text
            const label = item.querySelector('.form-check-label');
            if (label) {
                selectedLabels.push(label.textContent.trim());
            }
        }
    });

    console.log('Selected columns for preview:', selectedColumns);
    console.log('Selected labels for preview:', selectedLabels);

    const preview = document.getElementById('columnOrderPreview');
    if (preview) {
        preview.textContent = selectedLabels.join(', ') || 'No columns selected';
        console.log('Preview updated:', preview.textContent);
    }
}

// Generate report
function generateReport() {
    const form = document.getElementById('printReportForm');
    if (!form) {
        console.error('Report form not found');
        return;
    }

    // Get selected columns
    const selectedColumns = Array.from(form.querySelectorAll('input[name="columns[]"]:checked'))
        .map(cb => cb.value);

    if (selectedColumns.length === 0) {
        Swal.fire({
            title: 'Warning!',
            text: 'Please select at least one column to include in the report.',
            icon: 'warning',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
        return;
    }

    // Get form values
    const classId = form.querySelector('[name="class_id"]').value;
    const status = form.querySelector('[name="status"]').value;
    const formatElement = form.querySelector('[name="format"]:checked');
    const columnsOrderInput = form.querySelector('[name="columns_order"]');
    const includeHeader = form.querySelector('[name="include_header"]').checked;
    const includeLogo = form.querySelector('[name="include_logo"]').checked;
    const orientation = form.querySelector('[name="orientation"]').value;
    const termId = form.querySelector('[name="term_id"]')?.value || '';
    const sessionId = form.querySelector('[name="session_id"]')?.value || '';

    if (!formatElement) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an export format (PDF or Excel).',
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
        return;
    }

    const format = formatElement.value;

    // Debug: Log what's being sent
    console.log('Generating report with:', {
        selectedColumns: selectedColumns,
        columnsOrder: columnsOrderInput?.value || '',
        classId: classId,
        status: status,
        termId: termId,
        sessionId: sessionId,
        format: format,
        orientation: orientation,
        includeHeader: includeHeader,
        includeLogo: includeLogo
    });

    // Show loading indicator
    Swal.fire({
        title: 'Generating Report...',
        text: 'This may take a moment. Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Build query parameters
    const params = new URLSearchParams({
        class_id: classId,
        term_id: termId,
        session_id: sessionId,
        status: status,
        columns: selectedColumns.join(','),
        columns_order: columnsOrderInput?.value || '',
        format: format,
        orientation: orientation,
        include_header: includeHeader ? '1' : '0',
        include_logo: includeLogo ? '1' : '0'
    });

    // Make the request
    axios.get(`/students/report?${params.toString()}`, {
        responseType: 'blob',
        timeout: 120000 // 2 minutes timeout
    })
    .then(response => {
        Swal.close();

        // Create a blob from the response
        const blob = new Blob([response.data], {
            type: response.headers['content-type']
        });

        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

        // Get filename from content-disposition header or generate one
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'student-report.' + (format === 'pdf' ? 'pdf' : 'xlsx');

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="(.+)"/);
            if (filenameMatch && filenameMatch[1]) {
                filename = filenameMatch[1];
            }
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        setTimeout(() => {
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 100);

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('printStudentReportModal'));
        if (modal) {
            modal.hide();
        }

        // Show success message
        Swal.fire({
            title: 'Success!',
            text: `Report generated successfully and downloaded as ${format.toUpperCase()}`,
            icon: 'success',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false,
            timer: 3000,
            timerProgressBar: true
        });
    })
    .catch(error => {
        Swal.close();

        console.error('Error generating report:', error);

        let errorMessage = 'Failed to generate report. Please try again.';

        if (error.response) {
            // Server responded with error status
            if (error.response.status === 404) {
                errorMessage = 'No students found matching the selected filters.';
            } else if (error.response.status === 422) {
                errorMessage = error.response.data.message || 'Validation error. Please check your selections.';
            } else if (error.response.status === 500) {
                if (error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else {
                    errorMessage = 'Server error. Please try again later.';
                }
            }

            // Try to parse error message from response
            if (error.response.data && typeof error.response.data === 'object') {
                if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }
        } else if (error.code === 'ECONNABORTED') {
            errorMessage = 'Request timeout. The report generation is taking too long. Try with fewer students or different filters.';
        }

        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
    });
}

// ============================================================================
// DEBUG FUNCTIONS
// ============================================================================

// Debug column ordering
function debugColumnOrdering() {
    console.log('=== DEBUG COLUMN ORDERING ===');

    const container = document.getElementById('columnsContainer');
    const hiddenInput = document.getElementById('columnsOrderInput');
    const preview = document.getElementById('columnOrderPreview');

    if (!container) {
        console.error('❌ columnsContainer not found');
        return;
    }

    if (!hiddenInput) {
        console.error('❌ columnsOrderInput not found');
        return;
    }

    console.log('✅ Container found:', container);
    console.log('✅ Hidden input found, value:', hiddenInput.value);
    console.log('✅ Preview element:', preview ? 'found' : 'not found');
    console.log('✅ Preview text:', preview ? preview.textContent : 'N/A');

    // Check Sortable.js
    if (typeof Sortable !== 'undefined') {
        console.log('✅ Sortable.js loaded, version:', Sortable.version);
    } else {
        console.error('❌ Sortable.js NOT loaded');
    }

    // Check column items
    const items = container.querySelectorAll('.draggable-item');
    console.log('✅ Column items found:', items.length);

    items.forEach((item, index) => {
        const checkbox = item.querySelector('.column-checkbox');
        const label = item.querySelector('.form-check-label');
        const dragHandle = item.querySelector('.drag-handle');

        console.log(`Item ${index + 1}:`, {
            value: checkbox ? checkbox.value : 'N/A',
            checked: checkbox ? checkbox.checked : false,
            label: label ? label.textContent.trim() : 'N/A',
            dragHandle: dragHandle ? 'found' : 'not found',
            position: index + 1
        });
    });

    // Check current order
    const checkboxes = container.querySelectorAll('.column-checkbox:checked');
    const currentOrder = Array.from(checkboxes).map(cb => cb.value);
    console.log('✅ Current checked order:', currentOrder);
}

// ============================================================================
// INITIALIZATION FUNCTIONS
// ============================================================================

// Initialize student list
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

// ============================================================================
// EVENT LISTENERS
// ============================================================================

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing application...');

    // Initialize student list
    initializeStudentList();

    // Initialize form submission handlers
    initializeFormSubmissions();

    // Initialize report modal
    initializeReportModal();

    // Add event listeners for admission year changes
    document.getElementById('admissionYear')?.addEventListener('change', () => updateAdmissionNumber());
    document.getElementById('editAdmissionYear')?.addEventListener('change', () => updateAdmissionNumber('edit'));
});

// Initialize form submissions
function initializeFormSubmissions() {
    // Edit form submission
    const editForm = document.getElementById('editStudentForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEditFormSubmit(this);
        });
    }

    // Add form submission
    const addForm = document.getElementById('addStudentForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAddFormSubmit(this);
        });
    }
}

// Handle edit form submission
function handleEditFormSubmit(form) {
    console.log('Edit form submitted');

    if (!ensureAxios()) return;

    Swal.fire({
        title: 'Updating Student...',
        text: 'Please wait while we update student information',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData(form);
    const studentId = document.getElementById('editStudentId')?.value;

    if (!studentId) {
        Swal.fire({
            title: 'Error!',
            text: 'Student ID not found',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    const url = form.action;

    axios.post(url, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then((response) => {
        Swal.close();

        if (response.data.success) {
            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student updated successfully',
                icon: 'success',
                showCancelButton: false,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const editModalElement = document.getElementById('editStudentModal');
                    if (editModalElement) {
                        const editModal = bootstrap.Modal.getInstance(editModalElement);
                        if (editModal) {
                            editModal.hide();
                        }
                    }

                    fetchStudents();

                    if (response.data.redirect) {
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    }
                }
            });
        } else {
            throw new Error(response.data.message || 'Update failed');
        }
    })
    .catch((error) => {
        Swal.close();
        console.error('Error updating student:', error);

        let errorMessage = 'Failed to update student';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            let errorList = '';
            for (const field in errors) {
                errorList += `<li>${errors[field].join(', ')}</li>`;
            }
            errorMessage = `<div class="text-start"><strong>Validation Errors:</strong><ul class="mb-0">${errorList}</ul></div>`;
        }

        Swal.fire({
            title: 'Error!',
            html: errorMessage,
            icon: 'error',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    });
}

// Handle add form submission
function handleAddFormSubmit(form) {
    console.log('Add form submitted');

    if (!ensureAxios()) return;

    Swal.fire({
        title: 'Creating Student...',
        text: 'Please wait while we create the student record',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData(form);

    axios.post(form.action, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then((response) => {
        Swal.close();

        if (response.data.success) {
            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student created successfully',
                icon: 'success',
                showCancelButton: false,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const addModalElement = document.getElementById('addStudentModal');
                    if (addModalElement) {
                        const addModal = bootstrap.Modal.getInstance(addModalElement);
                        if (addModal) {
                            addModal.hide();
                        }
                    }

                    form.reset();
                    fetchStudents();

                    if (response.data.redirect) {
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    }
                }
            });
        } else {
            throw new Error(response.data.message || 'Creation failed');
        }
    })
    .catch((error) => {
        Swal.close();
        console.error('Error creating student:', error);

        let errorMessage = 'Failed to create student';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            let errorList = '';
            for (const field in errors) {
                errorList += `<li>${errors[field].join(', ')}</li>`;
            }
            errorMessage = `<div class="text-start"><strong>Validation Errors:</strong><ul class="mb-0">${errorList}</ul></div>`;
        }

        Swal.fire({
            title: 'Error!',
            html: errorMessage,
            icon: 'error',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    });
}

// Initialize report modal
function initializeReportModal() {
    const reportModal = document.getElementById('printStudentReportModal');

    if (reportModal) {
        // Initialize when modal is shown
        reportModal.addEventListener('shown.bs.modal', function() {
            console.log('Report modal shown, initializing column ordering...');
            setTimeout(() => {
                initializeColumnOrdering();
            }, 100);
        });

        // Clean up when modal is hidden
        reportModal.addEventListener('hidden.bs.modal', function() {
            console.log('Report modal hidden');
            if (columnSortable) {
                columnSortable.destroy();
                columnSortable = null;
            }
        });

        // Initialize on load if modal is already open
        if (reportModal.classList.contains('show')) {
            console.log('Modal already open, initializing...');
            setTimeout(() => {
                initializeColumnOrdering();
            }, 100);
        }
    }

    // Initialize generate report button
    const generateReportBtn = document.getElementById('generateReportBtn');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', generateReport);
    }

    // Update preview when checkboxes change
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('column-checkbox')) {
            console.log('Checkbox change detected, updating preview...');
            updatePreview();
        }
    });

    // Initialize preview on load
    updatePreview();
}

// ============================================================================
// STATE AND LGA MANAGEMENT (If needed)
// ============================================================================

// Nigerian states data
const nigerianStates = [
     { name: "Abia", lgAs: ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"] },
    { name: "Adamawa", lgAs: ["Demsa", "Fufure", "Ganye", "Gayuk", "Gombi", "Grie", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"] },
    { name: "Akwa Ibom", lgAs: ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono-Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat-Enin", "Nsit-Atai", "Nsit-Ibom", "Nsit-Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung-Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"] },
    { name: "Anambra", lgAs: ["Aguata", "Anambra East", "Anambra West", "Anaocha", "Awka North", "Awka South", "Ayamelum", "Dunukofia", "Ekwusigo", "Idemili North", "Idemili South", "Ihiala", "Njikoka", "Nnewi North", "Nnewi South", "Ogbaru", "Onitsha North", "Onitsha South", "Orumba North", "Orumba South", "Oyi"] },
    { name: "Bauchi", lgAs: ["Alkaleri", "Bauchi", "Bogoro", "Damban", "Darazo", "Dass", "Gamawa", "Ganjuwa", "Giade", "Itas/Gadau", "Jama'are", "Katagum", "Kirfi", "Misau", "Ningi", "Shira", "Tafawa Balewa", "Toro", "Warji", "Zaki"] },
    { name: "Bayelsa", lgAs: ["Brass", "Ekeremor", "Kolokuma/Opokuma", "Nembe", "Ogbia", "Sagbama", "Southern Ijaw", "Yenagoa"] },
    { name: "Benue", lgAs: ["Ado", "Agatu", "Apa", "Buruku", "Gboko", "Guma", "Gwer East", "Gwer West", "Katsina-Ala", "Konshisha", "Kwande", "Logo", "Makurdi", "Obi", "Ogbadibo", "Ohimini", "Oju", "Okpokwu", "Oturkpo", "Tarka", "Ukum", "Ushongo", "Vandeikya"] },
    { name: "Borno", lgAs: ["Abadam", "Askira/Uba", "Bama", "Bayo", "Biu", "Chibok", "Damboa", "Dikwa", "Gubio", "Guzamala", "Gwoza", "Hawul", "Jere", "Kaga", "Kala/Balge", "Konduga", "Kukawa", "Kwaya Kusar", "Mafa", "Magumeri", "Maiduguri", "Marte", "Mobbar", "Monguno", "Ngala", "Nganzai", "Shani"] },
    { name: "Cross River", lgAs: ["Abi", "Akamkpa", "Akpabuyo", "Bakassi", "Bekwarra", "Biase", "Boki", "Calabar Municipal", "Calabar South", "Etung", "Ikom", "Obanliku", "Obubra", "Obudu", "Odukpani", "Ogoja", "Yakuur", "Yala"] },
    { name: "Delta", lgAs: ["Aniocha North", "Aniocha South", "Bomadi", "Burutu", "Ethiope East", "Ethiope West", "Ika North East", "Ika South", "Isoko North", "Isoko South", "Ndokwa East", "Ndokwa West", "Okpe", "Oshimili North", "Oshimili South", "Patani", "Sapele", "Udu", "Ughelli North", "Ughelli South", "Ukwuani", "Uvwie", "Warri North", "Warri South", "Warri South West"] },
    { name: "Ebonyi", lgAs: ["Abakaliki", "Afikpo North", "Afikpo South", "Ebonyi", "Ezza North", "Ezza South", "Ikwo", "Ishielu", "Ivo", "Izzi", "Ohaozara", "Ohaukwu", "Onicha"] },
    { name: "Edo", lgAs: ["Akoko-Edo", "Egor", "Esan Central", "Esan North-East", "Esan South-East", "Esan West", "Etsako Central", "Etsako East", "Etsako West", "Igueben", "Ikpoba Okha", "Orhionmwon", "Oredo", "Ovia North-East", "Ovia South-West", "Owan East", "Owan West", "Uhunmwonde"] },
    { name: "Ekiti", lgAs: ["Ado Ekiti", "Efon", "Ekiti East", "Ekiti South-West", "Ekiti West", "Emure", "Gbonyin", "Ido Osi", "Ijero", "Ikere", "Ilejemeje", "Irepodun/Ifelodun", "Ise/Orun", "Moba", "Oye"] },
    { name: "Enugu", lgAs: ["Aninri", "Awgu", "Enugu East", "Enugu North", "Enugu South", "Ezeagu", "Igbo Etiti", "Igbo Eze North", "Igbo Eze South", "Isi Uzo", "Nkanu East", "Nkanu West", "Nsukka", "Oji River", "Udenu", "Udi", "Uzo Uwani"] },
    { name: "FCT", lgAs: ["Abaji", "Bwari", "Gwagwalada", "Kuje", "Kwali", "Municipal Area Council"] },
    { name: "Gombe", lgAs: ["Akko", "Balanga", "Billiri", "Dukku", "Funakaye", "Gombe", "Kaltungo", "Kwami", "Nafada", "Shongom", "Yamaltu/Deba"] },
    { name: "Imo", lgAs: ["Aboh Mbaise", "Ahiazu Mbaise", "Ehime Mbano", "Ezinihitte", "Ideato North", "Ideato South", "Ihitte/Uboma", "Ikeduru", "Isiala Mbano", "Isu", "Mbaitoli", "Ngor Okpala", "Njaba", "Nkwerre", "Nwangele", "Obowo", "Oguta", "Ohaji/Egbema", "Okigwe", "Orlu", "Orsu", "Oru East", "Oru West", "Owerri Municipal", "Owerri North", "Owerri West", "Unuimo"] },
    { name: "Jigawa", lgAs: ["Auyo", "Babura", "Biriniwa", "Birnin Kudu", "Buji", "Dutse", "Gagarawa", "Garki", "Gumel", "Guri", "Gwaram", "Gwiwa", "Hadejia", "Jahun", "Kafin Hausa", "Kazaure", "Kiri Kasama", "Kiyawa", "Kaugama", "Maigatari", "Malam Madori", "Miga", "Ringim", "Roni", "Sule Tankarkar", "Taura", "Yankwashi"] },
    { name: "Kaduna", lgAs: ["Birnin Gwari", "Chikun", "Giwa", "Igabi", "Ikara", "Jaba", "Jema'a", "Kachia", "Kaduna North", "Kaduna South", "Kagarko", "Kajuru", "Kaura", "Kauru", "Kubau", "Kudan", "Lere", "Makarfi", "Sabon Gari", "Sanga", "Soba", "Zangon Kataf", "Zaria"] },
    { name: "Kano", lgAs: ["Ajingi", "Albasu", "Bagwai", "Bebeji", "Bichi", "Bunkure", "Dala", "Dambatta", "Dawakin Kudu", "Dawakin Tofa", "Doguwa", "Fagge", "Gabasawa", "Garko", "Garun Mallam", "Gaya", "Gezawa", "Gwale", "Gwarzo", "Kabo", "Kano Municipal", "Karaye", "Kibiya", "Kiru", "Kumbotso", "Kunchi", "Kura", "Madobi", "Makoda", "Minjibir", "Nasarawa", "Rano", "Rimin Gado", "Rogo", "Shanono", "Sumaila", "Takai", "Tarauni", "Tofa", "Tsanyawa", "Tudun Wada", "Ungogo", "Warawa", "Wudil"] },
    { name: "Katsina", lgAs: ["Bakori", "Batagarawa", "Batsari", "Baure", "Bindawa", "Charanchi", "Dan Musa", "Dandume", "Danja", "Daura", "Dutsi", "Dutsin Ma", "Faskari", "Funtua", "Ingawa", "Jibia", "Kafur", "Kaita", "Kankara", "Kankia", "Katsina", "Kurfi", "Kusada", "Mai'Adua", "Malumfashi", "Mani", "Mashi", "Matazu", "Musawa", "Rimi", "Sabuwa", "Safana", "Sandamu", "Zango"] },
    { name: "Kebbi", lgAs: ["Aleiro", "Arewa Dandi", "Argungu", "Augie", "Bagudo", "Birnin Kebbi", "Bunza", "Dandi", "Fakai", "Gwandu", "Jega", "Kalgo", "Koko/Besse", "Maiyama", "Ngaski", "Sakaba", "Shanga", "Suru", "Danko/Wasagu", "Yauri", "Zuru"] },
    { name: "Kogi", lgAs: ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa Muro", "Ofu", "Ogori/Magongo", "Okehi", "Okene", "Olamaboro", "Omala", "Yagba East", "Yagba West"] },
    { name: "Kwara", lgAs: ["Asa", "Baruten", "Edu", "Ekiti", "Ifelodun", "Ilorin East", "Ilorin South", "Ilorin West", "Irepodun", "Isin", "Kaiama", "Moro", "Offa", "Oke Ero", "Oyun", "Pategi"] },
    { name: "Lagos", lgAs: ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"] },
    { name: "Nasarawa", lgAs: ["Akwanga", "Awe", "Doma", "Karu", "Keana", "Keffi", "Kokona", "Lafia", "Nasarawa", "Nasarawa Egon", "Obi", "Toto", "Wamba"] },
    { name: "Niger", lgAs: ["Agaie", "Agwara", "Bida", "Borgu", "Bosso", "Chanchaga", "Edati", "Gbako", "Gurara", "Katcha", "Kontagora", "Lapai", "Lavun", "Magama", "Mariga", "Mashegu", "Mokwa", "Moya", "Paikoro", "Rafi", "Rijau", "Shiroro", "Suleja", "Tafa", "Wushishi"] },
    { name: "Ogun", lgAs: ["Abeokuta North", "Abeokuta South", "Ado-Odo/Ota", "Egbado North", "Egbado South", "Ewekoro", "Ifo", "Ijebu East", "Ijebu North", "Ijebu North East", "Ijebu Ode", "Ikenne", "Imeko Afon", "Ipokia", "Obafemi Owode", "Odeda", "Odogbolu", "Ogun Waterside", "Remo North", "Shagamu"] },
    { name: "Ondo", lgAs: ["Akoko North-East", "Akoko North-West", "Akoko South-East", "Akoko South-West", "Akure North", "Akure South", "Ese Odo", "Idanre", "Ifedore", "Ilaje", "Ile Oluji/Okeigbo", "Irele", "Odigbo", "Okitipupa", "Ondo East", "Ondo West", "Ose", "Owo"] },
    { name: "Osun", lgAs: ["Aiyedade", "Aiyedire", "Atakunmosa East", "Atakunmosa West", "Boluwaduro", "Boripe", "Ede North", "Ede South", "Egbedore", "Ejigbo", "Ife Central", "Ife East", "Ife North", "Ife South", "Ifedayo", "Ifelodun", "Ila", "Ilesa East", "Ilesa West", "Irepodun", "Irewole", "Isokan", "Iwo", "Obokun", "Odo Otin", "Ola Oluwa", "Olorunda", "Oriade", "Orolu", "Osogbo"] },
    { name: "Oyo", lgAs: ["Afijio", "Akinyele", "Atiba", "Atisbo", "Egbeda", "Ibadan North", "Ibadan North-East", "Ibadan North-West", "Ibadan South-East", "Ibadan South-West", "Ibarapa Central", "Ibarapa East", "Ibarapa North", "Ido", "Irepo", "Iseyin", "Itesiwaju", "Iwajowa", "Kajola", "Lagelu", "Ogbomosho North", "Ogbomosho South", "Ogo Oluwa", "Olorunsogo", "Oluyole", "Ona Ara", "Orelope", "Ori Ire", "Oyo East", "Oyo West", "Saki East", "Saki West", "Surulere"] },
    { name: "Plateau", lgAs: ["Bokkos", "Barkin Ladi", "Bassa", "Jos East", "Jos North", "Jos South", "Kanam", "Kanke", "Langtang North", "Langtang South", "Mangu", "Mikang", "Pankshin", "Qua'an Pan", "Riyom", "Shendam", "Wase"] },
    { name: "Rivers", lgAs: ["Abua/Odual", "Ahoada East", "Ahoada West", "Akuku-Toru", "Andoni", "Asari-Toru", "Bonny", "Degema", "Eleme", "Emohua", "Etche", "Gokana", "Ikwerre", "Khana", "Obio/Akpor", "Ogba/Egbema/Ndoni", "Ogu/Bolo", "Okrika", "Omuma", "Opobo/Nkoro", "Oyigbo", "Port Harcourt", "Tai"] },
    { name: "Sokoto", lgAs: ["Binji", "Bodinga", "Dange Shuni", "Gada", "Goronyo", "Gudu", "Gwadabawa", "Illela", "Isa", "Kebbe", "Kware", "Rabah", "Sabon Birni", "Shagari", "Silame", "Sokoto North", "Sokoto South", "Tambuwal", "Tangaza", "Tureta", "Wamako", "Wurno", "Yabo"] },
    { name: "Taraba", lgAs: ["Ardo Kola", "Bali", "Donga", "Gashaka", "Gassol", "Ibi", "Jalingo", "Karim Lamido", "Kumi", "Lau", "Sardauna", "Takum", "Ussa", "Wukari", "Yorro", "Zing"] },
    { name: "Yobe", lgAs: ["Bade", "Bursari", "Damaturu", "Fika", "Fune", "Geidam", "Gujba", "Gulani", "Jakusko", "Karasuwa", "Machina", "Nangere", "Nguru", "Potiskum", "Tarmuwa", "Yunusari", "Yusufari"] },
    { name: "Zamfara", lgAs: ["Anka", "Bakura", "Birnin Magaji/Kiyaw", "Bukkuyum", "Bungudu", "Gummi", "Gusau", "Kaura Namoda", "Maradun", "Maru", "Shinkafi", "Talata Mafara", "Chafe", "Zurmi"] }
];

// Initialize states dropdown
function initializeStatesDropdown(stateDropdownId, lgaDropdownId) {
    const stateSelect = document.getElementById(stateDropdownId);
    const lgaSelect = document.getElementById(lgaDropdownId);

    if (!stateSelect || !lgaSelect) return;

    // Clear existing options
    stateSelect.innerHTML = '<option value="">Select State</option>';
    lgaSelect.innerHTML = '<option value="">Select LGA</option>';

    // Populate states
    nigerianStates.forEach(state => {
        const option = document.createElement('option');
        option.value = state.name;
        option.textContent = state.name;
        stateSelect.appendChild(option);
    });

    // Add change event listener
    stateSelect.addEventListener('change', function() {
        const selectedState = this.value;
        const state = nigerianStates.find(s => s.name === selectedState);

        // Clear LGA dropdown
        lgaSelect.innerHTML = '<option value="">Select LGA</option>';

        if (state) {
            // Populate LGAs for selected state
            state.lgAs.forEach(lga => {
                const option = document.createElement('option');
                option.value = lga;
                option.textContent = lga;
                lgaSelect.appendChild(option);
            });
        }
    });
}

// Set specific state and LGA
function setStateAndLGA(stateDropdownId, lgaDropdownId, stateName, lgaName) {
    const stateSelect = document.getElementById(stateDropdownId);
    const lgaSelect = document.getElementById(lgaDropdownId);

    if (!stateSelect || !lgaSelect) return;

    // Set state
    if (stateName) {
        stateSelect.value = stateName;

        // Trigger change to populate LGAs
        const event = new Event('change');
        stateSelect.dispatchEvent(event);

        // Set LGA after a short delay
        setTimeout(() => {
            lgaSelect.value = lgaName;
        }, 100);
    }
}
</script>
@endsection
