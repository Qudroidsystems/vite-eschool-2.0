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
// COMPLETE STUDENT MANAGEMENT JAVASCRIPT
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
let columnSortable = null;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initializeApplication();

    // Initialize admission numbers
    if (document.getElementById('admissionYear')) {
        updateAdmissionNumber();
    }
    if (document.getElementById('editAdmissionYear')) {
        updateAdmissionNumber('edit');
    }
});

// Initialize Application
function initializeApplication() {
    fetchStudents();
    initializeEventListeners();
    initializeUIComponents();
    initializeStatesDropdowns();

    // Report modal initialization
    const reportModal = document.getElementById('printStudentReportModal');
    if (reportModal) {
        reportModal.addEventListener('show.bs.modal', function() {
            setTimeout(initializeReportModal, 100);
        });
    }
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
    const confirmUpdateBtn = document.getElementById('confirmUpdateCurrentTerm');
    if (confirmUpdateBtn) {
        confirmUpdateBtn.addEventListener('click', updateCurrentTermForSelected);
    }

    // Form submissions
    const addForm = document.getElementById('addStudentForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddStudent);
    }

    const editForm = document.getElementById('editStudentForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditStudent);
    }

    // Admission mode toggles
    document.querySelectorAll('input[name="admissionMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            toggleAdmissionInput(this.name.includes('edit') ? 'edit' : '');
        });
    });

    // Admission year changes
    document.getElementById('admissionYear')?.addEventListener('change', () => updateAdmissionNumber());
    document.getElementById('editAdmissionYear')?.addEventListener('change', () => updateAdmissionNumber('edit'));

    // Date of birth age calculation
    document.getElementById('addDOB')?.addEventListener('change', function() {
        calculateAge(this.value, 'addAgeInput');
    });

    document.getElementById('editDOB')?.addEventListener('change', function() {
        calculateAge(this.value, 'editAgeInput');
    });

    // Image preview
    document.getElementById('avatar')?.addEventListener('change', function() {
        previewImage(this, 'addStudentAvatar');
    });

    document.getElementById('editAvatar')?.addEventListener('change', function() {
        previewImage(this, 'editStudentAvatar');
    });
}

// Initialize UI Components
function initializeUIComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Choices.js for select elements
    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('[data-choices]').forEach(select => {
            new Choices(select, {
                searchEnabled: select.dataset.choicesSearchFalse ? false : true,
                itemSelectText: '',
                shouldSort: false
            });
        });
    }
}

// Initialize States Dropdowns
function initializeStatesDropdowns() {
    if (document.getElementById('addState') && document.getElementById('addLocal')) {
        initializeStatesDropdown('addState', 'addLocal');
    }
    if (document.getElementById('editState') && document.getElementById('editLocal')) {
        initializeStatesDropdown('editState', 'editLocal');
    }
}

// ============================================================================
// ADMISSION NUMBER FUNCTIONS
// ============================================================================

