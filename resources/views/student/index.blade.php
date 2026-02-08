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
                        <h4 class="mb-sm-0">Student Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <style>
                /* ====== MODERN CARD UI STYLES ====== */
                .dashboard-stats-card {
                    border: none;
                    border-radius: 16px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    transition: all 0.3s ease;
                    margin-bottom: 24px;
                    position: relative;
                    overflow: hidden;
                }

                .dashboard-stats-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
                }

                .dashboard-stats-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
                }

                .dashboard-stats-card .card-body {
                    padding: 24px;
                    position: relative;
                    z-index: 1;
                }

                .dashboard-stats-card .stats-icon {
                    width: 64px;
                    height: 64px;
                    border-radius: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-bottom: 20px;
                    font-size: 28px;
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                    color: white;
                }

                .dashboard-stats-card .stats-content {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                }

                .dashboard-stats-card .stats-label {
                    font-size: 14px;
                    font-weight: 500;
                    color: rgba(255, 255, 255, 0.9);
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .dashboard-stats-card .stats-value {
                    font-size: 32px;
                    font-weight: 700;
                    color: white;
                    line-height: 1;
                }

                .dashboard-stats-card .stats-change {
                    font-size: 12px;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    color: rgba(255, 255, 255, 0.8);
                }

                .dashboard-stats-card .stats-change.positive {
                    color: #10b981;
                }

                .dashboard-stats-card .stats-change.negative {
                    color: #ef4444;
                }

                /* Card color themes */
                .stats-primary {
                    --gradient-start: #4361ee;
                    --gradient-end: #3a0ca3;
                    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
                }

                .stats-success {
                    --gradient-start: #10b981;
                    --gradient-end: #047857;
                    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
                }

                .stats-warning {
                    --gradient-start: #f59e0b;
                    --gradient-end: #b45309;
                    background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
                }

                .stats-info {
                    --gradient-start: #0ea5e9;
                    --gradient-end: #0369a1;
                    background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
                }

                .stats-purple {
                    --gradient-start: #8b5cf6;
                    --gradient-end: #7c3aed;
                    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                }

                .stats-pink {
                    --gradient-start: #ec4899;
                    --gradient-end: #be185d;
                    background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
                }

                .stats-teal {
                    --gradient-start: #14b8a6;
                    --gradient-end: #0d9488;
                    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
                }

                /* ====== PROFESSIONAL STUDENT CARD STYLES ====== */
                .student-profile-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 16px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                    background: white;
                    height: 100%;
                    position: relative;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .student-profile-card:hover {
                    border-color: #3b82f6;
                    box-shadow: 0 8px 30px rgba(59, 130, 246, 0.15);
                    transform: translateY(-4px);
                }

                .student-profile-card.selected {
                    border-color: #3b82f6;
                    background-color: #f0f9ff;
                }

                .student-profile-card .card-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 20px;
                    position: relative;
                    min-height: 120px;
                }

                .student-profile-card .avatar-container {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    width: 80px;
                    height: 80px;
                    border-radius: 16px;
                    overflow: hidden;
                    border: 4px solid white;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    background: white;
                }

                .student-profile-card .avatar {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .student-profile-card .avatar-initials {
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: 700;
                    color: #667eea;
                    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
                }

                .student-profile-card .header-content {
                    padding-right: 100px;
                }

                .student-profile-card .student-name {
                    font-size: 20px;
                    font-weight: 700;
                    color: white;
                    margin-bottom: 4px;
                    line-height: 1.2;
                }

                .student-profile-card .student-admission {
                    font-size: 13px;
                    color: rgba(255, 255, 255, 0.9);
                    background: rgba(255, 255, 255, 0.1);
                    padding: 4px 12px;
                    border-radius: 20px;
                    display: inline-block;
                    backdrop-filter: blur(10px);
                }

                .student-profile-card .card-body {
                    padding: 20px;
                }

                .student-profile-card .student-info-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px;
                    margin-bottom: 20px;
                }

                .student-profile-card .info-item {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }

                .student-profile-card .info-label {
                    font-size: 11px;
                    font-weight: 600;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .student-profile-card .info-value {
                    font-size: 14px;
                    font-weight: 600;
                    color: #374151;
                }

                .student-profile-card .status-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    margin-bottom: 16px;
                }

                .student-profile-card .status-active {
                    background-color: #d1fae5;
                    color: #065f46;
                    border: 1px solid #a7f3d0;
                }

                .student-profile-card .status-inactive {
                    background-color: #fee2e2;
                    color: #991b1b;
                    border: 1px solid #fecaca;
                }

                .student-profile-card .status-new {
                    background-color: #dbeafe;
                    color: #1e40af;
                    border: 1px solid #bfdbfe;
                }

                .student-profile-card .status-old {
                    background-color: #fef3c7;
                    color: #92400e;
                    border: 1px solid #fde68a;
                }

                .student-profile-card .action-buttons {
                    display: flex;
                    gap: 8px;
                    padding-top: 16px;
                    border-top: 1px solid #e5e7eb;
                }

                .student-profile-card .action-btn {
                    flex: 1;
                    padding: 10px;
                    border-radius: 12px;
                    border: none;
                    font-size: 13px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                }

                .student-profile-card .view-btn {
                    background-color: #3b82f6;
                    color: white;
                }

                .student-profile-card .view-btn:hover {
                    background-color: #2563eb;
                    transform: translateY(-2px);
                }

                .student-profile-card .edit-btn {
                    background-color: #f3f4f6;
                    color: #374151;
                    border: 1px solid #e5e7eb;
                }

                .student-profile-card .edit-btn:hover {
                    background-color: #e5e7eb;
                    transform: translateY(-2px);
                }

                .student-profile-card .delete-btn {
                    background-color: #fef2f2;
                    color: #dc2626;
                    border: 1px solid #fee2e2;
                }

                .student-profile-card .delete-btn:hover {
                    background-color: #fee2e2;
                    transform: translateY(-2px);
                }

                .student-profile-card .checkbox-container {
                    position: absolute;
                    top: 16px;
                    left: 16px;
                    z-index: 2;
                }

                .student-profile-card .form-check-input {
                    width: 20px;
                    height: 20px;
                    cursor: pointer;
                    border: 2px solid white;
                    background-color: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                }

                .student-profile-card .form-check-input:checked {
                    background-color: #3b82f6;
                    border-color: #3b82f6;
                }

                /* ====== TABLE STYLES ====== */
                .data-table-container {
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .data-table {
                    margin-bottom: 0;
                }

                .data-table thead {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }

                .data-table thead th {
                    border: none;
                    color: white;
                    font-weight: 600;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    padding: 16px 12px;
                }

                .data-table tbody tr {
                    transition: all 0.2s ease;
                    border-bottom: 1px solid #e5e7eb;
                }

                .data-table tbody tr:hover {
                    background-color: #f9fafb;
                }

                .data-table tbody tr.selected {
                    background-color: #f0f9ff;
                }

                .data-table tbody td {
                    padding: 16px 12px;
                    vertical-align: middle;
                    border: none;
                }

                /* ====== ACTION BUTTONS ====== */
                .btn-group-toggle .btn {
                    border-radius: 12px;
                    padding: 10px 20px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .btn-group-toggle .btn.active {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-color: #667eea;
                    color: white;
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                }

                .btn-primary-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    color: white;
                    padding: 12px 24px;
                    border-radius: 12px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .btn-primary-gradient:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
                }

                /* ====== FILTER BAR ====== */
                .filter-bar {
                    background: white;
                    padding: 20px;
                    border-radius: 16px;
                    margin-bottom: 24px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .search-box {
                    position: relative;
                }

                .search-box input {
                    padding-left: 44px;
                    border-radius: 12px;
                    border: 1px solid #e5e7eb;
                    height: 48px;
                    font-size: 14px;
                    transition: all 0.3s ease;
                }

                .search-box input:focus {
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }

                .search-box .search-icon {
                    position: absolute;
                    left: 16px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #9ca3af;
                    font-size: 18px;
                }

                /* ====== PAGINATION ====== */
                .pagination-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    background: white;
                    border-top: 1px solid #e5e7eb;
                }

                .pagination .page-link {
                    border: none;
                    color: #374151;
                    margin: 0 4px;
                    border-radius: 10px;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .pagination .page-link:hover {
                    background-color: #f3f4f6;
                    color: #667eea;
                }

                .pagination .page-item.active .page-link {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }

                /* ====== EMPTY STATE ====== */
                .empty-state {
                    padding: 60px 20px;
                    text-align: center;
                }

                .empty-state-icon {
                    font-size: 64px;
                    color: #d1d5db;
                    margin-bottom: 20px;
                }

                .empty-state-title {
                    font-size: 20px;
                    font-weight: 600;
                    color: #374151;
                    margin-bottom: 8px;
                }

                .empty-state-description {
                    color: #6b7280;
                    font-size: 14px;
                    max-width: 400px;
                    margin: 0 auto 24px;
                }

                /* ====== LOADING STATE ====== */
                .loading-state {
                    padding: 60px 20px;
                    text-align: center;
                }

                .spinner-container {
                    display: inline-block;
                    position: relative;
                    width: 80px;
                    height: 80px;
                }

                .spinner-ring {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    border: 4px solid #f3f4f6;
                    border-top-color: #667eea;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                /* ====== MODAL STYLES ====== */
                .modal-xl .modal-content {
                    border-radius: 20px;
                    overflow: hidden;
                    border: none;
                }

                .modal-header-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 24px 32px;
                    border: none;
                }

                .modal-header-gradient .modal-title {
                    font-size: 20px;
                    font-weight: 700;
                }

                .modal-header-gradient .btn-close {
                    filter: brightness(0) invert(1);
                    opacity: 0.8;
                }

                .modal-header-gradient .btn-close:hover {
                    opacity: 1;
                }
            </style>

            <!-- Dashboard Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-primary">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Total Students</span>
                                <span class="stats-value">{{ $total_population }}</span>
                                <span class="stats-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    12% from last term
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-success">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Active Students</span>
                                <span class="stats-value">{{ $student_status_counts['Active'] }}</span>
                                <span class="stats-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    8% from last term
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-warning">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">New Admissions</span>
                                <span class="stats-value">{{ $status_counts['New Student'] }}</span>
                                <span class="stats-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    15% from last term
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-purple">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Staff Count</span>
                                <span class="stats-value">{{ $staff_count }}</span>
                                <span class="stats-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    5% from last term
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gender and Religion Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-info">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-mars"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Male Students</span>
                                <span class="stats-value">{{ $gender_counts['Male'] }}</span>
                                <span class="stats-change">
                                    {{ number_format(($gender_counts['Male'] / $total_population) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-pink">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-venus"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Female Students</span>
                                <span class="stats-value">{{ $gender_counts['Female'] }}</span>
                                <span class="stats-change">
                                    {{ number_format(($gender_counts['Female'] / $total_population) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-teal">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-cross"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Christians</span>
                                <span class="stats-value">{{ $religion_counts['Christianity'] }}</span>
                                <span class="stats-change">
                                    {{ number_format(($religion_counts['Christianity'] / $total_population) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-warning">
                        <div class="card-body">
                            <div class="stats-icon">
                                <i class="fas fa-moon"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-label">Muslims</span>
                                <span class="stats-value">{{ $religion_counts['Islam'] }}</span>
                                <span class="stats-change">
                                    {{ number_format(($religion_counts['Islam'] / $total_population) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Validation Error!</strong> Please check the form for errors.
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="data-table-container">
                <!-- Card Header -->
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                            <label class="form-check-label" for="checkAll"></label>
                        </div>
                        <h5 class="mb-0 fw-bold">Student Records</h5>
                        <span class="badge bg-primary bg-gradient rounded-pill" id="totalStudents">0</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- View Toggle -->
                        <div class="btn-group btn-group-toggle" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="tableViewBtn">
                                <i class="fas fa-table me-2"></i>Table
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="cardViewBtn">
                                <i class="fas fa-th-large me-2"></i>Cards
                            </button>
                        </div>

                        <!-- Bulk Actions -->
                        @can('Delete student')
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="bulkActionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-2"></i>Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteMultiple()">
                                        <i class="fas fa-trash me-2"></i>Delete Selected
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-primary" href="javascript:void(0);" onclick="showUpdateCurrentTermModal()">
                                        <i class="fas fa-calendar-alt me-2"></i>Update Current Term
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endcan

                        <!-- Add Student Button -->
                        @can('Create student')
                        <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-user-plus me-2"></i>Add Student
                        </button>
                        @endcan

                        <!-- Export Button -->
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#printStudentReportModal">
                            <i class="fas fa-file-export me-2"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control" id="search-input"
                                       placeholder="Search name or admission number...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="schoolclass-filter">
                                <option value="all">All Classes</option>
                                @foreach ($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="status-filter">
                                <option value="all">All Status</option>
                                <option value="1">Old Student</option>
                                <option value="2">New Student</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="gender-filter">
                                <option value="all">All Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" onclick="filterData()">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div id="tableView" class="view-container">
                    <div class="table-responsive">
                        <table class="table data-table" id="studentTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="option" id="checkAllTable">
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
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="view-container d-none p-4">
                    <div class="row" id="studentsCardsContainer">
                        <!-- Cards will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Empty/Loading States -->
                <div id="emptyState" class="empty-state d-none">
                    <div class="empty-state-icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h5 class="empty-state-title">No Students Found</h5>
                    <p class="empty-state-description">
                        Try adjusting your search or filter to find what you're looking for.
                    </p>
                    <button class="btn btn-primary-gradient" onclick="resetFilters()">
                        <i class="fas fa-redo me-2"></i>Reset Filters
                    </button>
                </div>

                <div id="loadingState" class="loading-state d-none">
                    <div class="spinner-container">
                        <div class="spinner-ring"></div>
                    </div>
                    <p class="mt-3 text-muted">Loading students...</p>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div>
                        <span class="text-muted">
                            Showing <span class="fw-bold" id="showingCount">0</span> of
                            <span class="fw-bold" id="totalCount">0</span> students
                        </span>
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item">
                                <a class="page-link" href="javascript:void(0);" id="prevPage">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <span class="page-link" id="currentPage">1</span>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="javascript:void(0);" id="nextPage">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Update Current Term Modal -->
        <div id="updateCurrentTermModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-alt me-2"></i>Update Current Term
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="updateCurrentTermForm">
                            @csrf
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Updating current term for <span id="selectedStudentsCount">0</span> selected student(s).
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Class</label>
                                <select class="form-control" name="schoolclassId" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Session</label>
                                <select class="form-control" name="sessionId" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This action will update the current term for all selected students. This cannot be undone.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary-gradient" id="confirmUpdateCurrentTerm">
                            <i class="fas fa-save me-2"></i>Update Current Term
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print/Export Report Modal -->
        <div id="printStudentReportModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-file-export me-2"></i>Generate Report
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Form will be populated based on your existing structure -->
                        <p class="text-center text-muted">Report generation options will appear here</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary-gradient">Generate Report</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i>Register New Student
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addStudentForm" enctype="multipart/form-data" method="POST" action="{{ route('student.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <!-- Form content from original -->
                            <!-- Your existing form structure here -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Please fill in all required fields marked with *
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-gradient">
                                <i class="fas fa-save me-2"></i>Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Student
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editStudentForm" enctype="multipart/form-data" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" id="editStudentId" name="id">
                        <div class="modal-body p-4">
                            <!-- Form content from original -->
                            <!-- Your existing form structure here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-gradient">
                                <i class="fas fa-save me-2"></i>Update Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Student Modal -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-eye me-2"></i>Student Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Student details will be populated by JavaScript -->
                        <div id="viewStudentContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary-gradient" onclick="editStudentFromView()">
                            <i class="fas fa-edit me-2"></i>Edit Student
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// STUDENT MANAGEMENT SYSTEM - ENHANCED VERSION
// ============================================================================

// Global Variables
let allStudents = [];
let currentPage = 1;
const itemsPerPage = 12;
let currentView = 'table';
let currentFilter = {
    search: '',
    class: 'all',
    status: 'all',
    gender: 'all'
};

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeApplication();
});

// Initialize Application
function initializeApplication() {
    // Load initial data
    fetchStudents();

    // Initialize event listeners
    initializeEventListeners();

    // Initialize UI components
    initializeUIComponents();
}

// Initialize Event Listeners
function initializeEventListeners() {
    // View toggle
    document.getElementById('tableViewBtn').addEventListener('click', () => toggleView('table'));
    document.getElementById('cardViewBtn').addEventListener('click', () => toggleView('card'));

    // Search and filter
    document.getElementById('search-input').addEventListener('input', debounce(filterData, 300));
    document.getElementById('schoolclass-filter').addEventListener('change', filterData);
    document.getElementById('status-filter').addEventListener('change', filterData);
    document.getElementById('gender-filter').addEventListener('change', filterData);

    // Checkboxes
    document.getElementById('checkAll').addEventListener('change', toggleSelectAll);
    document.getElementById('checkAllTable').addEventListener('change', toggleSelectAll);

    // Pagination
    document.getElementById('prevPage').addEventListener('click', goToPrevPage);
    document.getElementById('nextPage').addEventListener('click', goToNextPage);

    // Update current term
    document.getElementById('confirmUpdateCurrentTerm').addEventListener('click', updateCurrentTerm);

    // Form submissions
    document.getElementById('addStudentForm')?.addEventListener('submit', handleAddStudent);
    document.getElementById('editStudentForm')?.addEventListener('submit', handleEditStudent);
}

// Initialize UI Components
function initializeUIComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize form validation
    initializeFormValidation();
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Toggle View Function
function toggleView(viewType) {
    currentView = viewType;
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');

    if (viewType === 'table') {
        tableView.classList.remove('d-none');
        cardView.classList.add('d-none');
        tableViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');
    } else {
        tableView.classList.add('d-none');
        cardView.classList.remove('d-none');
        tableViewBtn.classList.remove('active');
        cardViewBtn.classList.add('active');
    }

    renderCurrentView();
}

// Render Current View
function renderCurrentView() {
    const filteredStudents = filterStudents();
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageStudents = filteredStudents.slice(startIndex, endIndex);

    if (currentView === 'table') {
        renderTableView(pageStudents);
    } else {
        renderCardView(pageStudents);
    }

    updatePagination(filteredStudents.length);
    updateCounts(filteredStudents.length, pageStudents.length);
}

// Filter Students
function filterStudents() {
    const searchTerm = currentFilter.search.toLowerCase();
    const classFilter = currentFilter.class;
    const statusFilter = currentFilter.status;
    const genderFilter = currentFilter.gender;

    return allStudents.filter(student => {
        // Search filter
        const searchMatch = !searchTerm ||
            student.firstname?.toLowerCase().includes(searchTerm) ||
            student.lastname?.toLowerCase().includes(searchTerm) ||
            student.admissionNo?.toLowerCase().includes(searchTerm);

        // Class filter
        const classMatch = classFilter === 'all' || student.schoolclassid == classFilter;

        // Status filter
        const statusMatch = statusFilter === 'all' ||
            (statusFilter === '1' && student.statusId == 1) ||
            (statusFilter === '2' && student.statusId == 2) ||
            (statusFilter === 'Active' && student.student_status === 'Active') ||
            (statusFilter === 'Inactive' && student.student_status === 'Inactive');

        // Gender filter
        const genderMatch = genderFilter === 'all' || student.gender === genderFilter;

        return searchMatch && classMatch && statusMatch && genderMatch;
    });
}

// Filter Data
function filterData() {
    currentFilter = {
        search: document.getElementById('search-input').value,
        class: document.getElementById('schoolclass-filter').value,
        status: document.getElementById('status-filter').value,
        gender: document.getElementById('gender-filter').value
    };

    currentPage = 1;
    renderCurrentView();
}

// Reset Filters
function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('schoolclass-filter').value = 'all';
    document.getElementById('status-filter').value = 'all';
    document.getElementById('gender-filter').value = 'all';

    currentFilter = {
        search: '',
        class: 'all',
        status: 'all',
        gender: 'all'
    };

    currentPage = 1;
    renderCurrentView();
}

// Toggle Select All
function toggleSelectAll(e) {
    const isChecked = e.target.checked;
    const checkboxes = document.querySelectorAll('.student-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
        const parent = checkbox.closest('.student-profile-card, tr');
        if (parent) {
            parent.classList.toggle('selected', isChecked);
        }
    });

    updateBulkActionsVisibility();
}

// Update Bulk Actions Visibility
function updateBulkActionsVisibility() {
    const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
    const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');

    if (selectedCount > 0) {
        bulkActionsDropdown.disabled = false;
        bulkActionsDropdown.innerHTML = `<i class="fas fa-cog me-2"></i>Actions (${selectedCount})`;
    } else {
        bulkActionsDropdown.disabled = true;
        bulkActionsDropdown.innerHTML = `<i class="fas fa-cog me-2"></i>Actions`;
    }
}

// Fetch Students
async function fetchStudents() {
    showLoading();

    try {
        const response = await axios.get('/students/data');
        allStudents = response.data.students || response.data.data || response.data || [];

        renderCurrentView();
    } catch (error) {
        console.error('Error fetching students:', error);
        showError('Failed to load students. Please try again.');
    } finally {
        hideLoading();
    }
}

// Show Loading
function showLoading() {
    document.getElementById('loadingState').classList.remove('d-none');
    document.getElementById('tableView').classList.add('d-none');
    document.getElementById('cardView').classList.add('d-none');
    document.getElementById('emptyState').classList.add('d-none');
}

// Hide Loading
function hideLoading() {
    document.getElementById('loadingState').classList.add('d-none');
}

// Show Error
function showError(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'btn btn-primary'
        }
    });
}

// Render Table View
function renderTableView(students) {
    const tbody = document.getElementById('studentTableBody');

    if (students.length === 0) {
        tbody.innerHTML = '';
        document.getElementById('emptyState').classList.remove('d-none');
        return;
    }

    document.getElementById('emptyState').classList.add('d-none');

    tbody.innerHTML = students.map(student => `
        <tr data-id="${student.id}">
            <td>
                <div class="form-check">
                    <input class="form-check-input student-checkbox" type="checkbox"
                           value="${student.id}" onchange="updateBulkActionsVisibility()">
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        ${getStudentAvatar(student)}
                    </div>
                    <div>
                        <h6 class="mb-0">${student.firstname} ${student.lastname}</h6>
                        <small class="text-muted">${student.othername || ''}</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-light text-dark">${student.admissionNo}</span>
            </td>
            <td>${student.schoolclass || ''} ${student.arm || ''}</td>
            <td>
                ${getStatusBadge(student)}
            </td>
            <td>
                <span class="badge bg-${student.gender === 'Male' ? 'primary' : 'pink'} bg-gradient">
                    ${student.gender}
                </span>
            </td>
            <td>${formatDate(student.created_at)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewStudent(${student.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="editStudent(${student.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteStudent(${student.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render Card View
function renderCardView(students) {
    const container = document.getElementById('studentsCardsContainer');

    if (students.length === 0) {
        container.innerHTML = '';
        document.getElementById('emptyState').classList.remove('d-none');
        return;
    }

    document.getElementById('emptyState').classList.add('d-none');

    container.innerHTML = students.map(student => `
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="student-profile-card" data-id="${student.id}">
                <div class="checkbox-container">
                    <div class="form-check">
                        <input class="form-check-input student-checkbox" type="checkbox"
                               value="${student.id}" onchange="updateBulkActionsVisibility()">
                    </div>
                </div>

                <div class="card-header">
                    <div class="header-content">
                        <h5 class="student-name">${student.firstname} ${student.lastname}</h5>
                        <span class="student-admission">${student.admissionNo}</span>
                    </div>
                    <div class="avatar-container">
                        ${getStudentAvatar(student, true)}
                    </div>
                </div>

                <div class="card-body">
                    ${getStatusBadge(student, true)}

                    <div class="student-info-grid">
                        <div class="info-item">
                            <span class="info-label">Class</span>
                            <span class="info-value">${student.schoolclass || ''} ${student.arm || ''}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender</span>
                            <span class="info-value">${student.gender}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age</span>
                            <span class="info-value">${calculateAge(student.dateofbirth)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Registered</span>
                            <span class="info-value">${formatDate(student.created_at, 'short')}</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="action-btn view-btn" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn edit-btn" onclick="editStudent(${student.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteStudent(${student.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Get Student Avatar
function getStudentAvatar(student, isCard = false) {
    const initials = `${student.firstname?.charAt(0) || ''}${student.lastname?.charAt(0) || ''}`;
    const size = isCard ? '80px' : '40px';

    if (student.picture && student.picture !== 'unnamed.jpg') {
        return `
            <img src="/storage/images/student_avatars/${student.picture}"
                 alt="${student.firstname}"
                 class="avatar"
                 style="width: ${size}; height: ${size}; object-fit: cover;">
        `;
    }

    return `
        <div class="avatar-initials" style="width: ${size}; height: ${size};">
            ${initials}
        </div>
    `;
}

// Get Status Badge
function getStatusBadge(student, isCard = false) {
    let badge = '';

    if (student.student_status === 'Active') {
        badge = `<span class="status-badge status-active">
                    <i class="fas fa-check-circle"></i> Active
                </span>`;
    } else if (student.student_status === 'Inactive') {
        badge = `<span class="status-badge status-inactive">
                    <i class="fas fa-pause-circle"></i> Inactive
                </span>`;
    }

    if (student.statusId == 2) {
        badge += `<span class="status-badge status-new ms-2">
                    <i class="fas fa-star"></i> New
                </span>`;
    } else if (student.statusId == 1) {
        badge += `<span class="status-badge status-old ms-2">
                    <i class="fas fa-history"></i> Old
                </span>`;
    }

    return badge;
}

// Calculate Age
function calculateAge(dateOfBirth) {
    if (!dateOfBirth) return 'N/A';
    const dob = new Date(dateOfBirth);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    return age;
}

// Format Date
function formatDate(dateString, format = 'long') {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    const options = format === 'short' ?
        { year: 'numeric', month: 'short', day: 'numeric' } :
        { year: 'numeric', month: 'long', day: 'numeric' };

    return date.toLocaleDateString('en-US', options);
}

// Update Pagination
function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const currentPageSpan = document.getElementById('currentPage');

    currentPageSpan.textContent = currentPage;

    prevBtn.classList.toggle('disabled', currentPage === 1);
    nextBtn.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);
}

// Update Counts
function updateCounts(total, showing) {
    document.getElementById('totalStudents').textContent = total;
    document.getElementById('totalCount').textContent = total;
    document.getElementById('showingCount').textContent = showing;
}

// Pagination Functions
function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderCurrentView();
    }
}

function goToNextPage() {
    const filteredStudents = filterStudents();
    const totalPages = Math.ceil(filteredStudents.length / itemsPerPage);

    if (currentPage < totalPages) {
        currentPage++;
        renderCurrentView();
    }
}

// Student CRUD Operations
async function viewStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        const student = response.data.student || response.data;

        showStudentDetails(student);
    } catch (error) {
        showError('Failed to load student details.');
    }
}

async function editStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        const student = response.data.student || response.data;

        populateEditForm(student);
        showEditModal();
    } catch (error) {
        showError('Failed to load student for editing.');
    }
}

async function deleteStudent(id) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-light'
        }
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/student/${id}/destroy`);

            // Remove from UI
            allStudents = allStudents.filter(s => s.id != id);
            renderCurrentView();

            Swal.fire({
                title: 'Deleted!',
                text: 'Student has been deleted.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } catch (error) {
            showError('Failed to delete student.');
        }
    }
}

// Delete Multiple Students
async function deleteMultiple() {
    const selectedIds = getSelectedStudentIds();

    if (selectedIds.length === 0) {
        Swal.fire({
            title: 'No Selection',
            text: 'Please select at least one student to delete.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    const result = await Swal.fire({
        title: `Delete ${selectedIds.length} Students?`,
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-light'
        }
    });

    if (result.isConfirmed) {
        try {
            const deletePromises = selectedIds.map(id =>
                axios.delete(`/student/${id}/destroy`)
            );

            await Promise.all(deletePromises);

            // Remove from UI
            allStudents = allStudents.filter(s => !selectedIds.includes(s.id.toString()));
            renderCurrentView();

            Swal.fire({
                title: 'Deleted!',
                text: `${selectedIds.length} student(s) have been deleted.`,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } catch (error) {
            showError('Failed to delete selected students.');
        }
    }
}

// Get Selected Student IDs
function getSelectedStudentIds() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Update Current Term Functions
function showUpdateCurrentTermModal() {
    const selectedIds = getSelectedStudentIds();

    if (selectedIds.length === 0) {
        Swal.fire({
            title: 'No Selection',
            text: 'Please select at least one student.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    document.getElementById('selectedStudentsCount').textContent = selectedIds.length;

    const modal = new bootstrap.Modal(document.getElementById('updateCurrentTermModal'));
    modal.show();
}

async function updateCurrentTerm() {
    const selectedIds = getSelectedStudentIds();
    const form = document.getElementById('updateCurrentTermForm');
    const formData = new FormData(form);

    formData.append('student_ids', JSON.stringify(selectedIds));

    try {
        await axios.post('/student-current-term/bulk-update', formData);

        const modal = bootstrap.Modal.getInstance(document.getElementById('updateCurrentTermModal'));
        modal.hide();

        Swal.fire({
            title: 'Success!',
            text: `Current term updated for ${selectedIds.length} student(s).`,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    } catch (error) {
        showError('Failed to update current term.');
    }
}

// Form Handlers
async function handleAddStudent(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await axios.post(form.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
            modal.hide();

            // Refresh student list
            await fetchStudents();

            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student registered successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        handleFormError(error, 'add');
    }
}

async function handleEditStudent(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await axios.post(form.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
            modal.hide();

            // Refresh student list
            await fetchStudents();

            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        handleFormError(error, 'edit');
    }
}

// Handle Form Errors
function handleFormError(error, formType) {
    let errorMessage = 'Failed to save student.';

    if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
    }

    if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        let errorList = '';
        for (const field in errors) {
            errorList += `<li>${errors[field].join(', ')}</li>`;
        }
        errorMessage = `<div class="text-start">
            <strong>Validation Errors:</strong>
            <ul class="mb-0">${errorList}</ul>
        </div>`;
    }

    Swal.fire({
        title: 'Error!',
        html: errorMessage,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

// Initialize Form Validation
function initializeFormValidation() {
    // Add your form validation logic here
    // This is a placeholder for form validation initialization
}

// Show Student Details Modal
function showStudentDetails(student) {
    const content = `
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="avatar-container" style="width: 150px; height: 150px; margin: 0 auto;">
                    ${getStudentAvatar(student, true)}
                </div>
                <h4 class="mt-3">${student.firstname} ${student.lastname}</h4>
                <p class="text-muted">${student.admissionNo}</p>
                ${getStatusBadge(student, true)}
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Date of Birth</label>
                        <p class="fw-bold">${formatDate(student.dateofbirth)}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Gender</label>
                        <p class="fw-bold">${student.gender}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Class</label>
                        <p class="fw-bold">${student.schoolclass || ''} ${student.arm || ''}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Religion</label>
                        <p class="fw-bold">${student.religion || 'N/A'}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p class="fw-bold">${student.permanent_address || 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('viewStudentContent').innerHTML = content;
    const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
    modal.show();
}

// Populate Edit Form
function populateEditForm(student) {
    // Populate form fields with student data
    // This is a simplified version - you should populate all fields
    document.getElementById('editStudentId').value = student.id;
    document.getElementById('editAdmissionNo').value = student.admissionNo;
    document.getElementById('editFirstname').value = student.firstname;
    document.getElementById('editLastname').value = student.lastname;
    document.getElementById('editStudentForm').action = `/student/${student.id}`;

    // Populate other fields as needed
}

// Show Edit Modal
function showEditModal() {
    const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
    modal.show();
}

// Edit Student from View
function editStudentFromView() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'));
    modal.hide();

    // Get the student ID from somewhere (you might need to store it)
    // For now, we'll just show a message
    setTimeout(() => {
        Swal.fire({
            title: 'Edit Student',
            text: 'Please select a student to edit from the list.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }, 300);
}

</script>
@endsection
