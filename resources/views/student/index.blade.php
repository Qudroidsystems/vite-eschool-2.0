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

                /* ====== PROGRESS STEPS ====== */
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
                    color: #6b7280;
                    position: relative;
                    z-index: 2;
                    border: 2px solid #e9ecef;
                }

                .progress-steps .step.active {
                    background: #405189;
                    color: white;
                    border-color: #405189;
                }

                /* ====== FORM SECTIONS ====== */
                .form-section {
                    padding: 20px 30px;
                    border-bottom: 1px solid #e5e7eb;
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

                .form-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                    gap: 20px;
                }

                .name-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 15px;
                }

                .full-width {
                    grid-column: 1 / -1;
                }

                /* ====== DRAG AND DROP STYLES ====== */
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

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') }}
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
                        <button type="button" class="btn btn-success" id="generateReportBtn" onclick="generateReport()">
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

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i>
                            Student Registration
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                                <input type="text" id="last_school" name="last_school" class="form-control" placeholder="Previous school name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="last_class" class="form-label">Last Class Attended</label>
                                                <input type="text" id="last_class" name="last_class" class="form-control" placeholder="e.g., JSS 2" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="reason_for_leaving" class="form-label">Reason for Leaving</label>
                                                <textarea id="reason_for_leaving" name="reason_for_leaving" class="form-control" rows="2" placeholder="Reason for leaving previous school" required></textarea>
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
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title">
                            <i class="fas fa-user-edit me-2"></i>Edit Student
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                                <label for="editLastSchool" class="form-label">Last School Attended</label>
                                                <input type="text" id="editLastSchool" name="last_school" class="form-control" placeholder="Previous school name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editLastClass" class="form-label">Last Class Attended</label>
                                                <input type="text" id="editLastClass" name="last_class" class="form-control" placeholder="e.g., JSS 2" required>
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
// COMBINED STUDENT MANAGEMENT SYSTEM - ENHANCED WITH ALL FEATURES
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
const defaultAvatar = '{{ asset("storage/images/student_avatars/unnamed.jpg") }}';

// Nigerian states data (from original code)
const nigerianStates = [
    { name: "Abia", lgAs: ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"] },
    // ... (all other states from original code)
];

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing application...');
    initializeApplication();
});