// Update admission number based on year selection
function updateAdmissionNumber(prefix = '') {
    const yearSelect = document.getElementById(`${prefix}admissionYear`);
    const admissionNoInput = document.getElementById(`${prefix}admissionNo`);

    if (!yearSelect || !admissionNoInput) return;

    const year = yearSelect.value;
    const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? '[id^="edit"]' : ''}`);

    if (admissionMode && admissionMode.value === 'auto') {
        admissionNoInput.readOnly = true;
        generateAutoAdmissionNumber(year, admissionNoInput);
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value) {
            admissionNoInput.value = `TCC/${year}/0001`;
        }
    }
}

// Generate auto admission number
async function generateAutoAdmissionNumber(year, inputElement) {
    try {
        const response = await axios.get(`/students/last-admission-number?year=${year}`);
        if (response.data.success) {
            inputElement.value = response.data.admissionNo;
        } else {
            inputElement.value = `TCC/${year}/0001`;
        }
    } catch (error) {
        console.error('Error generating admission number:', error);
        inputElement.value = `TCC/${year}/0001`;
    }
}

// Toggle admission input based on mode
function toggleAdmissionInput(prefix = '') {
    updateAdmissionNumber(prefix);
}

// ============================================================================
// FORM FUNCTIONS
// ============================================================================

// Calculate age from date of birth
function calculateAge(dateValue, targetId) {
    if (!dateValue) return;

    const dob = new Date(dateValue);
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
}

// Preview image before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';

            // Hide initials if showing
            const initialsDiv = preview.nextElementSibling;
            if (initialsDiv && initialsDiv.classList.contains('avatar-initials')) {
                initialsDiv.style.display = 'none';
            }
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// ============================================================================
// STATE AND LGA MANAGEMENT
// ============================================================================

// Nigerian states data
const nigerianStates = [
    // ... (same states data as before)
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

        // Set LGA after a short delay to ensure LGAs are populated
        setTimeout(() => {
            lgaSelect.value = lgaName;
        }, 100);
    }
}

// ============================================================================
// STUDENT DATA FUNCTIONS
// ============================================================================

// Fetch students from server
async function fetchStudents() {
    showLoading();

    try {
        const response = await axios.get('/students/data');
        console.log('API Response:', response.data);

        // Parse response data
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
            // Try to extract students from object
            studentsArray = Object.values(response.data).filter(item =>
                item && (item.id || item.student_id)
            );
        }

        // Transform student data
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
            dateofbirth: student.dateofbirth || student.dob || '',
            religion: student.religion || '',
            permanent_address: student.permanent_address || student.address || '',
            phone_number: student.phone_number || student.phone || '',
            email: student.email || '',
            state: student.state || '',
            local: student.local || '',
            father_name: student.father_name || '',
            mother_name: student.mother_name || '',
            father_phone: student.father_phone || '',
            last_school: student.last_school || '',
            last_class: student.last_class || '',
            reason_for_leaving: student.reason_for_leaving || '',
            schoolhouseid: student.schoolhouseid || student.house_id || '',
            student_category: student.student_category || student.category || ''
        }));

        console.log('Processed students:', allStudents.length);

        renderCurrentView();
    } catch (error) {
        console.error('Error fetching students:', error);
        showError('Failed to load students. Please try again.');
        renderCurrentView(); // Render empty state
    } finally {
        hideLoading();
    }
}

// Show loading state
function showLoading() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');

    if (loadingState) loadingState.classList.remove('d-none');
    if (emptyState) emptyState.classList.add('d-none');
}

// Hide loading state
function hideLoading() {
    const loadingState = document.getElementById('loadingState');
    if (loadingState) loadingState.classList.add('d-none');
}

// Show error message
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

// ============================================================================
// VIEW RENDERING FUNCTIONS
// ============================================================================

// Toggle between table and card view
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

// Render current view based on filters
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
    updateEmptyState(filteredStudents.length === 0);
}

// Filter students based on current filters
function filterStudents() {
    const searchTerm = currentFilter.search.toLowerCase();
    const classFilter = currentFilter.class;
    const statusFilter = currentFilter.status;
    const genderFilter = currentFilter.gender;

    return allStudents.filter(student => {
        // Search filter
        const fullName = `${student.firstname || ''} ${student.lastname || ''} ${student.othername || ''}`.toLowerCase();
        const searchMatch = !searchTerm ||
            fullName.includes(searchTerm) ||
            (student.admissionNo && student.admissionNo.toLowerCase().includes(searchTerm));

        // Class filter
        const classMatch = classFilter === 'all' || student.schoolclassid == classFilter;

        // Status filter
        let statusMatch = statusFilter === 'all';
        if (!statusMatch) {
            if (statusFilter === '1' || statusFilter === '2') {
                statusMatch = student.statusId == statusFilter;
            } else if (statusFilter === 'Active' || statusFilter === 'Inactive') {
                statusMatch = student.student_status === statusFilter;
            }
        }

        // Gender filter
        const genderMatch = genderFilter === 'all' || student.gender === genderFilter;

        return searchMatch && classMatch && statusMatch && genderMatch;
    });
}

// Apply filters
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

// Reset all filters
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

// ============================================================================
// TABLE VIEW FUNCTIONS
// ============================================================================

// Render table view
function renderTableView(students) {
    const tbody = document.getElementById('studentTableBody');

    if (students.length === 0) {
        tbody.innerHTML = '';
        return;
    }

    tbody.innerHTML = students.map(student => `
        <tr data-id="${student.id}" class="${student.selected ? 'selected' : ''}">
            <td>
                <div class="form-check">
                    <input class="form-check-input student-checkbox" type="checkbox"
                           value="${student.id}" onchange="updateSelection(this, ${student.id})">
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        ${getStudentAvatar(student, false)}
                    </div>
                    <div>
                        <h6 class="mb-0">${student.firstname} ${student.lastname}</h6>
                        <small class="text-muted">${student.othername || ''}</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-light text-dark">${student.admissionNo || 'N/A'}</span>
            </td>
            <td>${student.schoolclass || ''} ${student.arm ? ' - ' + student.arm : ''}</td>
            <td>
                ${getStatusBadges(student)}
            </td>
            <td>
                <span class="badge ${student.gender === 'Male' ? 'bg-primary' : 'bg-pink'}">
                    ${student.gender || 'N/A'}
                </span>
            </td>
            <td>${formatDate(student.created_at, 'short')}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="viewStudent(${student.id})"
                            title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="editStudent(${student.id})"
                            title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteStudent(${student.id})"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ============================================================================
