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
                /* ======================== */
                /* DASHBOARD STATS CARDS */
                /* ======================== */
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

                /* ======================== */
                /* PROFESSIONAL STUDENT CARDS */
                /* ======================== */
                .student-card-professional {
                    border: none;
                    border-radius: 12px;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    margin-bottom: 24px;
                    background: white;
                    position: relative;
                    overflow: hidden;
                    height: 100%;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                }

                .student-card-professional:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
                }

                .student-card-professional.selected {
                    border: 2px solid #405189;
                    background: linear-gradient(to right, rgba(64, 81, 137, 0.02), rgba(64, 81, 137, 0.05));
                }

                .student-card-professional .card-body {
                    padding: 24px;
                    position: relative;
                }

                /* Card header with gradient */
                .card-header-gradient {
                    height: 80px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    position: relative;
                    margin: -24px -24px 24px -24px;
                    border-radius: 12px 12px 0 0;
                }

                /* Avatar Container */
                .avatar-container-professional {
                    width: 100px;
                    height: 100px;
                    margin: -50px auto 20px auto;
                    position: relative;
                    z-index: 2;
                }

                .avatar-professional {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 4px solid white;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 32px;
                    font-weight: bold;
                    color: white;
                }

                /* Student Info */
                .student-info {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .student-name-professional {
                    font-size: 18px;
                    font-weight: 700;
                    color: #2d3748;
                    margin-bottom: 8px;
                    line-height: 1.3;
                }

                .student-admission-professional {
                    font-size: 13px;
                    color: #718096;
                    background: #f7fafc;
                    padding: 4px 12px;
                    border-radius: 20px;
                    display: inline-block;
                    margin-bottom: 16px;
                    font-weight: 500;
                }

                /* Details Grid */
                .details-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px;
                    margin-bottom: 20px;
                }

                .detail-item {
                    text-align: left;
                }

                .detail-label {
                    font-size: 11px;
                    color: #718096;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    font-weight: 600;
                    margin-bottom: 4px;
                }

                .detail-value {
                    font-size: 13px;
                    color: #2d3748;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }

                /* Status Badge */
                .status-badge-professional {
                    position: absolute;
                    top: 16px;
                    right: 16px;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    z-index: 2;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }

                .status-active-professional {
                    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
                    color: white;
                }

                .status-inactive-professional {
                    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
                    color: white;
                }

                /* Action Buttons */
                .action-buttons-professional {
                    display: flex;
                    justify-content: center;
                    gap: 8px;
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #e2e8f0;
                }

                .action-btn-professional {
                    width: 36px;
                    height: 36px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    background: #f7fafc;
                    color: #4a5568;
                }

                .action-btn-professional:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .view-btn-professional:hover {
                    background: #4299e1;
                    color: white;
                }

                .edit-btn-professional:hover {
                    background: #38a169;
                    color: white;
                }

                .delete-btn-professional:hover {
                    background: #e53e3e;
                    color: white;
                }

                /* Checkbox */
                .checkbox-container-professional {
                    position: absolute;
                    top: 16px;
                    left: 16px;
                    z-index: 2;
                }

                .checkbox-container-professional .form-check-input {
                    width: 20px;
                    height: 20px;
                    cursor: pointer;
                    border: 2px solid #cbd5e0;
                }

                .checkbox-container-professional .form-check-input:checked {
                    background-color: #405189;
                    border-color: #405189;
                }

                /* Empty state */
                .empty-state-professional {
                    text-align: center;
                    padding: 60px 20px;
                    background: #f8fafc;
                    border-radius: 12px;
                    border: 2px dashed #cbd5e0;
                }

                .empty-state-professional i {
                    font-size: 60px;
                    color: #a0aec0;
                    margin-bottom: 20px;
                    display: block;
                }

                .empty-state-professional h5 {
                    color: #4a5568;
                    font-weight: 600;
                    margin-bottom: 10px;
                }

                .empty-state-professional p {
                    color: #718096;
                    margin-bottom: 0;
                    max-width: 400px;
                    margin: 0 auto;
                }

                /* ======================== */
                /* TABLE STYLING */
                /* ======================== */
                .table-responsive {
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                }

                .table thead th {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    padding: 16px 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    font-size: 12px;
                    letter-spacing: 0.5px;
                }

                .table tbody tr {
                    transition: all 0.2s ease;
                }

                .table tbody tr:hover {
                    background: #f7fafc;
                    transform: scale(1.002);
                }

                .table tbody td {
                    padding: 16px 12px;
                    vertical-align: middle;
                    border-color: #e2e8f0;
                }

                /* ======================== */
                /* FILTER BAR */
                /* ======================== */
                .filter-bar {
                    background: white;
                    padding: 20px;
                    border-radius: 12px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                    margin-bottom: 24px;
                }

                .search-box-professional {
                    position: relative;
                }

                .search-box-professional .form-control {
                    padding-left: 40px;
                    border-radius: 10px;
                    border: 1px solid #e2e8f0;
                    height: 44px;
                    font-size: 14px;
                }

                .search-box-professional .search-icon {
                    position: absolute;
                    left: 15px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #a0aec0;
                    font-size: 16px;
                }

                /* ======================== */
                /* VIEW TOGGLE BUTTONS */
                /* ======================== */
                .view-toggle-buttons .btn {
                    padding: 10px 20px;
                    border-radius: 10px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }

                .view-toggle-buttons .btn-outline-secondary {
                    border: 2px solid #e2e8f0;
                    color: #4a5568;
                }

                .view-toggle-buttons .btn-outline-secondary.active {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-color: transparent;
                    color: white;
                    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
                }

                /* ======================== */
                /* MODAL STYLES */
                /* ======================== */
                .modern-modal {
                    border-radius: 16px;
                    overflow: hidden;
                    border: none;
                    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
                }

                .modern-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 24px 32px;
                    border: none;
                    position: relative;
                }

                .modern-header:after {
                    content: '';
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: rgba(255, 255, 255, 0.2);
                }

                .modern-close {
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 50%;
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    border: none;
                    opacity: 1;
                    transition: all 0.2s ease;
                }

                .modern-close:hover {
                    background: rgba(255, 255, 255, 0.2);
                    transform: rotate(90deg);
                }

                /* ======================== */
                /* DRAG & DROP STYLES */
                /* ======================== */
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
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    background: white;
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

                /* ======================== */
                /* PAGINATION */
                /* ======================== */
                .pagination-wrap .page-link {
                    border-radius: 8px;
                    margin: 0 4px;
                    border: 1px solid #e2e8f0;
                    color: #4a5568;
                    min-width: 36px;
                    text-align: center;
                    transition: all 0.2s ease;
                }

                .pagination-wrap .page-link:hover {
                    background: #f7fafc;
                    border-color: #cbd5e0;
                }

                .pagination-wrap .page-item.active .page-link {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-color: transparent;
                    color: white;
                }

                .pagination-wrap .page-item.disabled .page-link {
                    background: #f7fafc;
                    color: #a0aec0;
                }
            </style>

            <!-- Dashboard Statistics -->
            <div class="container">
                <h2 class="mb-4 text-center">School Dashboard Statistics</h2>
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
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0">Students by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0">Students by Active/Inactive Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="studentsByActiveStatusChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
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
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center bg-white">
                            <div class="flex-grow-1 d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Students</h5>
                                    <p class="text-muted mb-0" id="selectedCountText">0 selected</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <!-- View Toggle Buttons -->
                                    <div class="view-toggle-buttons btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary active" id="tableViewBtn">
                                            <i class="fas fa-table me-1"></i> Table
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="cardViewBtn">
                                            <i class="fas fa-th-large me-1"></i> Cards
                                        </button>
                                    </div>

                                    @can('Delete student')
                                        <div class="dropdown">
                                            <button class="btn btn-outline-danger dropdown-toggle d-none" id="bulkActionsBtn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-delete-bin-2-line me-1"></i> Bulk Actions
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="bulkActionsBtn">
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteMultiple()">
                                                        <i class="ri-delete-bin-line me-2"></i> Delete Selected
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-primary" href="javascript:void(0);" onclick="showUpdateCurrentTermModal()">
                                                        <i class="ri-calendar-line me-2"></i> Update Current Term
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="javascript:void(0);" onclick="exportSelectedStudents()">
                                                        <i class="ri-download-line me-2"></i> Export Selected
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan

                                    @can('Create student')
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                            <i class="bi bi-plus-circle align-baseline me-1"></i> Add Student
                                        </button>
                                    @endcan

                                    <!-- Print/Export Report Button -->
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#printStudentReportModal">
                                        <i class="ri-printer-line align-bottom me-1"></i> Print / Export
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter Bar -->
                            <div class="filter-bar">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="search-box-professional">
                                            <input type="text" class="form-control" id="search-input" placeholder="Search by name or admission no">
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
                                        <button type="button" class="btn btn-outline-secondary w-100" onclick="filterData()">
                                            <i class="bi bi-funnel align-baseline me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Table View (Default - Visible) -->
                            <div id="tableView" class="view-container">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="studentTable">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAllTable">
                                                    </div>
                                                </th>
                                                <th>Student</th>
                                                <th>Admission No</th>
                                                <th>Class</th>
                                                <th>Status</th>
                                                <th>Gender</th>
                                                <th>Registered</th>
                                                <th width="150">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            <!-- JS renders rows here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Cards View (Hidden by default) -->
                            <div id="cardView" class="view-container d-none">
                                <div class="row" id="studentsCardsContainer">
                                    <!-- Students will be rendered here as professional cards -->
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div class="row mt-4 align-items-center" id="pagination-element">
                                <div class="col-sm">
                                    <div class="text-muted">
                                        Showing <span class="fw-semibold" id="showingCount">0</span> of <span class="fw-semibold" id="totalCount">0</span> Results
                                    </div>
                                </div>
                                <div class="col-sm-auto">
                                    <div class="pagination-wrap hstack gap-2">
                                        <a class="page-item pagination-prev disabled" href="javascript:void(0);" id="prevPage">
                                            <i class="mdi mdi-chevron-left"></i>
                                        </a>
                                        <ul class="pagination listjs-pagination mb-0" id="paginationLinks"></ul>
                                        <a class="page-item pagination-next" href="javascript:void(0);" id="nextPage">
                                            <i class="mdi mdi-chevron-right"></i>
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
        <!--        UPDATE CURRENT TERM MODAL                 -->
        <!-- ================================================= -->
        <div id="updateCurrentTermModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modern-modal">
                    <div class="modal-header modern-header">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-alt me-2"></i>Update Current Term
                        </h5>
                        <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="updateCurrentTermForm">
                            @csrf
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                This will update the current term for <span id="selectedStudentsCount">0</span> selected student(s).
                            </div>

                            <div class="mb-3">
                                <label for="currentClass" class="form-label">Current Class <span class="text-danger">*</span></label>
                                <select id="currentClass" name="schoolclassId" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="currentSession" class="form-label">Current Session <span class="text-danger">*</span></label>
                                <select id="currentSession" name="sessionId" class="form-control" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> The current term will be automatically determined and set for all selected students.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmUpdateCurrentTerm">
                            <i class="fas fa-save me-2"></i>Update Current Term
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================= -->
        <!--        PRINT / EXPORT REPORT MODAL               -->
        <!-- ================================================= -->
        <div id="printStudentReportModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content modern-modal">
                    <div class="modal-header modern-header bg-success">
                        <h5 class="modal-title">
                            <i class="ri-printer-line me-2"></i> Generate Student Report
                        </h5>
                        <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
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
                                    <i class="ri-draggable me-1"></i> Select & Arrange Columns
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
                                            <div class="form-check draggable-item" data-column="{{ $key }}">
                                                <div class="d-flex align-items-center p-2 border rounded">
                                                    <span class="drag-handle me-2 cursor-move">
                                                        <i class="ri-draggable"></i>
                                                    </span>
                                                    <input class="form-check-input column-checkbox" type="checkbox"
                                                           name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
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
                            <div class="card mb-4 border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="ri-file-info-line me-2"></i> Report Options</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" role="switch" name="include_header" id="includeHeader" checked>
                                                <label class="form-check-label" for="includeHeader">
                                                    Include School Header
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" role="switch" name="include_logo" id="includeLogo" checked>
                                                <label class="form-check-label" for="includeLogo">
                                                    Include School Logo
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
                            <div class="alert alert-info">
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" id="generateReportBtn">
                            <i class="ri-printer-line me-1"></i> Generate & Download
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Student Modal (Keep existing structure) -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- Keep your existing add student modal code here -->
            <!-- ... -->
        </div>

        <!-- Edit Student Modal (Keep existing structure) -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- Keep your existing edit student modal code here -->
            <!-- ... -->
        </div>

        <!-- View Student Modal (Keep existing structure) -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <!-- Keep your existing view student modal code here -->
            <!-- ... -->
        </div>
    </div>