// Initialize Application
function initializeApplication() {
    // Initialize admission number on page load
    updateAdmissionNumber();
    updateAdmissionNumber('edit');

    // Initialize states dropdowns
    initializeStatesDropdown('addState', 'addLocal');
    initializeStatesDropdown('editState', 'editLocal');

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
    const addForm = document.getElementById('addStudentForm');
    const editForm = document.getElementById('editStudentForm');

    if (addForm) {
        addForm.addEventListener('submit', handleAddStudent);
    }

    if (editForm) {
        editForm.addEventListener('submit', handleEditStudent);
    }

    // Admission number events
    const admissionYear = document.getElementById('admissionYear');
    const editAdmissionYear = document.getElementById('editAdmissionYear');

    if (admissionYear) {
        admissionYear.addEventListener('change', () => updateAdmissionNumber());
    }

    if (editAdmissionYear) {
        editAdmissionYear.addEventListener('change', () => updateAdmissionNumber('edit'));
    }

    // Initialize report modal when shown
    const reportModal = document.getElementById('printStudentReportModal');
    if (reportModal) {
        reportModal.addEventListener('show.bs.modal', function() {
            console.log('Report modal shown, initializing...');
            setTimeout(initializeReportModal, 100);
        });
    }
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

// ============================================================================
// ADMISSION NUMBER FUNCTIONS (from original)
// ============================================================================

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
                showError(data.message || 'Failed to generate admission number');
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to generate admission number');
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
                showError(data.message || 'Failed to generate admission number');
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to generate admission number');
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
// STATE AND LGA MANAGEMENT FUNCTIONS (from original)
// ============================================================================

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

// Set specific state and LGA (for edit mode)
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
// VIEW MANAGEMENT FUNCTIONS
// ============================================================================

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

// ============================================================================
// FILTER FUNCTIONS
// ============================================================================

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

// ============================================================================
// STUDENT DATA FUNCTIONS
// ============================================================================

// Fetch Students
// Fetch Students with proper relationships
async function fetchStudents() {
    showLoading();

    try {
        const response = await axios.get('/students/data');
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

        // Process students and fetch their current term/class info
        allStudents = await Promise.all(studentsArray.map(async (student) => {
            // Try to get current term/class info
            let currentClassInfo = {};
            try {
                const currentInfo = await axios.get(`/student/${student.id}/current-info`);
                if (currentInfo.data.success) {
                    currentClassInfo = currentInfo.data.data;
                }
            } catch (error) {
                console.log(`No current info for student ${student.id}`);
            }

            return {
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
                // Current term info
                current_class_id: currentClassInfo.current_class_id || '',
                current_class: currentClassInfo.current_class || '',
                current_class_arm: currentClassInfo.current_class_arm || '',
                current_term_id: currentClassInfo.current_term_id || '',
                current_term: currentClassInfo.current_term || '',
                current_session_id: currentClassInfo.current_session_id || '',
                current_session: currentClassInfo.current_session || '',
                // Other fields
                state: student.state || '',
                local: student.local || '',
                dateofbirth: student.dateofbirth || '',
                placeofbirth: student.placeofbirth || '',
                phone_number: student.phone_number || '',
                email: student.email || '',
                permanent_address: student.permanent_address || '',
                future_ambition: student.future_ambition || '',
                nationality: student.nationality || '',
                religion: student.religion || '',
                blood_group: student.blood_group || '',
                mother_tongue: student.mother_tongue || '',
                nin_number: student.nin_number || '',
                student_category: student.student_category || '',
                father_name: student.father_name || '',
                mother_name: student.mother_name || '',
                father_phone: student.father_phone || '',
                mother_phone: student.mother_phone || '',
                parent_email: student.parent_email || '',
                parent_address: student.parent_address || '',
                last_school: student.last_school || '',
                last_class: student.last_class || '',
                reason_for_leaving: student.reason_for_leaving || ''
            };
        }));

        console.log('Processed students with current info:', allStudents);
        renderCurrentView();
    } catch (error) {
        console.error('Error fetching students:', error);
        showError('Failed to load students. Please try again.');
    } finally {
        hideLoading();
    }
}



// ============================================================================
// RENDER FUNCTIONS
// ============================================================================

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

    initializeCheckboxes();
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

    initializeStudentCheckboxes();
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

// Get Student Avatar
function getStudentAvatar(student, isCard = false) {
    const initials = getStudentInitials(student.firstname, student.lastname);
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

// Get Student Initials
function getStudentInitials(firstName, lastName) {
    const firstInitial = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
    const lastInitial = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
    return (firstInitial + lastInitial) || '??';
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

// ============================================================================
// CHECKBOX AND BULK ACTIONS
// ============================================================================

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

// Initialize Checkboxes
function initializeCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    const checkAllTable = document.getElementById('checkAllTable');

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsVisibility();
        });
    }

    if (checkAllTable) {
        checkAllTable.addEventListener('change', function() {
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsVisibility();
        });
    }
}

// Initialize Student Checkboxes for Card View
function initializeStudentCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;

    checkAll.addEventListener('change', function() {
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionsVisibility();
    });

    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.student-checkbox').length ===
                document.querySelectorAll('.student-checkbox:checked').length;
            checkAll.checked = allChecked;
            updateBulkActionsVisibility();
        });
    });
}

// ============================================================================
// PAGINATION FUNCTIONS
// ============================================================================

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

// ============================================================================
// CRUD OPERATIONS
// ============================================================================

// View Student
async function viewStudent(id) {
    try {
        const response = await axios.get(`/student/${id}/edit`);
        const student = response.data.student || response.data;

        showStudentDetails(student);
    } catch (error) {
        showError('Failed to load student details.');
    }
}

// Edit Student
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

// Delete Student
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

// ============================================================================
// FORM HANDLING FUNCTIONS
// ============================================================================