// CARD VIEW FUNCTIONS
// ============================================================================

// Render card view
function renderCardView(students) {
    const container = document.getElementById('studentsCardsContainer');

    if (students.length === 0) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = students.map(student => `
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="student-profile-card ${student.selected ? 'selected' : ''}" data-id="${student.id}">
                <div class="checkbox-container">
                    <div class="form-check">
                        <input class="form-check-input student-checkbox" type="checkbox"
                               value="${student.id}" onchange="updateSelection(this, ${student.id})">
                    </div>
                </div>

                <div class="card-header">
                    <div class="header-content">
                        <h5 class="student-name">${student.firstname} ${student.lastname}</h5>
                        <span class="student-admission">${student.admissionNo || 'No Admission No'}</span>
                    </div>
                    <div class="avatar-container">
                        ${getStudentAvatar(student, true)}
                    </div>
                </div>

                <div class="card-body">
                    ${getStatusBadges(student, true)}

                    <div class="student-info-grid">
                        <div class="info-item">
                            <span class="info-label">Class</span>
                            <span class="info-value">${student.schoolclass || 'N/A'} ${student.arm || ''}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender</span>
                            <span class="info-value">${student.gender || 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age</span>
                            <span class="info-value">${calculateStudentAge(student.dateofbirth)}</span>
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

// Get student avatar
function getStudentAvatar(student, isCard = false) {
    const initials = `${student.firstname?.charAt(0) || ''}${student.lastname?.charAt(0) || ''}`;
    const size = isCard ? '80px' : '40px';

    if (student.picture && student.picture !== 'unnamed.jpg') {
        return `
            <img src="/storage/images/student_avatars/${student.picture}"
                 alt="${student.firstname}"
                 class="avatar"
                 style="width: ${size}; height: ${size}; object-fit: cover;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="avatar-initials" style="width: ${size}; height: ${size}; display: none;">
                ${initials}
            </div>
        `;
    }

    return `
        <div class="avatar-initials" style="width: ${size}; height: ${size};">
            ${initials}
        </div>
    `;
}

// Get status badges
function getStatusBadges(student, isCard = false) {
    let badges = '';

    // Student activity status
    if (student.student_status === 'Active') {
        badges += `<span class="status-badge status-active ${isCard ? '' : 'me-1'}">
            <i class="fas fa-check-circle"></i> Active
        </span>`;
    } else if (student.student_status === 'Inactive') {
        badges += `<span class="status-badge status-inactive ${isCard ? '' : 'me-1'}">
            <i class="fas fa-pause-circle"></i> Inactive
        </span>`;
    }

    // Student type (New/Old)
    if (student.statusId == 2) {
        badges += `<span class="status-badge status-new ${isCard ? 'mt-1' : 'ms-1'}">
            <i class="fas fa-star"></i> New
        </span>`;
    } else if (student.statusId == 1) {
        badges += `<span class="status-badge status-old ${isCard ? 'mt-1' : 'ms-1'}">
            <i class="fas fa-history"></i> Old
        </span>`;
    }

    return badges;
}

// Calculate student age
function calculateStudentAge(dateOfBirth) {
    if (!dateOfBirth) return 'N/A';

    const dob = new Date(dateOfBirth);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    return age + ' years';
}

// Format date
function formatDate(dateString, format = 'long') {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);

    if (isNaN(date.getTime())) {
        return 'N/A';
    }

    const options = format === 'short' ?
        { year: 'numeric', month: 'short', day: 'numeric' } :
        { year: 'numeric', month: 'long', day: 'numeric' };

    return date.toLocaleDateString('en-US', options);
}

// ============================================================================
// SELECTION AND BULK OPERATIONS
// ============================================================================

// Update selection state
function updateSelection(checkbox, studentId) {
    const student = allStudents.find(s => s.id == studentId);
    if (student) {
        student.selected = checkbox.checked;
    }

    // Update UI
    const element = checkbox.closest('.student-profile-card, tr');
    if (element) {
        element.classList.toggle('selected', checkbox.checked);
    }

    updateBulkActionsVisibility();
    updateSelectAllCheckbox();
}

// Toggle select all
function toggleSelectAll(e) {
    const isChecked = e.target.checked;
    const checkboxes = document.querySelectorAll('.student-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;

        // Update student data
        const studentId = checkbox.value;
        const student = allStudents.find(s => s.id == studentId);
        if (student) {
            student.selected = isChecked;
        }

        // Update UI
        const parent = checkbox.closest('.student-profile-card, tr');
        if (parent) {
            parent.classList.toggle('selected', isChecked);
        }
    });

    updateBulkActionsVisibility();
}

// Update select all checkbox state
function updateSelectAllCheckbox() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
    const checkAll = document.getElementById('checkAll');
    const checkAllTable = document.getElementById('checkAllTable');

    if (checkboxes.length > 0) {
        const allChecked = checkedCount === checkboxes.length;
        const someChecked = checkedCount > 0 && checkedCount < checkboxes.length;

        if (checkAll) {
            checkAll.checked = allChecked;
            checkAll.indeterminate = someChecked;
        }

        if (checkAllTable) {
            checkAllTable.checked = allChecked;
            checkAllTable.indeterminate = someChecked;
        }
    }
}

// Update bulk actions visibility
function updateBulkActionsVisibility() {
    const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
    const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');

    if (bulkActionsDropdown) {
        if (selectedCount > 0) {
            bulkActionsDropdown.disabled = false;
            bulkActionsDropdown.innerHTML = `<i class="fas fa-cog me-2"></i>Actions (${selectedCount})`;
        } else {
            bulkActionsDropdown.disabled = true;
            bulkActionsDropdown.innerHTML = `<i class="fas fa-cog me-2"></i>Actions`;
        }
    }
}

// Get selected student IDs
function getSelectedStudentIds() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// ============================================================================
// PAGINATION FUNCTIONS
// ============================================================================

// Update pagination
function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const currentPageSpan = document.getElementById('currentPage');

    currentPageSpan.textContent = currentPage;

    if (prevBtn) {
        prevBtn.classList.toggle('disabled', currentPage === 1);
        prevBtn.onclick = currentPage === 1 ? null : goToPrevPage;
    }

    if (nextBtn) {
        nextBtn.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);
        nextBtn.onclick = (currentPage === totalPages || totalPages === 0) ? null : goToNextPage;
    }
}

// Go to previous page
function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderCurrentView();
    }
}

// Go to next page
function goToNextPage() {
    const filteredStudents = filterStudents();
    const totalPages = Math.ceil(filteredStudents.length / itemsPerPage);

    if (currentPage < totalPages) {
        currentPage++;
        renderCurrentView();
    }
}

// Update counts
function updateCounts(total, showing) {
    const totalStudents = document.getElementById('totalStudents');
    const totalCount = document.getElementById('totalCount');
    const showingCount = document.getElementById('showingCount');

    if (totalStudents) totalStudents.textContent = total;
    if (totalCount) totalCount.textContent = total;
    if (showingCount) showingCount.textContent = showing;
}

// Update empty state
function updateEmptyState(isEmpty) {
    const emptyState = document.getElementById('emptyState');
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');

    if (isEmpty) {
        if (emptyState) emptyState.classList.remove('d-none');
        if (tableView) tableView.classList.add('d-none');
        if (cardView) cardView.classList.add('d-none');
    } else {
        if (emptyState) emptyState.classList.add('d-none');
    }
}

// ============================================================================
// STUDENT CRUD OPERATIONS
// ============================================================================

// View student details
async function viewStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        const student = response.data.student || response.data;

        showStudentDetails(student);
    } catch (error) {
        console.error('Error viewing student:', error);
        showError('Failed to load student details.');
    }
}

// Show student details modal
function showStudentDetails(student) {
    const content = `
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div style="width: 150px; height: 150px; margin: 0 auto; border-radius: 50%; overflow: hidden; border: 5px solid #f3f4f6;">
                    ${getStudentAvatar(student, true).replace('80px', '140px')}
                </div>
                <h4 class="mt-3 mb-1">${student.firstname} ${student.lastname}</h4>
                <p class="text-muted mb-2">${student.admissionNo || 'No Admission No'}</p>
                ${getStatusBadges(student, true)}
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Date of Birth</label>
                        <p class="fw-bold">${formatDate(student.dateofbirth)}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Gender</label>
                        <p class="fw-bold">${student.gender || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Class</label>
                        <p class="fw-bold">${student.schoolclass || 'N/A'} ${student.arm || ''}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Religion</label>
                        <p class="fw-bold">${student.religion || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone</label>
                        <p class="fw-bold">${student.phone_number || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="fw-bold">${student.email || 'N/A'}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p class="fw-bold">${student.permanent_address || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Father's Name</label>
                        <p class="fw-bold">${student.father_name || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Mother's Name</label>
                        <p class="fw-bold">${student.mother_name || 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    const modalContent = document.getElementById('viewStudentContent');
    if (modalContent) {
        modalContent.innerHTML = content;

        const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
        modal.show();

        // Store student ID for edit button
        modal._element.dataset.studentId = student.id;
    }
}

// Edit student from view modal
function editStudentFromView() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'));
    const studentId = modal._element.dataset.studentId;

    if (studentId) {
        modal.hide();
        setTimeout(() => editStudent(studentId), 300);
    }
}

// Edit student
async function editStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        const student = response.data.student || response.data;

        populateEditForm(student);

        const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        modal.show();
    } catch (error) {
        console.error('Error editing student:', error);
        showError('Failed to load student for editing.');
    }
}

// Populate edit form
function populateEditForm(student) {
    // Basic information
    document.getElementById('editStudentId').value = student.id;
    document.getElementById('editAdmissionNo').value = student.admissionNo || '';
    document.getElementById('editAdmissionYear').value = student.admissionYear || new Date().getFullYear();
    document.getElementById('editAdmissionDate').value = student.admissionDate ? student.admissionDate.split('T')[0] : '';
    document.getElementById('editFirstname').value = student.firstname || '';
    document.getElementById('editLastname').value = student.lastname || '';
    document.getElementById('editOthername').value = student.othername || '';
    document.getElementById('editPermanentAddress').value = student.permanent_address || '';

    // Date of birth and age
    if (student.dateofbirth) {
        document.getElementById('editDOB').value = student.dateofbirth.split('T')[0];
        calculateAge(student.dateofbirth, 'editAgeInput');
    }

    // Set radio buttons
    setRadioValue('gender', student.gender);
    setRadioValue('statusId', student.statusId);
    setRadioValue('student_status', student.student_status);

    // Set select values
    setSelectValue('editSchoolclassid', student.schoolclassid);
    setSelectValue('editTermid', student.termid);
    setSelectValue('editSessionid', student.sessionid);
    setSelectValue('editReligion', student.religion);
    setSelectValue('editStudentCategory', student.student_category);
    setSelectValue('editSchoolHouse', student.schoolhouseid);

    // Set other fields
    document.getElementById('editPlaceofbirth').value = student.placeofbirth || '';
    document.getElementById('editNationality').value = student.nationality || '';
    document.getElementById('editCity').value = student.city || '';
    document.getElementById('editNinNumber').value = student.nin_number || '';
    document.getElementById('editBloodGroup').value = student.blood_group || '';
    document.getElementById('editMotherTongue').value = student.mother_tongue || '';
    document.getElementById('editPhoneNumber').value = student.phone_number || '';
    document.getElementById('editEmail').value = student.email || '';
    document.getElementById('editFutureAmbition').value = student.future_ambition || '';

    // Parent/Guardian details
    document.getElementById('editFatherName').value = student.father_name || '';
    document.getElementById('editFatherPhone').value = student.father_phone || '';
    document.getElementById('editFatherOccupation').value = student.father_occupation || '';
    document.getElementById('editFatherCity').value = student.father_city || '';
    document.getElementById('editMotherName').value = student.mother_name || '';
    document.getElementById('editMotherPhone').value = student.mother_phone || '';
    document.getElementById('editParentEmail').value = student.parent_email || '';
    document.getElementById('editParentAddress').value = student.parent_address || '';

    // Previous school
    document.getElementById('editLastSchool').value = student.last_school || '';
    document.getElementById('editLastClass').value = student.last_class || '';
    document.getElementById('editReasonForLeaving').value = student.reason_for_leaving || '';

    // Set state and LGA
    setTimeout(() => {
        setStateAndLGA('editState', 'editLocal', student.state || '', student.local || '');
    }, 100);

    // Set admission mode
    const admissionMode = student.admissionNo && student.admissionNo.includes('AUTO') ? 'auto' : 'manual';
    setRadioValue('admissionMode', admissionMode, 'edit');

    // Update form action
    const form = document.getElementById('editStudentForm');
    if (form && student.id) {
        form.action = `/student/${student.id}`;
    }
}

// Helper function to set radio button value
function setRadioValue(name, value, prefix = '') {
    const radios = document.querySelectorAll(`input[name="${name}"]${prefix ? '[id^="' + prefix + '"]' : ''}`);
    radios.forEach(radio => {
        radio.checked = (radio.value == value);
    });
}

// Helper function to set select value
function setSelectValue(selectId, value) {
    const select = document.getElementById(selectId);
    if (select) {
        select.value = value || '';
    }
}

// Delete student
async function deleteStudent(id) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "This student will be permanently deleted!",
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

            // Remove from local data
            allStudents = allStudents.filter(s => s.id != id);

            // Update UI
            renderCurrentView();

            Swal.fire({
                title: 'Deleted!',
                text: 'Student has been deleted successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } catch (error) {
            console.error('Error deleting student:', error);
            showError('Failed to delete student. Please try again.');
        }
    }
}

// Delete multiple students
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
        title: `Delete ${selectedIds.length} Student(s)?`,
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

            // Remove from local data
            allStudents = allStudents.filter(s => !selectedIds.includes(s.id.toString()));

            // Update UI
            renderCurrentView();

            Swal.fire({
                title: 'Deleted!',
                text: `${selectedIds.length} student(s) have been deleted.`,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } catch (error) {
            console.error('Error deleting students:', error);
            showError('Failed to delete selected students. Please try again.');
        }
    }
}

// ============================================================================
// FORM HANDLING
// ============================================================================

// Handle add student form submission
async function handleAddStudent(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await axios.post(form.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
            modal.hide();

            // Reset form
            form.reset();

            // Refresh student list
            await fetchStudents();

            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'Student registered successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } else {
            throw new Error(response.data.message || 'Registration failed');
        }
    } catch (error) {
        handleFormError(error, 'add');
    }
}

// Handle edit student form submission
async function handleEditStudent(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await axios.post(form.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            // Close modal
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
        } else {
            throw new Error(response.data.message || 'Update failed');
        }
    } catch (error) {
        handleFormError(error, 'edit');
    }
}