</div>

<script>
// ============================================================================
// COMPLETE STUDENT MANAGEMENT JAVASCRIPT
// ============================================================================

// Initialize admission number on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAdmissionNumber();
    updateAdmissionNumber('edit');
});

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
            admissionNoInput.value = `${baseFormat}0871`;
        });
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
            admissionNoInput.value = `${baseFormat}0871`;
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
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            admissionNoInput.value = `${baseFormat}0871`;
        });
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
            admissionNoInput.value = `${baseFormat}0871`;
        }
    }
};

// Ensure Axios and CSRF token
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error('Error: Axios is not defined');
        return false;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('Error: CSRF token not found');
        return false;
    }
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    return true;
}

// Global variables
let allStudents = [];
const itemsPerPage = 100;
const defaultAvatar = '{{ asset("storage/images/student_avatars/unnamed.jpg") }}';

// Get student initials
function getStudentInitials(firstName, lastName) {
    const firstInitial = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
    const lastInitial = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
    return (firstInitial + lastInitial) || '??';
}

// ============================================================================
// STATE AND LGA MANAGEMENT FUNCTIONS
// ============================================================================

// Nigerian states data (truncated for brevity - include full array from your code)
const nigerianStates = [
    { name: "Abia", lgAs: ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"] },
    // ... Include all other states from your original code
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

// Calculate age
window.calculateAge = function(dateValue, targetId) {
    if (!dateValue) return;

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
        }
    } catch (error) {
        console.error('Error calculating age:', error);
    }
};