// Populate Edit Form
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
        }
    });

    // Set gender
    const genderRadios = document.querySelectorAll('#editStudentModal input[name="gender"]');
    if (genderRadios.length > 0) {
        const studentGender = student.gender || '';
        genderRadios.forEach(radio => {
            radio.checked = (radio.value === studentGender);
        });
    }

    // Set status
    const statusRadios = document.querySelectorAll('#editStudentModal input[name="statusId"]');
    if (statusRadios.length > 0) {
        const studentStatusId = student.statusId || student.status_id || '';
        statusRadios.forEach(radio => {
            radio.checked = (parseInt(radio.value) === parseInt(studentStatusId));
        });
    }

    // Set student activity status
    const studentStatusRadios = document.querySelectorAll('#editStudentModal input[name="student_status"]');
    if (studentStatusRadios.length > 0) {
        const studentActivityStatus = student.student_status || student.status || '';
        studentStatusRadios.forEach(radio => {
            radio.checked = (radio.value === studentActivityStatus);
        });
    }

    // Set avatar
    const avatarElement = document.getElementById('editStudentAvatar');
    if (avatarElement) {
        const displayInitials = getStudentInitials(student.firstname, student.lastname);

        if (student.picture && student.picture !== 'unnamed.jpg') {
            const avatarUrl = `/storage/images/student_avatars/${student.picture}`;
            avatarElement.src = avatarUrl;
        } else {
            // Show initials
            avatarElement.src = generatePlaceholderImage(displayInitials);
        }
    }

    // Calculate age if date of birth exists
    if (student.dateofbirth) {
        calculateAge(student.dateofbirth, 'editAgeInput');
    }

    // Set state and LGA
    setTimeout(() => {
        setStateAndLGA('editState', 'editLocal', student.state || '', student.local || '');
    }, 100);

    // Update form action
    const form = document.getElementById('editStudentForm');
    if (form && student.id) {
        form.action = `/student/${student.id}`;
    }
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

// ============================================================================
// FORM SUBMISSION HANDLERS
// ============================================================================

// Handle Add Student
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

// Handle Edit Student
async function handleEditStudent(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await axios.post(form.action, formData, {
            headers: { 'Content-Type': 'multipart/form/form-data' }
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

// ============================================================================
// UPDATE CURRENT TERM FUNCTIONS
// ============================================================================

// Show Update Current Term Modal
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

// Update Current Term
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

// ============================================================================
// REPORT MODAL FUNCTIONS (from original)
// ============================================================================

let columnSortable = null;

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

// Update preview
function updatePreview() {
    console.log('Updating preview...');

    const container = document.getElementById('columnsContainer');
    if (!container) return;

    // Get selected columns in current order
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

    // Update hidden input with order
    const hiddenInput = document.getElementById('columnsOrderInput');
    if (hiddenInput) {
        const order = [];
        columnItems.forEach(item => {
            const checkbox = item.querySelector('.column-checkbox');
            if (checkbox && checkbox.checked) {
                order.push(checkbox.value);
            }
        });
        hiddenInput.value = order.join(',');
    }
}

// Generate Report
// Generate Report
window.generateReport = function() {
    console.log('Generate report clicked');

    const form = document.getElementById('printReportForm');
    if (!form) {
        console.error('Report form not found');
        return;
    }

    // Get selected columns
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

    // Get form values
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
        const modalElement = document.getElementById('printStudentReportModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
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
            if (error.response.status === 404) {
                errorMessage = 'No students found matching the selected filters.';
            } else if (error.response.status === 422) {
                errorMessage = error.response.data.message || 'Validation error. Please check your selections.';
            } else if (error.response.status === 500) {
                errorMessage = error.response.data?.message || 'Server error. Please try again later.';
            }

            if (error.response.data && typeof error.response.data === 'object') {
                if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }
        } else if (error.code === 'ECONNABORTED') {
            errorMessage = 'Request timeout. The report generation is taking too long. Try with fewer students or different filters.';
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
};

// Initialize Report Modal
function initializeReportModal() {
    console.log('Initializing report modal...');

    // Initialize column ordering
    initializeColumnOrdering();

    // Set up event listeners for checkboxes
    const container = document.getElementById('columnsContainer');
    if (container) {
        container.querySelectorAll('.column-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updatePreview);
        });
    }

    // Initial preview update
    updatePreview();
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

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

// Generate Placeholder Image
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

// Calculate Age (for form input)
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
        }
    } catch (error) {
        console.error('Error calculating age:', error);
    }
};

// Preview Image
function previewImage(input, targetId = 'addStudentAvatar') {
    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        document.getElementById(targetId).src = e.target.result;
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}

// Initialize Form Validation
function initializeFormValidation() {
    // Add your form validation logic here
    // This is a placeholder for form validation initialization
}

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

// Initialize the application
initializeApplication();

</script>
@endsection