// Handle form errors
function handleFormError(error, formType) {
    let errorMessage = `Failed to ${formType === 'add' ? 'register' : 'update'} student.`;
    const errorElementId = formType === 'add' ? 'alert-error-msg' : 'edit-alert-error-msg';
    const errorElement = document.getElementById(errorElementId);

    if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
    }

    if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        let errorList = '';
        for (const field in errors) {
            errorList += `<li>${errors[field].join(', ')}</li>`;
        }
        errorMessage = `<strong>Validation Errors:</strong><ul class="mb-0">${errorList}</ul>`;
    }

    if (errorElement) {
        errorElement.innerHTML = errorMessage;
        errorElement.classList.remove('d-none');
    } else {
        Swal.fire({
            title: 'Error!',
            html: errorMessage,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

// ============================================================================
// CURRENT TERM MANAGEMENT
// ============================================================================

// Show update current term modal
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

// Update current term for selected students
async function updateCurrentTermForSelected() {
    const selectedIds = getSelectedStudentIds();
    const form = document.getElementById('updateCurrentTermForm');
    const formData = new FormData(form);

    formData.append('student_ids', JSON.stringify(selectedIds));

    try {
        const response = await axios.post('/student-current-term/bulk-update', formData);

        if (response.data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('updateCurrentTermModal'));
            modal.hide();

            // Clear selections
            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                cb.checked = false;
                cb.dispatchEvent(new Event('change'));
            });

            Swal.fire({
                title: 'Success!',
                text: `Current term updated for ${selectedIds.length} student(s).`,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } else {
            throw new Error(response.data.message || 'Update failed');
        }
    } catch (error) {
        console.error('Error updating current term:', error);
        showError('Failed to update current term. Please try again.');
    }
}

// ============================================================================
// REPORT MODAL FUNCTIONS
// ============================================================================

// Initialize report modal
function initializeReportModal() {
    initializeColumnOrdering();
    updatePreview();

    // Set up event listeners for checkboxes
    document.querySelectorAll('.column-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updatePreview);
    });
}

// Initialize column ordering
function initializeColumnOrdering() {
    const container = document.getElementById('columnsContainer');
    if (!container) return;

    if (typeof Sortable !== 'undefined') {
        columnSortable = new Sortable(container, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            onEnd: updateColumnOrder
        });
    }
}