// ============================================================================
// VIEW MANAGEMENT FUNCTIONS
// ============================================================================

// Toggle between table and card views
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
        document.getElementById('bulkActionsBtn').classList.add('d-none');
    } else {
        tableView.classList.add('d-none');
        cardView.classList.remove('d-none');
        tableViewBtn.classList.remove('active');
        cardViewBtn.classList.add('active');

        if (document.getElementById('studentsCardsContainer').children.length === 0 && allStudents.length > 0) {
            renderStudentsCards(allStudents);
        }

        document.getElementById('checkAll').checked = false;
        document.getElementById('bulkActionsBtn').classList.add('d-none');
    }
}

// ============================================================================
// PROFESSIONAL STUDENT CARDS RENDERING
// ============================================================================

function renderStudentsCards(students) {
    console.log('Rendering professional student cards:', students);
    const container = document.getElementById('studentsCardsContainer');
    if (!container) {
        console.error('studentsCardsContainer element not found');
        return;
    }

    container.innerHTML = '';

    if (students.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="empty-state-professional">
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
        const statusClass = isActive ? 'status-active-professional' : 'status-inactive-professional';
        const studentType = student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : 'N/A';
        const regDate = student.created_at ? new Date(student.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : 'N/A';

        const cardHtml = `
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
                <div class="student-card-professional" data-id="${student.id}"
                     data-name="${student.lastname || ''} ${student.firstname || ''} ${student.othername || ''}"
                     data-admission="${student.admissionNo || ''}"
                     data-class="${student.schoolclassid || ''}"
                     data-status="${student.statusId || ''}"
                     data-gender="${student.gender || ''}"
                     data-student-status="${student.student_status || ''}">

                    <div class="card-header-gradient"></div>

                    <div class="checkbox-container-professional">
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox" name="chk_child" value="${student.id}">
                        </div>
                    </div>

                    <span class="status-badge-professional ${statusClass}">${statusText}</span>

                    <div class="avatar-container-professional">
                        <div class="avatar-professional">
                            ${displayInitials}
                        </div>
                        <img src="${avatarUrl}" alt="${student.firstname || ''} ${student.lastname || ''}"
                             class="avatar-professional" style="position: absolute; top: 0; left: 0; display: none;"
                             onload="this.style.display='block'; this.previousElementSibling.style.display='none';"
                             onerror="this.style.display='none'; this.previousElementSibling.style.display='flex';">
                    </div>

                    <div class="student-info">
                        <h6 class="student-name-professional">${student.lastname || ''} ${student.firstname || ''}</h6>
                        <span class="student-admission-professional">${student.admissionNo || 'No Admission No'}</span>
                    </div>

                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Class</div>
                            <div class="detail-value">
                                <i class="fas fa-graduation-cap"></i>
                                ${student.schoolclass || 'N/A'} ${student.arm || ''}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Type</div>
                            <div class="detail-value">
                                <i class="fas fa-user-tag"></i>
                                ${studentType}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value">
                                <i class="fas ${student.gender === 'Female' ? 'fa-female' : 'fa-male'}"></i>
                                ${student.gender || 'N/A'}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Registered</div>
                            <div class="detail-value">
                                <i class="fas fa-calendar-alt"></i>
                                ${regDate}
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons-professional">
                        <button class="action-btn-professional view-btn-professional" title="View Details" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn-professional edit-btn-professional" title="Edit" onclick="editStudent(${student.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn-professional delete-btn-professional" title="Delete" onclick="deleteStudent(${student.id})">
                            <i class="fas fa-trash"></i>
                        </button>
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
            const isChecked = this.checked;
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const card = checkbox.closest('.student-card-professional');
                if (card) {
                    card.classList.toggle('selected', isChecked);
                }
            });
            updateBulkActionsButton();
        });
    }

    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.student-card-professional');
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

            updateBulkActionsButton();
        });
    });
}

// Update bulk actions button visibility
function updateBulkActionsButton() {
    const checkedCount = document.querySelectorAll('.student-checkbox:checked, input[name="chk_child"]:checked').length;
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');
    const selectedCountText = document.getElementById('selectedCountText');

    if (bulkActionsBtn) {
        if (checkedCount > 0) {
            bulkActionsBtn.classList.remove('d-none');
            selectedCountText.textContent = `${checkedCount} selected`;
        } else {
            bulkActionsBtn.classList.add('d-none');
            selectedCountText.textContent = '0 selected';
        }
    }
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
            let student = response.data.student || response.data;

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
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-light'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            axios.delete(`/student/${id}/destroy`)
                .then(() => {
                    const card = document.querySelector(`.student-card-professional[data-id="${id}"]`);
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
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the selected students',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const deletePromises = ids.map(id =>
                axios.delete(`/student/${id}/destroy`)
            );

            Promise.all(deletePromises)
                .then(() => {
                    Swal.close();
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
                    Swal.close();
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
// DATA FETCHING AND RENDERING
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
        row.innerHTML = `<td colspan="8" class="text-center py-5">
            <div class="empty-state-professional">
                <i class="fas fa-users-slash"></i>
                <h5>No students found</h5>
                <p>Try adjusting your filters or add a new student</p>
            </div>
        </td>`;
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
                    <div class="avatar avatar-sm me-3">
                        <img src="${studentImage}" alt="" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"/>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">${student.lastname || ''} ${student.firstname || ''}</h6>
                        <small class="text-muted">${student.othername || ''}</small>
                    </div>
                </div>
            </td>
            <td class="admissionNo" data-admissionno="${student.admissionNo || ''}">
                <span class="badge bg-light text-dark">${student.admissionNo || ''}</span>
            </td>
            <td class="class" data-class="${student.schoolclassid || ''}">
                ${student.schoolclass || ''} ${student.arm ? ' - ' + student.arm : ''}
            </td>
            <td class="status" data-status="${student.statusId || ''}">
                <span class="badge ${student.statusId == 1 ? 'bg-info' : 'bg-success'}">
                    ${student.statusId == 1 ? 'Old Student' : 'New Student'}
                </span>
            </td>
            <td class="gender" data-gender="${student.gender || ''}">
                <span class="badge ${student.gender === 'Female' ? 'bg-pink' : 'bg-primary'}">
                    ${student.gender || ''}
                </span>
            </td>
            <td class="datereg">
                ${student.created_at ? new Date(student.created_at).toISOString().split('T')[0] : ''}
            </td>
            <td>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-info" onclick="viewStudent(${student.id})" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="editStudent(${student.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(${student.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    updatePagination();
    initializeCheckboxes();
}

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
// FILTER FUNCTIONS
// ============================================================================

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

// Initialize checkboxes for multiple selection
function initializeCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    const checkAllTable = document.getElementById('checkAllTable');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="chk_child"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsButton();
        });
    }

    if (checkAllTable) {
        checkAllTable.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="chk_child"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsButton();
        });
    }

    document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allCheckboxes = document.querySelectorAll('input[name="chk_child"]');
            const checkedCheckboxes = document.querySelectorAll('input[name="chk_child"]:checked');

            if (checkAll) {
                checkAll.checked = allCheckboxes.length === checkedCheckboxes.length;
                checkAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            }

            if (checkAllTable) {
                checkAllTable.checked = allCheckboxes.length === checkedCheckboxes.length;
            }

            updateBulkActionsButton();
        });
    });
}

// ============================================================================
// CURRENT TERM MANAGEMENT FUNCTIONS
// ============================================================================

function showUpdateCurrentTermModal() {
    const selectedIds = getSelectedStudentIds();

    if (selectedIds.length === 0) {
        Swal.fire({
            title: "No Students Selected!",
            text: "Please select at least one student to update current term.",
            icon: "warning",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    document.getElementById('selectedStudentsCount').textContent = selectedIds.length;

    const modalElement = document.getElementById('updateCurrentTermModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

function getSelectedStudentIds() {
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

    return ids;
}

function updateCurrentTermForSelected() {
    const selectedIds = getSelectedStudentIds();

    if (selectedIds.length === 0) {
        Swal.fire({
            title: "No Students Selected!",
            text: "Please select at least one student.",
            icon: "warning",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    const classId = document.getElementById('currentClass').value;
    const sessionId = document.getElementById('currentSession').value;

    if (!classId || !sessionId) {
        Swal.fire({
            title: "Missing Information!",
            text: "Please select both class and session.",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    Swal.fire({
        title: 'Updating Current Term...',
        text: `Updating current term for ${selectedIds.length} student(s). Please wait...`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    axios.post('/student-current-term/bulk-update', {
        student_ids: selectedIds,
        schoolclassId: classId,
        termId: @json($currentTerm->id ?? 'null'),
        sessionId: sessionId
    })
    .then(response => {
        Swal.close();

        if (response.data.success) {
            const successCount = response.data.summary?.success || 0;
            const failedCount = response.data.summary?.failed || 0;

            const modalElement = document.getElementById('updateCurrentTermModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }

            if (failedCount === 0) {
                Swal.fire({
                    title: 'Success!',
                    text: `Successfully updated current term for all ${successCount} student(s).`,
                    icon: 'success',
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false
                });
            } else {
                Swal.fire({
                    title: 'Partial Success',
                    html: `
                        <div class="text-start">
                            <p><strong>Results:</strong></p>
                            <ul class="mb-0">
                                <li class="text-success">✓ Successfully updated: ${successCount} student(s)</li>
                                <li class="text-danger">✗ Failed to update: ${failedCount} student(s)</li>
                            </ul>
                        </div>
                    `,
                    icon: 'info',
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false
                });
            }

            document.getElementById('checkAll').checked = false;
            document.querySelectorAll('input[name="chk_child"]:checked, .student-checkbox:checked')
                .forEach(checkbox => checkbox.checked = false);
            document.getElementById('bulkActionsBtn').classList.add('d-none');
        } else {
            throw new Error(response.data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('Error updating current term:', error);
        Swal.close();

        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to update current term. Please try again.',
            icon: 'error',
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
    });
}

// ============================================================================
// REPORT MODAL FUNCTIONS
// ============================================================================

let columnSortable = null;

function initializeColumnOrdering() {
    console.log('Initializing column ordering...');

    const columnContainer = document.getElementById('columnsContainer');
    const hiddenOrderInput = document.getElementById('columnsOrderInput');

    if (!columnContainer || !hiddenOrderInput) {
        console.error('Column container or hidden input not found');
        return;
    }

    function updateColumnOrder() {
        console.log('Updating column order...');

        const columnItems = columnContainer.querySelectorAll('.draggable-item');
        const order = [];
        const selectedLabels = [];

        columnItems.forEach(item => {
            const checkbox = item.querySelector('.column-checkbox');
            if (checkbox && checkbox.checked) {
                order.push(checkbox.value);

                const label = item.querySelector('.form-check-label');
                if (label) {
                    selectedLabels.push(label.textContent.trim());
                }
            }
        });

        console.log('New order:', order);
        hiddenOrderInput.value = order.join(',');

        updatePreview();
    }

    if (typeof Sortable !== 'undefined') {
        console.log('Sortable.js loaded, version:', Sortable.version);

        if (columnSortable) {
            columnSortable.destroy();
        }

        columnSortable = new Sortable(columnContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            filter: '.column-checkbox',
            onEnd: function() {
                console.log('Drag ended');
                updateColumnOrder();
            }
        });

        console.log('Sortable.js initialized successfully');
    } else {
        console.error('Sortable.js not loaded!');
        initializeNativeDragDrop();
    }

    columnContainer.querySelectorAll('.column-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('Checkbox changed:', this.value, this.checked);
            updateColumnOrder();
        });
    });

    updateColumnOrder();
}

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

        item.addEventListener('dragend', function() {
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
                this.classList.remove('drag-over');

                const allItems = Array.from(container.querySelectorAll('.draggable-item'));
                const draggedIndex = allItems.indexOf(draggedItem);
                const targetIndex = allItems.indexOf(this);

                if (draggedIndex < targetIndex) {
                    this.parentElement.after(draggedItem.parentElement);
                } else {
                    this.parentElement.before(draggedItem.parentElement);
                }

                updateColumnOrder();
            }
        });
    });
}

function updatePreview() {
    console.log('Updating preview...');

    const container = document.getElementById('columnsContainer');
    if (!container) return;

    const columnItems = container.querySelectorAll('.draggable-item');
    const selectedLabels = [];

    columnItems.forEach(item => {
        const checkbox = item.querySelector('.column-checkbox');
        if (checkbox && checkbox.checked) {
            const label = item.querySelector('.form-check-label');
            if (label) {
                selectedLabels.push(label.textContent.trim());
            }
        }
    });

    const preview = document.getElementById('columnOrderPreview');
    if (preview) {
        preview.textContent = selectedLabels.join(', ') || 'No columns selected';
    }
}

function generateReport() {
    console.log('Generate report clicked');

    const form = document.getElementById('printReportForm');
    if (!form) {
        console.error('Report form not found');
        return;
    }

    const selectedCheckboxes = form.querySelectorAll('input[name="columns[]"]:checked');
    const selectedColumns = Array.from(selectedCheckboxes).map(cb => cb.value);

    console.log('Selected columns:', selectedColumns);

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

    const classId = form.querySelector('[name="class_id"]').value;
    const status = form.querySelector('[name="status"]').value;
    const formatElements = form.querySelectorAll('[name="format"]');
    let format = '';

    formatElements.forEach(element => {
        if (element.checked) {
            format = element.value;
        }
    });

    if (!format) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select an export format (PDF or Excel).',
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
        return;
    }

    const columnsOrderInput = document.getElementById('columnsOrderInput');
    const includeHeader = form.querySelector('[name="include_header"]').checked;
    const includeLogo = form.querySelector('[name="include_logo"]').checked;
    const orientation = form.querySelector('[name="orientation"]').value;
    const termId = form.querySelector('[name="term_id"]')?.value || '';
    const sessionId = form.querySelector('[name="session_id"]')?.value || '';

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

    Swal.fire({
        title: 'Generating Report...',
        text: 'This may take a moment. Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const params = new URLSearchParams({
        class_id: classId || '',
        term_id: termId,
        session_id: sessionId,
        status: status || '',
        columns: selectedColumns.join(','),
        columns_order: columnsOrderInput?.value || '',
        format: format,
        orientation: orientation,
        include_header: includeHeader ? '1' : '0',
        include_logo: includeLogo ? '1' : '0'
    });

    axios.get(`/students/report?${params.toString()}`, {
        responseType: 'blob',
        timeout: 120000
    })
    .then(response => {
        Swal.close();

        const blob = new Blob([response.data], {
            type: response.headers['content-type']
        });

        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

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

        setTimeout(() => {
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 100);

        const modalElement = document.getElementById('printStudentReportModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }

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
            if (error.response.status === 404) {
                errorMessage = 'No students found matching the selected filters.';
            } else if (error.response.status === 422) {
                errorMessage = error.response.data.message || 'Validation error. Please check your selections.';
            } else if (error.response.status === 500) {
                errorMessage = error.response.data?.message || 'Server error. Please try again later.';
            }
        } else if (error.code === 'ECONNABORTED') {
            errorMessage = 'Request timeout. The report generation is taking too long.';
        } else if (error.message) {
            errorMessage = error.message;
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
// INITIALIZATION FUNCTIONS
// ============================================================================

function initializeReportModal() {
    console.log('Initializing report modal...');

    initializeColumnOrdering();

    const container = document.getElementById('columnsContainer');
    if (container) {
        container.querySelectorAll('.column-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updatePreview);
        });
    }

    updatePreview();
}

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
// FORM SUBMISSION HANDLERS
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize states dropdowns
    initializeStatesDropdown('addState', 'addLocal');
    initializeStatesDropdown('editState', 'editLocal');

    // Initialize student list
    initializeStudentList();

    // Add event listener for update current term button
    const confirmButton = document.getElementById('confirmUpdateCurrentTerm');
    if (confirmButton) {
        confirmButton.addEventListener('click', updateCurrentTermForSelected);
    }

    // Initialize report modal
    const reportModal = document.getElementById('printStudentReportModal');
    if (reportModal) {
        reportModal.addEventListener('show.bs.modal', function() {
            console.log('Report modal shown, initializing...');
            setTimeout(initializeReportModal, 100);
        });
    }

    // Initialize generate report button
    const generateBtn = document.getElementById('generateReportBtn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateReport);
    }

    // Handle edit form submission
    const editForm = document.getElementById('editStudentForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

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

            const formData = new FormData(this);
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

            const url = this.action;

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
        });
    }

    // Handle add form submission
    const addForm = document.getElementById('addStudentForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();

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

            const formData = new FormData(this);

            axios.post(this.action, formData, {
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

                            addForm.reset();
                            fetchStudents();
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
        });
    }
});

</script>
@endsection