// Update column order
function updateColumnOrder() {
    const container = document.getElementById('columnsContainer');
    const hiddenInput = document.getElementById('columnsOrderInput');

    if (!container || !hiddenInput) return;

    const columnItems = container.querySelectorAll('.draggable-item');
    const order = [];

    columnItems.forEach(item => {
        const checkbox = item.querySelector('.column-checkbox');
        if (checkbox && checkbox.checked) {
            order.push(checkbox.value);
        }
    });

    hiddenInput.value = order.join(',');
    updatePreview();
}

// Update preview
function updatePreview() {
    const container = document.getElementById('columnsContainer');
    const preview = document.getElementById('columnOrderPreview');

    if (!container || !preview) return;

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

    preview.textContent = selectedLabels.join(', ') || 'No columns selected';
}

// Generate report
async function generateReport() {
    const form = document.getElementById('printReportForm');
    const selectedCheckboxes = form.querySelectorAll('input[name="columns[]"]:checked');
    const selectedColumns = Array.from(selectedCheckboxes).map(cb => cb.value);

    if (selectedColumns.length === 0) {
        Swal.fire({
            title: 'Warning!',
            text: 'Please select at least one column.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Get form values
    const formData = new FormData(form);
    const params = new URLSearchParams();

    formData.forEach((value, key) => {
        if (key === 'columns[]') {
            // Handle array values
            if (!params.has('columns')) {
                params.set('columns', value);
            }
        } else {
            params.set(key, value);
        }
    });

    // Add columns order
    const columnsOrderInput = document.getElementById('columnsOrderInput');
    if (columnsOrderInput && columnsOrderInput.value) {
        params.set('columns_order', columnsOrderInput.value);
    }

    try {
        const response = await axios.get(`/students/report?${params.toString()}`, {
            responseType: 'blob'
        });

        // Create download link
        const blob = new Blob([response.data]);
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

        // Get filename
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'student-report.pdf';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="(.+)"/);
            if (filenameMatch && filenameMatch[1]) {
                filename = filenameMatch[1];
            }
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('printStudentReportModal'));
        modal.hide();

        Swal.fire({
            title: 'Success!',
            text: 'Report generated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    } catch (error) {
        console.error('Error generating report:', error);
        showError('Failed to generate report. Please try again.');
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

// Ensure axios is available
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error('Axios is not loaded');
        Swal.fire({
            title: 'Error!',
            text: 'Required libraries are not loaded. Please refresh the page.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }

    // Set CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    return true;
}

// Get student initials
function getStudentInitials(firstName, lastName) {
    const firstInitial = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
    const lastInitial = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
    return (firstInitial + lastInitial) || '??';
}

// Generate placeholder image
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

// Print student details
function printStudentDetails(mode = 'add') {
    const form = mode === 'edit' ? document.getElementById('editStudentForm') : document.getElementById('addStudentForm');

    if (!form) {
        showError('Form not found for printing.');
        return;
    }

    // Collect form data
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Student Details</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .section { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
                    .section h3 { color: #333; margin-bottom: 10px; }
                    .field { margin-bottom: 8px; }
                    .label { font-weight: bold; color: #666; }
                    .value { margin-left: 10px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Student Registration Form</h1>
                    <p>Generated on ${new Date().toLocaleDateString()}</p>
                </div>
                <div class="section">
                    <h3>Academic Details</h3>
                    <div class="field">
                        <span class="label">Admission Number:</span>
                        <span class="value">${data.admissionNo || ''}</span>
                    </div>
                    <div class="field">
                        <span class="label">Class:</span>
                        <span class="value">${getClassName(data.schoolclassid) || ''}</span>
                    </div>
                </div>
                <div class="section">
                    <h3>Personal Details</h3>
                    <div class="field">
                        <span class="label">Name:</span>
                        <span class="value">${data.firstname || ''} ${data.lastname || ''} ${data.othername || ''}</span>
                    </div>
                    <div class="field">
                        <span class="label">Gender:</span>
                        <span class="value">${data.gender || ''}</span>
                    </div>
                </div>
                <!-- Add more sections as needed -->
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}

// Get class name from ID
function getClassName(classId) {
    const select = document.getElementById('schoolclassid') || document.getElementById('editSchoolclassid');
    if (select) {
        const option = select.querySelector(`option[value="${classId}"]`);
        return option ? option.textContent : classId;
    }
    return classId;
}

// Debug function
function debugColumnOrdering() {
    const container = document.getElementById('columnsContainer');
    const hiddenInput = document.getElementById('columnsOrderInput');

    console.log('Container:', container);
    console.log('Hidden input:', hiddenInput);
    console.log('Hidden input value:', hiddenInput?.value);

    const columnItems = container.querySelectorAll('.draggable-item');
    console.log('Column items:', columnItems.length);

    columnItems.forEach((item, index) => {
        const checkbox = item.querySelector('.column-checkbox');
        console.log(`Item ${index + 1}:`, {
            value: checkbox?.value,
            checked: checkbox?.checked,
            label: item.querySelector('.form-check-label')?.textContent.trim()
        });
    });
}

// Initialize everything when page loads
window.addEventListener('load', function() {
    // Ensure all required scripts are loaded
    if (!ensureAxios()) {
        console.error('Required scripts not loaded');
        return;
    }

    // Initialize the application
    initializeApplication();
});

// Export functions that need to be available globally
window.toggleView = toggleView;
window.filterData = filterData;
window.resetFilters = resetFilters;
window.deleteMultiple = deleteMultiple;
window.showUpdateCurrentTermModal = showUpdateCurrentTermModal;
window.updateCurrentTermForSelected = updateCurrentTermForSelected;
window.generateReport = generateReport;
window.debugColumnOrdering = debugColumnOrdering;
window.calculateAge = calculateAge;
window.toggleAdmissionInput = toggleAdmissionInput;
window.updateAdmissionNumber = updateAdmissionNumber;
window.previewImage = previewImage;
window.editStudentFromView = editStudentFromView;
window.printStudentDetails = printStudentDetails;
</script>
@endsection
