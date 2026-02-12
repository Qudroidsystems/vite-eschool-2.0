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

                /* ====== ENHANCED TABLE ACTION BUTTONS ====== */
                .btn-soft-info {
                    color: #0dcaf0;
                    background-color: rgba(13, 202, 240, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-info:hover {
                    color: #fff;
                    background-color: #0dcaf0;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(13, 202, 240, 0.2);
                }

                .btn-soft-warning {
                    color: #ffc107;
                    background-color: rgba(255, 193, 7, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-warning:hover {
                    color: #fff;
                    background-color: #ffc107;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
                }

                .btn-soft-danger {
                    color: #dc3545;
                    background-color: rgba(220, 53, 69, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-danger:hover {
                    color: #fff;
                    background-color: #dc3545;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
                }

                .btn-soft-secondary {
                    color: #6c757d;
                    background-color: rgba(108, 117, 125, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-secondary:hover {
                    color: #fff;
                    background-color: #6c757d;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
                }

                .btn-soft-success {
                    color: #198754;
                    background-color: rgba(25, 135, 84, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-success:hover {
                    color: #fff;
                    background-color: #198754;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
                }

                .btn-soft-primary {
                    color: #0d6efd;
                    background-color: rgba(13, 110, 253, 0.1);
                    border-color: transparent;
                    transition: all 0.2s ease;
                }

                .btn-soft-primary:hover {
                    color: #fff;
                    background-color: #0d6efd;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
                }

                /* Button groups */
                .btn-group .btn {
                    padding: 0.4rem 0.8rem;
                    font-size: 0.875rem;
                }

                .btn-group .btn:first-child {
                    border-top-left-radius: 8px;
                    border-bottom-left-radius: 8px;
                }

                .btn-group .btn:last-child {
                    border-top-right-radius: 8px;
                    border-bottom-right-radius: 8px;
                }

                /* Dropdown menu styling */
                .dropdown-menu {
                    border: none;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
                    border-radius: 12px;
                    padding: 8px;
                    animation: fadeInDown 0.2s ease;
                }

                .dropdown-item {
                    border-radius: 8px;
                    padding: 8px 16px;
                    font-size: 0.875rem;
                    transition: all 0.2s ease;
                }

                .dropdown-item:hover {
                    background-color: #f8f9fa;
                    transform: translateX(4px);
                }

                .dropdown-item i {
                    width: 20px;
                    text-align: center;
                }

                .dropdown-divider {
                    margin: 8px 0;
                    opacity: 0.1;
                }

                /* Table row enhancements */
                .data-table tbody tr {
                    transition: all 0.25s ease;
                }

                .data-table tbody tr:hover {
                    background-color: #f8f9fa;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
                }

                .data-table tbody td {
                    padding: 16px 12px;
                    vertical-align: middle;
                }

                /* Badge enhancements */
                .badge {
                    font-weight: 500;
                    letter-spacing: 0.3px;
                }

                .badge.bg-gradient {
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                }

                /* Animations */
                @keyframes fadeInDown {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes pulse {
                    0% {
                        box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4);
                    }
                    70% {
                        box-shadow: 0 0 0 6px rgba(67, 97, 238, 0);
                    }
                    100% {
                        box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
                    }
                }

                /* Active status indicator */
                .position-absolute.bg-success,
                .position-absolute.bg-secondary {
                    animation: pulse 2s infinite;
                    box-shadow: 0 0 0 rgba(67, 97, 238, 0.4);
                    border: 2px solid white;
                }

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .btn-group .btn {
                        padding: 0.3rem 0.6rem;
                    }

                    .data-table tbody td {
                        padding: 12px 8px;
                    }
                }

                /* Color utilities */
                .text-pink {
                    color: #f72585;
                }

                .bg-pink {
                    background-color: #f72585;
                }

                /* Avatar enhancements */
                .avatar-circle {
                    position: relative;
                    display: inline-block;
                }

                .avatar-initials {
                    font-family: 'Inter', sans-serif;
                    font-weight: 600;
                    text-transform: uppercase;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    transition: all 0.3s ease;
                }

                tr:hover .avatar-initials {
                    transform: scale(1.05);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }

                /* Quick action buttons */
                .quick-actions {
                    display: flex;
                    gap: 4px;
                }

                /* Status indicators */
                .status-dot {
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    margin-right: 6px;
                }

                .status-dot.active {
                    background-color: #10b981;
                    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
                }

                .status-dot.inactive {
                    background-color: #6c757d;
                    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.2);
                }

                /* Table action container */
                .table-actions-container {
                    display: flex;
                    gap: 8px;
                    justify-content: flex-end;
                }

                /* Student info in table */
                .student-info-wrapper {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .student-details {
                    display: flex;
                    flex-direction: column;
                }

                .student-name {
                    font-weight: 600;
                    color: #1e293b;
                    margin-bottom: 4px;
                }

                .student-meta {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .admission-badge {
                    background-color: #f1f5f9;
                    color: #475569;
                    padding: 2px 8px;
                    border-radius: 20px;
                    font-size: 11px;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                }

                /* Compact status badges */
                .compact-badge {
                    padding: 4px 8px;
                    border-radius: 20px;
                    font-size: 11px;
                    font-weight: 500;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
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

                /* ====== ENHANCED VIEW MODAL STYLES ====== */
                .modal-header-gradient {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }

                .info-card {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                    overflow: hidden;
                    height: 100%;
                }

                .info-card-header {
                    padding: 12px 16px;
                    background: #f8fafc;
                    border-bottom: 1px solid #e9ecef;
                }

                .info-card-header h6 {
                    margin: 0;
                    color: #1e293b;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                }

                .info-card-body {
                    padding: 16px;
                }

                .bg-soft-primary {
                    background-color: rgba(13, 110, 253, 0.05);
                }

                .bg-soft-pink {
                    background-color: rgba(244, 67, 149, 0.05);
                }

                .bg-soft-success {
                    background-color: rgba(40, 167, 69, 0.05);
                }

                .bg-soft-warning {
                    background-color: rgba(255, 193, 7, 0.05);
                }

                .bg-soft-info {
                    background-color: rgba(23, 162, 184, 0.05);
                }

                .bg-danger-light {
                    background-color: rgba(220, 53, 69, 0.1);
                    color: #dc3545;
                    padding: 4px 8px;
                    border-radius: 20px;
                }

                /* Profile header */
                .student-profile-header {
                    background: linear-gradient(to bottom, #f8fafc, #fff);
                    border-bottom: 1px solid #e9ecef;
                }

                .profile-avatar {
                    border-radius: 50%;
                    display: inline-block;
                    position: relative;
                }

                /* Nav tabs customization */
                .nav-tabs-custom {
                    border-bottom: 2px solid #e9ecef;
                }

                .nav-tabs-custom .nav-link {
                    border: none;
                    padding: 12px 20px;
                    color: #6c757d;
                    font-weight: 500;
                    position: relative;
                }

                .nav-tabs-custom .nav-link.active {
                    color: #405189;
                    background: transparent;
                }

                .nav-tabs-custom .nav-link.active::after {
                    content: '';
                    position: absolute;
                    bottom: -2px;
                    left: 0;
                    right: 0;
                    height: 2px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }

                /* Table improvements */
                .table-sm td, .table-sm th {
                    padding: 8px 8px;
                }

                .table tr {
                    transition: background-color 0.2s ease;
                }

                .table tr:hover {
                    background-color: rgba(64, 81, 137, 0.02);
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
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Gender</th>
                                    <th>Registered</th>
                                    <th width="250">Actions</th>
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
                            <i class="fas fa-calendar-alt me-2"></i>Register/Update Term
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="updateCurrentTermForm">
                            @csrf
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Registering/updating term for <span id="selectedStudentsCount">0</span> selected student(s).
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Class</label>
                                <select class="form-control" name="schoolclassId" required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Term</label>
                                <select class="form-control" name="termId" required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Session</label>
                                <select class="form-control" name="sessionId" required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" checked>
                                    <label class="form-check-label" for="is_current">
                                        Mark as current term for student(s)
                                    </label>
                                </div>
                                <small class="text-muted">If checked, this will be marked as the current term. Previous current term will be unmarked.</small>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Students can have multiple terms registered in the same session.
                                If a term already exists for a student in this session, it will be updated.
                                Otherwise, a new term registration will be created.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary-gradient" id="confirmUpdateCurrentTerm" onclick="updateCurrentTerm()">
                            <i class="fas fa-save me-2"></i>Register/Update Term
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

        <!-- ===== ENHANCED VIEW STUDENT MODAL WITH COMPLETE PARENT INFORMATION ===== -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <!-- Modern Gradient Header -->
                    <div class="modal-header modal-header-gradient">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper me-3">
                                <i class="fas fa-graduation-cap fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="modal-title mb-1">Student Profile</h4>
                                <p class="text-white-50 mb-0">Complete student information and records</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-0">
                        <!-- Student Header with Profile Image and Basic Info -->
                        <div class="student-profile-header bg-light p-4 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="position-relative">
                                        <div class="profile-avatar" id="viewStudentAvatarContainer">
                                            <img id="viewStudentPhoto"
                                                 src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}"
                                                 alt="Student Photo"
                                                 class="rounded-circle border border-4 border-white shadow"
                                                 style="width: 120px; height: 120px; object-fit: cover;">
                                            <span class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2 border border-2 border-white"
                                                  style="width: 20px; height: 20px;"
                                                  id="studentStatusIndicator"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="d-flex flex-column">
                                        <h2 class="mb-2 fw-bold" id="viewFullName">-</h2>
                                        <div class="d-flex flex-wrap gap-3 mb-2">
                                            <span class="badge bg-primary bg-gradient px-3 py-2">
                                                <i class="fas fa-id-card me-1"></i>
                                                <span id="viewAdmissionNumber">-</span>
                                            </span>
                                            <span class="badge bg-info bg-gradient px-3 py-2" id="viewClassBadge">
                                                <i class="fas fa-school me-1"></i>
                                                <span id="viewClassDisplay">-</span>
                                            </span>
                                            <span class="badge bg-success bg-gradient px-3 py-2" id="viewStudentTypeBadge">
                                                <i class="fas fa-user-tag me-1"></i>
                                                <span id="viewStudentType">-</span>
                                            </span>
                                        </div>
                                        <div class="d-flex gap-4 text-muted">
                                            <div><i class="fas fa-calendar-alt me-1"></i> Admitted: <span id="viewAdmittedDate">-</span></div>
                                            <div><i class="fas fa-venus-mars me-1"></i> <span id="viewGenderText">-</span></div>
                                            <div><i class="fas fa-birthday-cake me-1"></i> Age: <span id="viewAge">-</span> years</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modern Tab Navigation -->
                        <div class="px-4 pt-4">
                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#personalInfo" role="tab">
                                        <i class="fas fa-user-circle me-2"></i>Personal Details
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#academicInfo" role="tab">
                                        <i class="fas fa-graduation-cap me-2"></i>Academic Info
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#familyInfo" role="tab">
                                        <i class="fas fa-users me-2"></i>Family & Guardian
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#additionalInfo" role="tab">
                                        <i class="fas fa-info-circle me-2"></i>Additional Info
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#termHistory" role="tab">
                                        <i class="fas fa-history me-2"></i>Term History
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content p-4">
                            <!-- 1. PERSONAL DETAILS TAB -->
                            <div class="tab-pane fade show active" id="personalInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-id-badge me-2 text-primary"></i>Basic Information</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Full Name:</th>
                                                        <td class="fw-semibold" id="viewFullNameDetail">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Title:</th>
                                                        <td id="viewTitle">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Date of Birth:</th>
                                                        <td><span id="viewDOB">-</span> (<span id="viewAgeDetail">-</span> years)</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Place of Birth:</th>
                                                        <td id="viewPlaceOfBirth">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Gender:</th>
                                                        <td><span id="viewGenderDetail">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Blood Group:</th>
                                                        <td><span class="badge bg-danger-light" id="viewBloodGroupDetail">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Religion:</th>
                                                        <td id="viewReligionDetail">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-address-card me-2 text-primary"></i>Contact Information</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Phone Number:</th>
                                                        <td><i class="fas fa-phone-alt me-1 text-muted"></i> <span id="viewPhoneNumber">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email Address:</th>
                                                        <td><i class="fas fa-envelope me-1 text-muted"></i> <span id="viewEmailAddress">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Permanent Address:</th>
                                                        <td><i class="fas fa-map-marker-alt me-1 text-muted"></i> <span id="viewPermanentAddress">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>City:</th>
                                                        <td id="viewCity">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>State of Origin:</th>
                                                        <td id="viewStateOrigin">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>LGA:</th>
                                                        <td id="viewLGA">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Nationality:</th>
                                                        <td id="viewNationality">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-rocket me-2 text-primary"></i>Future Ambition</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <p class="mb-0 fst-italic" id="viewFutureAmbition">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. ACADEMIC INFORMATION TAB -->
                            <div class="tab-pane fade" id="academicInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-graduation-cap me-2 text-success"></i>Current Academic Status</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Admission No:</th>
                                                        <td class="fw-bold text-primary" id="viewAdmissionNo">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Admission Date:</th>
                                                        <td id="viewAdmissionDate">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Class:</th>
                                                        <td><span class="badge bg-info" id="viewCurrentClass">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Arm:</th>
                                                        <td id="viewArm">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Student Category:</th>
                                                        <td><span class="badge bg-secondary" id="viewStudentCategory">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Student Status:</th>
                                                        <td><span id="viewStudentStatus">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>School House:</th>
                                                        <td id="viewSchoolHouse">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2 text-success"></i>Current Term Information</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <div class="text-center mb-3" id="currentTermAlert">
                                                    <!-- Will be populated by JS -->
                                                </div>
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Current Term:</th>
                                                        <td><span id="viewCurrentTerm">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Current Session:</th>
                                                        <td id="viewCurrentSession">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status in Current Term:</th>
                                                        <td><span id="viewCurrentTermStatus">-</span></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-school me-2 text-success"></i>Previous School</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Last School:</th>
                                                        <td id="viewLastSchool">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Last Class:</th>
                                                        <td id="viewLastClass">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Reason for Leaving:</th>
                                                        <td><em id="viewReasonForLeaving">-</em></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. FAMILY & GUARDIAN INFORMATION TAB - ENHANCED -->
                            <div class="tab-pane fade" id="familyInfo" role="tabpanel">
                                <div class="row">
                                    <!-- Father's Information -->
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header bg-soft-primary">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-tie me-2 text-primary"></i>Father's Information
                                                    <span class="badge bg-primary ms-2" id="fatherStatusBadge"></span>
                                                </h6>
                                            </div>
                                            <div class="info-card-body">
                                                <div class="text-center mb-3" id="fatherPhotoSection" style="display: none;">
                                                    <img id="viewFatherPhoto" src="" alt="Father" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover;">
                                                </div>
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%"><i class="fas fa-user me-1 text-muted"></i>Full Name:</th>
                                                        <td class="fw-semibold" id="viewFatherFullName">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-phone me-1 text-muted"></i>Phone Number:</th>
                                                        <td>
                                                            <span id="viewFatherPhone">-</span>
                                                            <a href="javascript:void(0)" onclick="callNumber('viewFatherPhone')" class="ms-2 text-success" title="Call">
                                                                <i class="fas fa-phone-alt"></i>
                                                            </a>
                                                            <a href="javascript:void(0)" onclick="sendSMS('viewFatherPhone')" class="ms-2 text-info" title="SMS">
                                                                <i class="fas fa-comment"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-briefcase me-1 text-muted"></i>Occupation:</th>
                                                        <td><span class="badge bg-soft-success" id="viewFatherOccupation">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-building me-1 text-muted"></i>Employer:</th>
                                                        <td id="viewFatherEmployer">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-map-marker-alt me-1 text-muted"></i>City/State:</th>
                                                        <td id="viewFatherCityState">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-envelope me-1 text-muted"></i>Email:</th>
                                                        <td>
                                                            <span id="viewFatherEmail">-</span>
                                                            <a href="javascript:void(0)" onclick="sendEmail('viewFatherEmail')" class="ms-2 text-info" title="Send Email">
                                                                <i class="fas fa-envelope"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-address-card me-1 text-muted"></i>Address:</th>
                                                        <td id="viewFatherAddress">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mother's Information -->
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header bg-soft-pink">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-tie me-2 text-danger"></i>Mother's Information
                                                    <span class="badge bg-danger ms-2" id="motherStatusBadge"></span>
                                                </h6>
                                            </div>
                                            <div class="info-card-body">
                                                <div class="text-center mb-3" id="motherPhotoSection" style="display: none;">
                                                    <img id="viewMotherPhoto" src="" alt="Mother" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover;">
                                                </div>
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%"><i class="fas fa-user me-1 text-muted"></i>Full Name:</th>
                                                        <td class="fw-semibold" id="viewMotherFullName">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-phone me-1 text-muted"></i>Phone Number:</th>
                                                        <td>
                                                            <span id="viewMotherPhone">-</span>
                                                            <a href="javascript:void(0)" onclick="callNumber('viewMotherPhone')" class="ms-2 text-success" title="Call">
                                                                <i class="fas fa-phone-alt"></i>
                                                            </a>
                                                            <a href="javascript:void(0)" onclick="sendSMS('viewMotherPhone')" class="ms-2 text-info" title="SMS">
                                                                <i class="fas fa-comment"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-briefcase me-1 text-muted"></i>Occupation:</th>
                                                        <td><span class="badge bg-soft-success" id="viewMotherOccupation">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-building me-1 text-muted"></i>Employer:</th>
                                                        <td id="viewMotherEmployer">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-map-marker-alt me-1 text-muted"></i>City/State:</th>
                                                        <td id="viewMotherCityState">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-envelope me-1 text-muted"></i>Email:</th>
                                                        <td>
                                                            <span id="viewMotherEmail">-</span>
                                                            <a href="javascript:void(0)" onclick="sendEmail('viewMotherEmail')" class="ms-2 text-info" title="Send Email">
                                                                <i class="fas fa-envelope"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-address-card me-1 text-muted"></i>Address:</th>
                                                        <td id="viewMotherAddress">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guardian Information (if different from parents) -->
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <div class="info-card-header bg-soft-warning">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-shield me-2 text-warning"></i>Emergency Contact / Guardian
                                                </h6>
                                            </div>
                                            <div class="info-card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless table-sm mb-0">
                                                            <tr>
                                                                <th width="40%">Guardian Name:</th>
                                                                <td class="fw-semibold" id="viewGuardianName">-</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Relationship:</th>
                                                                <td id="viewGuardianRelation">-</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Phone Number:</th>
                                                                <td>
                                                                    <span id="viewGuardianPhone">-</span>
                                                                    <a href="javascript:void(0)" onclick="callNumber('viewGuardianPhone')" class="ms-2 text-success" title="Call">
                                                                        <i class="fas fa-phone-alt"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless table-sm mb-0">
                                                            <tr>
                                                                <th width="40%">Parent's Email:</th>
                                                                <td id="viewParentEmail">-</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Parent's Address:</th>
                                                                <td id="viewParentAddress">-</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. ADDITIONAL INFORMATION TAB -->
                            <div class="tab-pane fade" id="additionalInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-notes-medical me-2 text-info"></i>Medical & Personal</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Blood Group:</th>
                                                        <td><span class="badge bg-danger-light" id="viewBloodGroupAdditional">-</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Genotype:</th>
                                                        <td id="viewGenotype">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Allergies:</th>
                                                        <td id="viewAllergies">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Medical Conditions:</th>
                                                        <td id="viewMedicalConditions">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Disabilities:</th>
                                                        <td id="viewDisabilities">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>NIN Number:</th>
                                                        <td id="viewNIN">-</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Mother Tongue:</th>
                                                        <td id="viewMotherTongue">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card mb-3">
                                            <div class="info-card-header">
                                                <h6 class="mb-0"><i class="fas fa-certificate me-2 text-info"></i>Identification</h6>
                                            </div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th width="40%">Birth Certificate:</th>
                                                        <td><span id="viewBirthCertificate">Not Uploaded</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Previous School Report:</th>
                                                        <td><span id="viewPreviousReport">Not Uploaded</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Health Report:</th>
                                                        <td><span id="viewHealthReport">Not Uploaded</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Immunization Record:</th>
                                                        <td><span id="viewImmunization">Not Uploaded</span></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. TERM HISTORY TAB -->
                            <div class="tab-pane fade" id="termHistory" role="tabpanel">
                                <div class="info-card">
                                    <div class="info-card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Term Registration History</h6>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshTermHistory()">
                                            <i class="fas fa-sync-alt me-1"></i> Refresh
                                        </button>
                                    </div>
                                    <div class="info-card-body">
                                        <div id="termHistoryLoading" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Loading term history...</p>
                                        </div>
                                        <div id="termHistoryContent" style="display: none;">
                                            <!-- Term history table will be inserted here by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <span class="text-muted" id="studentProfileLastUpdated"></span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Close
                                </button>
                                <button type="button" class="btn btn-primary" onclick="editStudentFromView()">
                                    <i class="fas fa-edit me-1"></i> Edit Student
                                </button>
                                <button type="button" class="btn btn-success" onclick="printStudentProfile()">
                                    <i class="fas fa-print me-1"></i> Print Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
 // ============================================================================
// STUDENT MANAGEMENT SYSTEM - COMPLETE OPTIMIZED VERSION
// ============================================================================
// This comprehensive solution includes:
// 1. Server-side pagination with user-controlled items per page
// 2. Enhanced filtering with debouncing
// 3. Working column sorting in Generate Report modal
// 4. Optimized for 5000+ student records
// ============================================================================

(function() {
    'use strict';

    // ============================================================================
    // GLOBAL CONFIGURATION
    // ============================================================================
    const CONFIG = {
        DEFAULT_PER_PAGE: 12,
        PER_PAGE_OPTIONS: [12, 25, 50, 100, 250, 500],
        DEBOUNCE_DELAY: 500,
        MAX_API_RETRIES: 3,
        CACHE_DURATION: 300000, // 5 minutes
        LAZY_LOAD_IMAGES: true,
        ENABLE_LOGGING: false
    };

    // ============================================================================
    // STATE MANAGEMENT - Single source of truth
    // ============================================================================
    const AppState = {
        // Pagination state
        pagination: {
            currentPage: 1,
            perPage: CONFIG.DEFAULT_PER_PAGE,
            total: 0,
            lastPage: 1,
            data: []
        },

        // Filter state
        filters: {
            search: '',
            class: 'all',
            status: 'all',
            gender: 'all'
        },

        // UI state
        ui: {
            currentView: 'table',
            isLoading: false,
            selectedStudents: new Set(),
            lastUpdated: null
        },

        // Cache for frequently accessed data
        cache: {
            students: new Map(),
            stats: null,
            classes: null
        },

        // Report column state
        report: {
            columns: [],
            columnOrder: [],
            sortField: null,
            sortDirection: 'asc'
        }
    };

    // ============================================================================
    // NIGERIAN STATES AND LGAS - Complete dataset
    // ============================================================================
    const NIGERIAN_STATES = [
        { name: "Abia", lgas: ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"] },
        { name: "Adamawa", lgas: ["Demsa", "Fufure", "Ganye", "Gayuk", "Gombi", "Grie", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"] },
        { name: "Akwa Ibom", lgas: ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono-Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat-Enin", "Nsit-Atai", "Nsit-Ibom", "Nsit-Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung-Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"] },
        { name: "Anambra", lgas: ["Aguata", "Anambra East", "Anambra West", "Anaocha", "Awka North", "Awka South", "Ayamelum", "Dunukofia", "Ekwusigo", "Idemili North", "Idemili South", "Ihiala", "Njikoka", "Nnewi North", "Nnewi South", "Ogbaru", "Onitsha North", "Onitsha South", "Orumba North", "Orumba South", "Oyi"] },
        { name: "Bauchi", lgas: ["Alkaleri", "Bauchi", "Bogoro", "Damban", "Darazo", "Dass", "Gamawa", "Ganjuwa", "Giade", "Itas/Gadau", "Jama'are", "Katagum", "Kirfi", "Misau", "Ningi", "Shira", "Tafawa Balewa", "Toro", "Warji", "Zaki"] },
        { name: "Bayelsa", lgas: ["Brass", "Ekeremor", "Kolokuma/Opokuma", "Nembe", "Ogbia", "Sagbama", "Southern Ijaw", "Yenagoa"] },
        { name: "Benue", lgas: ["Ado", "Agatu", "Apa", "Buruku", "Gboko", "Guma", "Gwer East", "Gwer West", "Katsina-Ala", "Konshisha", "Kwande", "Logo", "Makurdi", "Obi", "Ogbadibo", "Ohimini", "Oju", "Okpokwu", "Oturkpo", "Tarka", "Ukum", "Ushongo", "Vandeikya"] },
        { name: "Borno", lgas: ["Abadam", "Askira/Uba", "Bama", "Bayo", "Biu", "Chibok", "Damboa", "Dikwa", "Gubio", "Guzamala", "Gwoza", "Hawul", "Jere", "Kaga", "Kala/Balge", "Konduga", "Kukawa", "Kwaya Kusar", "Mafa", "Magumeri", "Maiduguri", "Marte", "Mobbar", "Monguno", "Ngala", "Nganzai", "Shani"] },
        { name: "Cross River", lgas: ["Abi", "Akamkpa", "Akpabuyo", "Bakassi", "Bekwarra", "Biase", "Boki", "Calabar Municipal", "Calabar South", "Etung", "Ikom", "Obanliku", "Obubra", "Obudu", "Odukpani", "Ogoja", "Yakuur", "Yala"] },
        { name: "Delta", lgas: ["Aniocha North", "Aniocha South", "Bomadi", "Burutu", "Ethiope East", "Ethiope West", "Ika North East", "Ika South", "Isoko North", "Isoko South", "Ndokwa East", "Ndokwa West", "Okpe", "Oshimili North", "Oshimili South", "Patani", "Sapele", "Udu", "Ughelli North", "Ughelli South", "Ukwuani", "Uvwie", "Warri North", "Warri South", "Warri South West"] },
        { name: "Ebonyi", lgas: ["Abakaliki", "Afikpo North", "Afikpo South", "Ebonyi", "Ezza North", "Ezza South", "Ikwo", "Ishielu", "Ivo", "Izzi", "Ohaozara", "Ohaukwu", "Onicha"] },
        { name: "Edo", lgas: ["Akoko-Edo", "Egor", "Esan Central", "Esan North-East", "Esan South-East", "Esan West", "Etsako Central", "Etsako East", "Etsako West", "Igueben", "Ikpoba Okha", "Orhionmwon", "Oredo", "Ovia North-East", "Ovia South-West", "Owan East", "Owan West", "Uhunmwonde"] },
        { name: "Ekiti", lgas: ["Ado Ekiti", "Efon", "Ekiti East", "Ekiti South-West", "Ekiti West", "Emure", "Gbonyin", "Ido Osi", "Ijero", "Ikere", "Ilejemeje", "Irepodun/Ifelodun", "Ise/Orun", "Moba", "Oye"] },
        { name: "Enugu", lgas: ["Aninri", "Awgu", "Enugu East", "Enugu North", "Enugu South", "Ezeagu", "Igbo Etiti", "Igbo Eze North", "Igbo Eze South", "Isi Uzo", "Nkanu East", "Nkanu West", "Nsukka", "Oji River", "Udenu", "Udi", "Uzo Uwani"] },
        { name: "FCT", lgas: ["Abaji", "Bwari", "Gwagwalada", "Kuje", "Kwali", "Municipal Area Council"] },
        { name: "Gombe", lgas: ["Akko", "Balanga", "Billiri", "Dukku", "Funakaye", "Gombe", "Kaltungo", "Kwami", "Nafada", "Shongom", "Yamaltu/Deba"] },
        { name: "Imo", lgas: ["Aboh Mbaise", "Ahiazu Mbaise", "Ehime Mbano", "Ezinihitte", "Ideato North", "Ideato South", "Ihitte/Uboma", "Ikeduru", "Isiala Mbano", "Isu", "Mbaitoli", "Ngor Okpala", "Njaba", "Nkwerre", "Nwangele", "Obowo", "Oguta", "Ohaji/Egbema", "Okigwe", "Orlu", "Orsu", "Oru East", "Oru West", "Owerri Municipal", "Owerri North", "Owerri West", "Unuimo"] },
        { name: "Jigawa", lgas: ["Auyo", "Babura", "Biriniwa", "Birnin Kudu", "Buji", "Dutse", "Gagarawa", "Garki", "Gumel", "Guri", "Gwaram", "Gwiwa", "Hadejia", "Jahun", "Kafin Hausa", "Kazaure", "Kiri Kasama", "Kiyawa", "Kaugama", "Maigatari", "Malam Madori", "Miga", "Ringim", "Roni", "Sule Tankarkar", "Taura", "Yankwashi"] },
        { name: "Kaduna", lgas: ["Birnin Gwari", "Chikun", "Giwa", "Igabi", "Ikara", "Jaba", "Jema'a", "Kachia", "Kaduna North", "Kaduna South", "Kagarko", "Kajuru", "Kaura", "Kauru", "Kubau", "Kudan", "Lere", "Makarfi", "Sabon Gari", "Sanga", "Soba", "Zangon Kataf", "Zaria"] },
        { name: "Kano", lgas: ["Ajingi", "Albasu", "Bagwai", "Bebeji", "Bichi", "Bunkure", "Dala", "Dambatta", "Dawakin Kudu", "Dawakin Tofa", "Doguwa", "Fagge", "Gabasawa", "Garko", "Garun Mallam", "Gaya", "Gezawa", "Gwale", "Gwarzo", "Kabo", "Kano Municipal", "Karaye", "Kibiya", "Kiru", "Kumbotso", "Kunchi", "Kura", "Madobi", "Makoda", "Minjibir", "Nasarawa", "Rano", "Rimin Gado", "Rogo", "Shanono", "Sumaila", "Takai", "Tarauni", "Tofa", "Tsanyawa", "Tudun Wada", "Ungogo", "Warawa", "Wudil"] },
        { name: "Katsina", lgas: ["Bakori", "Batagarawa", "Batsari", "Baure", "Bindawa", "Charanchi", "Dan Musa", "Dandume", "Danja", "Daura", "Dutsi", "Dutsin Ma", "Faskari", "Funtua", "Ingawa", "Jibia", "Kafur", "Kaita", "Kankara", "Kankia", "Katsina", "Kurfi", "Kusada", "Mai'Adua", "Malumfashi", "Mani", "Mashi", "Matazu", "Musawa", "Rimi", "Sabuwa", "Safana", "Sandamu", "Zango"] },
        { name: "Kebbi", lgas: ["Aleiro", "Arewa Dandi", "Argungu", "Augie", "Bagudo", "Birnin Kebbi", "Bunza", "Dandi", "Fakai", "Gwandu", "Jega", "Kalgo", "Koko/Besse", "Maiyama", "Ngaski", "Sakaba", "Shanga", "Suru", "Danko/Wasagu", "Yauri", "Zuru"] },
        { name: "Kogi", lgas: ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa Muro", "Ofu", "Ogori/Magongo", "Okehi", "Okene", "Olamaboro", "Omala", "Yagba East", "Yagba West"] },
        { name: "Kwara", lgas: ["Asa", "Baruten", "Edu", "Ekiti", "Ifelodun", "Ilorin East", "Ilorin South", "Ilorin West", "Irepodun", "Isin", "Kaiama", "Moro", "Offa", "Oke Ero", "Oyun", "Pategi"] },
        { name: "Lagos", lgas: ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"] },
        { name: "Nasarawa", lgas: ["Akwanga", "Awe", "Doma", "Karu", "Keana", "Keffi", "Kokona", "Lafia", "Nasarawa", "Nasarawa Egon", "Obi", "Toto", "Wamba"] },
        { name: "Niger", lgas: ["Agaie", "Agwara", "Bida", "Borgu", "Bosso", "Chanchaga", "Edati", "Gbako", "Gurara", "Katcha", "Kontagora", "Lapai", "Lavun", "Magama", "Mariga", "Mashegu", "Mokwa", "Moya", "Paikoro", "Rafi", "Rijau", "Shiroro", "Suleja", "Tafa", "Wushishi"] },
        { name: "Ogun", lgas: ["Abeokuta North", "Abeokuta South", "Ado-Odo/Ota", "Egbado North", "Egbado South", "Ewekoro", "Ifo", "Ijebu East", "Ijebu North", "Ijebu North East", "Ijebu Ode", "Ikenne", "Imeko Afon", "Ipokia", "Obafemi Owode", "Odeda", "Odogbolu", "Ogun Waterside", "Remo North", "Shagamu"] },
        { name: "Ondo", lgas: ["Akoko North-East", "Akoko North-West", "Akoko South-East", "Akoko South-West", "Akure North", "Akure South", "Ese Odo", "Idanre", "Ifedore", "Ilaje", "Ile Oluji/Okeigbo", "Irele", "Odigbo", "Okitipupa", "Ondo East", "Ondo West", "Ose", "Owo"] },
        { name: "Osun", lgas: ["Aiyedade", "Aiyedire", "Atakunmosa East", "Atakunmosa West", "Boluwaduro", "Boripe", "Ede North", "Ede South", "Egbedore", "Ejigbo", "Ife Central", "Ife East", "Ife North", "Ife South", "Ifedayo", "Ifelodun", "Ila", "Ilesa East", "Ilesa West", "Irepodun", "Irewole", "Isokan", "Iwo", "Obokun", "Odo Otin", "Ola Oluwa", "Olorunda", "Oriade", "Orolu", "Osogbo"] },
        { name: "Oyo", lgas: ["Afijio", "Akinyele", "Atiba", "Atisbo", "Egbeda", "Ibadan North", "Ibadan North-East", "Ibadan North-West", "Ibadan South-East", "Ibadan South-West", "Ibarapa Central", "Ibarapa East", "Ibarapa North", "Ido", "Irepo", "Iseyin", "Itesiwaju", "Iwajowa", "Kajola", "Lagelu", "Ogbomosho North", "Ogbomosho South", "Ogo Oluwa", "Olorunsogo", "Oluyole", "Ona Ara", "Orelope", "Ori Ire", "Oyo East", "Oyo West", "Saki East", "Saki West", "Surulere"] },
        { name: "Plateau", lgas: ["Bokkos", "Barkin Ladi", "Bassa", "Jos East", "Jos North", "Jos South", "Kanam", "Kanke", "Langtang North", "Langtang South", "Mangu", "Mikang", "Pankshin", "Qua'an Pan", "Riyom", "Shendam", "Wase"] },
        { name: "Rivers", lgas: ["Abua/Odual", "Ahoada East", "Ahoada West", "Akuku-Toru", "Andoni", "Asari-Toru", "Bonny", "Degema", "Eleme", "Emohua", "Etche", "Gokana", "Ikwerre", "Khana", "Obio/Akpor", "Ogba/Egbema/Ndoni", "Ogu/Bolo", "Okrika", "Omuma", "Opobo/Nkoro", "Oyigbo", "Port Harcourt", "Tai"] },
        { name: "Sokoto", lgas: ["Binji", "Bodinga", "Dange Shuni", "Gada", "Goronyo", "Gudu", "Gwadabawa", "Illela", "Isa", "Kebbe", "Kware", "Rabah", "Sabon Birni", "Shagari", "Silame", "Sokoto North", "Sokoto South", "Tambuwal", "Tangaza", "Tureta", "Wamako", "Wurno", "Yabo"] },
        { name: "Taraba", lgas: ["Ardo Kola", "Bali", "Donga", "Gashaka", "Gassol", "Ibi", "Jalingo", "Karim Lamido", "Kumi", "Lau", "Sardauna", "Takum", "Ussa", "Wukari", "Yorro", "Zing"] },
        { name: "Yobe", lgas: ["Bade", "Bursari", "Damaturu", "Fika", "Fune", "Geidam", "Gujba", "Gulani", "Jakusko", "Karasuwa", "Machina", "Nangere", "Nguru", "Potiskum", "Tarmuwa", "Yunusari", "Yusufari"] },
        { name: "Zamfara", lgas: ["Anka", "Bakura", "Birnin Magaji/Kiyaw", "Bukkuyum", "Bungudu", "Gummi", "Gusau", "Kaura Namoda", "Maradun", "Maru", "Shinkafi", "Talata Mafara", "Chafe", "Zurmi"] }
    ];

    // ============================================================================
    // AVAILABLE COLUMNS FOR REPORTING - Complete list
    // ============================================================================
    const AVAILABLE_COLUMNS = {
        'photo': 'Photo',
        'admissionNo': 'Admission No',
        'lastname': 'Last Name',
        'firstname': 'First Name',
        'othername': 'Other Name',
        'gender': 'Gender',
        'dateofbirth': 'Date of Birth',
        'age': 'Age',
        'class': 'Class / Arm',
        'status': 'Student Status',
        'admission_date': 'Admission Date',
        'phone_number': 'Phone Number',
        'email': 'Email',
        'state': 'State of Origin',
        'local': 'LGA',
        'religion': 'Religion',
        'blood_group': 'Blood Group',
        'father_name': "Father's Name",
        'mother_name': "Mother's Name",
        'father_phone': "Father's Phone",
        'mother_phone': "Mother's Phone",
        'parent_email': 'Parent Email',
        'guardian_phone': 'Guardian Phone',
        'student_category': 'Student Category',
        'school_house': 'School House',
        'nin_number': 'NIN Number',
        'term': 'Term',
        'session': 'Session'
    };

    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================
    const Utils = {
        log: function(message, data = null, level = 'info') {
            if (!CONFIG.ENABLE_LOGGING) return;
            const timestamp = new Date().toISOString();
            const logFn = level === 'error' ? console.error : console.log;
            if (data) {
                logFn(`[${timestamp}] ${message}:`, data);
            } else {
                logFn(`[${timestamp}] ${message}`);
            }
        },

        escapeHtml: function(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        },

        formatDate: function(dateString, format = 'short') {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';

                if (format === 'short') {
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                } else if (format === 'long') {
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
                return date.toLocaleDateString();
            } catch (e) {
                return 'N/A';
            }
        },

        calculateAge: function(dateOfBirth) {
            if (!dateOfBirth) return 'N/A';
            try {
                const dob = new Date(dateOfBirth);
                if (isNaN(dob.getTime())) return 'N/A';

                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const monthDiff = today.getMonth() - dob.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                return age;
            } catch (e) {
                return 'N/A';
            }
        },

        getInitials: function(firstName, lastName) {
            const first = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
            const last = lastName && lastName.length > 0 ? lastName.charAt(0).toUpperCase() : '';
            return (first + last) || 'ST';
        },

        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        throttle: function(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        showLoading: function() {
            const loadingEl = document.getElementById('loadingState');
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const emptyState = document.getElementById('emptyState');

            if (loadingEl) loadingEl.classList.remove('d-none');
            if (tableView) tableView.classList.add('d-none');
            if (cardView) cardView.classList.add('d-none');
            if (emptyState) emptyState.classList.add('d-none');

            AppState.ui.isLoading = true;
        },

        hideLoading: function() {
            const loadingEl = document.getElementById('loadingState');
            if (loadingEl) loadingEl.classList.add('d-none');

            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');

            if (tableView && AppState.ui.currentView === 'table') {
                tableView.classList.remove('d-none');
            }

            if (cardView && AppState.ui.currentView === 'card') {
                cardView.classList.remove('d-none');
            }

            AppState.ui.isLoading = false;
        },

        showError: function(message, title = 'Error') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-primary' }
                });
            } else {
                alert(`${title}: ${message}`);
            }
        },

        showSuccess: function(message, title = 'Success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                alert(`${title}: ${message}`);
            }
        },

        showConfirm: async function(title, text, confirmText = 'Yes', cancelText = 'Cancel') {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light'
                    }
                });
                return result.isConfirmed;
            }
            return confirm(`${title}: ${text}`);
        },

        initializeTooltips: function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        },

        ensureAxios: function() {
            if (typeof axios === 'undefined') {
                Utils.showError('Axios library is missing. Please refresh the page or contact support.');
                return false;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                Utils.showError('CSRF token not found. Please refresh the page.');
                return false;
            }

            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            return true;
        }
    };

    // ============================================================================
    // API SERVICE - Centralized API calls
    // ============================================================================
    const ApiService = {
        async getStudents(page = 1, perPage = null, filters = null) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            const params = new URLSearchParams();
            params.append('page', page);
            params.append('per_page', perPage || AppState.pagination.perPage);

            const currentFilters = filters || AppState.filters;
            if (currentFilters.search) params.append('search', currentFilters.search);
            if (currentFilters.class && currentFilters.class !== 'all') params.append('class_id', currentFilters.class);
            if (currentFilters.status && currentFilters.status !== 'all') params.append('status', currentFilters.status);
            if (currentFilters.gender && currentFilters.gender !== 'all') params.append('gender', currentFilters.gender);

            try {
                Utils.log('Fetching students', { page, perPage, params: params.toString() });
                const response = await axios.get(`/students/optimized?${params.toString()}`);

                if (response.data.success) {
                    return response.data.data;
                } else {
                    throw new Error(response.data.message || 'Failed to fetch students');
                }
            } catch (error) {
                Utils.log('API Error - getStudents', error, 'error');
                throw error;
            }
        },

        async getStudent(id) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.get(`/student/${id}/edit`);
                return response.data.student || response.data;
            } catch (error) {
                Utils.log('API Error - getStudent', error, 'error');
                throw error;
            }
        },

        async deleteStudent(id) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.delete(`/student/${id}/destroy`);
                return response.data;
            } catch (error) {
                Utils.log('API Error - deleteStudent', error, 'error');
                throw error;
            }
        },

        async deleteMultipleStudents(ids) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.post('/students/destroy-multiple', { ids });
                return response.data;
            } catch (error) {
                Utils.log('API Error - deleteMultipleStudents', error, 'error');
                throw error;
            }
        },

        async getActiveTermSystem() {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.get('/system/active-term-session');
                return response.data;
            } catch (error) {
                Utils.log('API Error - getActiveTermSystem', error, 'error');
                return { success: false, term: null, session: null };
            }
        },

        async getStudentActiveTerm(studentId) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.get(`/student-current-term/student/${studentId}/active`);
                return response.data;
            } catch (error) {
                Utils.log('API Error - getStudentActiveTerm', error, 'error');
                return { success: false, data: null };
            }
        },

        async getStudentAllTerms(studentId) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.get(`/student/${studentId}/all-terms`);
                return response.data;
            } catch (error) {
                Utils.log('API Error - getStudentAllTerms', error, 'error');
                return { success: false, data: [] };
            }
        },

        async updateBulkCurrentTerm(data) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.post('/student-current-term/bulk-update', data);
                return response.data;
            } catch (error) {
                Utils.log('API Error - updateBulkCurrentTerm', error, 'error');
                throw error;
            }
        },

        async generateReport(params) {
            if (!Utils.ensureAxios()) {
                throw new Error('Axios not available');
            }

            try {
                const response = await axios.get('/students/report', {
                    params: params,
                    responseType: 'blob',
                    timeout: 120000
                });
                return response;
            } catch (error) {
                Utils.log('API Error - generateReport', error, 'error');
                throw error;
            }
        }
    };

    // ============================================================================
    // PAGINATION MANAGER - User-controlled items per page
    // ============================================================================
    const PaginationManager = {
        initializePerPageSelector: function() {
            const container = document.querySelector('.pagination-container');
            if (!container) return;

            // Check if per page selector already exists
            if (document.getElementById('perPageSelector')) return;

            const perPageHtml = `
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">Show:</span>
                    <select id="perPageSelector" class="form-select form-select-sm" style="width: auto;">
                        ${CONFIG.PER_PAGE_OPTIONS.map(option =>
                            `<option value="${option}" ${AppState.pagination.perPage === option ? 'selected' : ''}>${option}</option>`
                        ).join('')}
                    </select>
                    <span class="text-muted">entries</span>
                </div>
            `;

            const paginationNav = container.querySelector('nav');
            if (paginationNav) {
                container.insertAdjacentHTML('afterbegin', perPageHtml);
            } else {
                container.insertAdjacentHTML('beforeend', perPageHtml);
            }

            const selector = document.getElementById('perPageSelector');
            if (selector) {
                selector.addEventListener('change', function(e) {
                    const newPerPage = parseInt(e.target.value, 10);
                    AppState.pagination.perPage = newPerPage;
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }
        },

        updatePaginationUI: function(pagination) {
            const paginationContainer = document.querySelector('.pagination');
            if (!paginationContainer) return;

            const currentPageSpan = document.getElementById('currentPage');
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');

            if (currentPageSpan) {
                currentPageSpan.textContent = pagination.current_page;
            }

            // Update prev button
            if (prevBtn) {
                if (pagination.current_page > 1) {
                    prevBtn.classList.remove('disabled');
                    prevBtn.onclick = (e) => {
                        e.preventDefault();
                        AppState.pagination.currentPage = pagination.current_page - 1;
                        StudentManager.fetchStudents();
                    };
                } else {
                    prevBtn.classList.add('disabled');
                    prevBtn.onclick = null;
                }
            }

            // Update next button
            if (nextBtn) {
                if (pagination.current_page < pagination.last_page) {
                    nextBtn.classList.remove('disabled');
                    nextBtn.onclick = (e) => {
                        e.preventDefault();
                        AppState.pagination.currentPage = pagination.current_page + 1;
                        StudentManager.fetchStudents();
                    };
                } else {
                    nextBtn.classList.add('disabled');
                    nextBtn.onclick = null;
                }
            }

            this.updatePageNumbers(pagination);
            this.updateCounts(pagination.total, pagination.data.length);
        },

        updatePageNumbers: function(pagination) {
            const paginationNav = document.querySelector('.pagination');
            if (!paginationNav) return;

            // Remove existing page number buttons
            const pageItems = paginationNav.querySelectorAll('.page-item:not(:first-child):not(:last-child)');
            pageItems.forEach(item => item.remove());

            // Calculate which page numbers to show
            let startPage = Math.max(1, pagination.current_page - 2);
            let endPage = Math.min(pagination.last_page, pagination.current_page + 2);

            // Insert page numbers before the next button
            const nextButton = paginationNav.querySelector('.page-item:last-child');

            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;

                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = 'javascript:void(0);';
                a.textContent = i;
                a.onclick = (e) => {
                    e.preventDefault();
                    AppState.pagination.currentPage = i;
                    StudentManager.fetchStudents();
                };

                li.appendChild(a);
                paginationNav.insertBefore(li, nextButton);
            }

            // Add ellipsis if needed
            if (pagination.last_page > 5 && endPage < pagination.last_page) {
                const ellipsisLi = document.createElement('li');
                ellipsisLi.className = 'page-item disabled';
                ellipsisLi.innerHTML = '<span class="page-link">...</span>';
                paginationNav.insertBefore(ellipsisLi, nextButton);
            }

            if (pagination.last_page > 5 && startPage > 1) {
                // Add ellipsis at beginning
                const firstPage = paginationNav.querySelector('.page-item:first-child');
                const ellipsisLi = document.createElement('li');
                ellipsisLi.className = 'page-item disabled';
                ellipsisLi.innerHTML = '<span class="page-link">...</span>';
                paginationNav.insertBefore(ellipsisLi, paginationNav.children[1]);
            }
        },

        updateCounts: function(total, showing) {
            const totalStudentsEl = document.getElementById('totalStudents');
            const totalCountEl = document.getElementById('totalCount');
            const showingCountEl = document.getElementById('showingCount');

            if (totalStudentsEl) totalStudentsEl.textContent = total;
            if (totalCountEl) totalCountEl.textContent = total;
            if (showingCountEl) showingCountEl.textContent = showing;
        }
    };

    // ============================================================================
    // FILTER MANAGER - Enhanced filtering with debouncing
    // ============================================================================
    const FilterManager = {
        debouncedSearch: Utils.debounce(function() {
            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        }, CONFIG.DEBOUNCE_DELAY),

        initializeFilters: function() {
            const searchInput = document.getElementById('search-input');
            const classFilter = document.getElementById('schoolclass-filter');
            const statusFilter = document.getElementById('status-filter');
            const genderFilter = document.getElementById('gender-filter');

            if (searchInput) {
                searchInput.addEventListener('input', () => this.debouncedSearch());
            }

            if (classFilter) {
                classFilter.addEventListener('change', () => {
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }

            if (statusFilter) {
                statusFilter.addEventListener('change', () => {
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }

            if (genderFilter) {
                genderFilter.addEventListener('change', () => {
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }
        },

        applyFilters: function() {
            const searchInput = document.getElementById('search-input');
            const classFilter = document.getElementById('schoolclass-filter');
            const statusFilter = document.getElementById('status-filter');
            const genderFilter = document.getElementById('gender-filter');

            AppState.filters = {
                search: searchInput ? searchInput.value : '',
                class: classFilter ? classFilter.value : 'all',
                status: statusFilter ? statusFilter.value : 'all',
                gender: genderFilter ? genderFilter.value : 'all'
            };

            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        },

        resetFilters: function() {
            const searchInput = document.getElementById('search-input');
            const classFilter = document.getElementById('schoolclass-filter');
            const statusFilter = document.getElementById('status-filter');
            const genderFilter = document.getElementById('gender-filter');

            if (searchInput) searchInput.value = '';
            if (classFilter) classFilter.value = 'all';
            if (statusFilter) statusFilter.value = 'all';
            if (genderFilter) genderFilter.value = 'all';

            AppState.filters = {
                search: '',
                class: 'all',
                status: 'all',
                gender: 'all'
            };

            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        }
    };

    // ============================================================================
    // STUDENT MANAGER - Core student operations
    // ============================================================================
    const StudentManager = {
        async fetchStudents() {
            Utils.showLoading();

            try {
                const paginationData = await ApiService.getStudents(
                    AppState.pagination.currentPage,
                    AppState.pagination.perPage,
                    AppState.filters
                );

                AppState.pagination = {
                    ...AppState.pagination,
                    currentPage: paginationData.current_page,
                    lastPage: paginationData.last_page,
                    total: paginationData.total,
                    data: paginationData.data
                };

                // Render based on current view
                if (AppState.ui.currentView === 'table') {
                    RenderManager.renderTableView(paginationData.data);
                } else {
                    RenderManager.renderCardView(paginationData.data);
                }

                // Update UI
                PaginationManager.updatePaginationUI(paginationData);
                SelectionManager.updateBulkActionsVisibility();

                // Update cache
                paginationData.data.forEach(student => {
                    AppState.cache.students.set(student.id.toString(), student);
                });

                Utils.log('Students fetched successfully', {
                    total: paginationData.total,
                    showing: paginationData.data.length
                });

            } catch (error) {
                Utils.log('Error fetching students', error, 'error');
                Utils.showError('Failed to load students. Please try again.');

                const emptyState = document.getElementById('emptyState');
                if (emptyState) emptyState.classList.remove('d-none');
            } finally {
                Utils.hideLoading();
            }
        },

        async viewStudent(id) {
            try {
                Utils.showLoading();

                // Try to get from cache first
                let student = AppState.cache.students.get(id.toString());

                if (!student) {
                    student = await ApiService.getStudent(id);
                    if (student && student.id) {
                        AppState.cache.students.set(id.toString(), student);
                    }
                }

                Utils.hideLoading();

                if (student) {
                    ViewModalManager.populateEnhancedViewModal(student);

                    const viewModalElement = document.getElementById('viewStudentModal');
                    if (viewModalElement) {
                        const viewModal = new bootstrap.Modal(viewModalElement);
                        viewModal.show();

                        // Fetch term history after modal is shown
                        viewModalElement.addEventListener('shown.bs.modal', function onShown() {
                            ViewModalManager.fetchStudentTermInfo(student.id);
                            this.removeEventListener('shown.bs.modal', onShown);
                        });
                    }
                }
            } catch (error) {
                Utils.hideLoading();
                Utils.log('Error viewing student', error, 'error');
                Utils.showError('Failed to load student data.');
            }
        },

        async editStudent(id) {
            try {
                Utils.showLoading();

                const student = await ApiService.getStudent(id);

                Utils.hideLoading();

                EditFormManager.populateEditForm(student);

                const editModalElement = document.getElementById('editStudentModal');
                if (editModalElement) {
                    const editModal = new bootstrap.Modal(editModalElement);
                    editModal.show();
                }
            } catch (error) {
                Utils.hideLoading();
                Utils.log('Error editing student', error, 'error');
                Utils.showError('Failed to load student for editing.');
            }
        },

        async deleteStudent(id) {
            const confirmed = await Utils.showConfirm(
                'Delete Student',
                'You won\'t be able to revert this!',
                'Yes, delete it!'
            );

            if (confirmed) {
                try {
                    await ApiService.deleteStudent(id);

                    // Remove from cache
                    AppState.cache.students.delete(id.toString());

                    // Refresh current page
                    await this.fetchStudents();

                    Utils.showSuccess('Student has been deleted.');
                } catch (error) {
                    Utils.log('Error deleting student', error, 'error');
                    Utils.showError('Failed to delete student.');
                }
            }
        },

        async deleteMultiple() {
            const selectedIds = SelectionManager.getSelectedStudentIds();

            if (selectedIds.length === 0) {
                Utils.showError('Please select at least one student to delete.', 'No Selection');
                return;
            }

            const confirmed = await Utils.showConfirm(
                `Delete ${selectedIds.length} Students?`,
                "This action cannot be undone!",
                'Yes, delete them!'
            );

            if (confirmed) {
                try {
                    await ApiService.deleteMultipleStudents(selectedIds);

                    // Remove from cache
                    selectedIds.forEach(id => AppState.cache.students.delete(id.toString()));

                    // Refresh current page
                    await this.fetchStudents();

                    Utils.showSuccess(`${selectedIds.length} student(s) have been deleted.`);

                    // Clear selections
                    SelectionManager.clearAllSelections();

                } catch (error) {
                    Utils.log('Error deleting multiple students', error, 'error');
                    Utils.showError('Failed to delete selected students.');
                }
            }
        }
    };

    // ============================================================================
    // RENDER MANAGER - Optimized rendering for large datasets
    // ============================================================================
    const RenderManager = {
        renderTableView: function(students) {
            const tbody = document.getElementById('studentTableBody');
            if (!tbody) return;

            if (!students || students.length === 0) {
                tbody.innerHTML = '';
                const emptyState = document.getElementById('emptyState');
                if (emptyState) emptyState.classList.remove('d-none');
                return;
            }

            const emptyState = document.getElementById('emptyState');
            if (emptyState) emptyState.classList.add('d-none');

            // Use DocumentFragment for better performance
            const fragment = document.createDocumentFragment();

            students.forEach(student => {
                const row = document.createElement('tr');
                row.className = 'align-middle';
                row.dataset.id = student.id;

                row.innerHTML = `
                    <td>
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox"
                                   value="${student.id}" onchange="window.updateBulkActionsVisibility()">
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                ${this.getOptimizedAvatar(student)}
                                <span class="position-absolute bottom-0 end-0 ${student.student_status === 'Active' ? 'bg-success' : 'bg-secondary'} rounded-circle p-1 border border-2 border-white"
                                      style="width: 12px; height: 12px;"></span>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold">${Utils.escapeHtml(student.lastname || '')} ${Utils.escapeHtml(student.firstname || '')}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                        <i class="fas fa-id-card me-1 text-muted" style="font-size: 10px;"></i>
                                        ${Utils.escapeHtml(student.admissionNo || 'N/A')}
                                    </span>
                                    ${student.statusId == 2 ?
                                        '<span class="badge bg-warning bg-gradient text-dark px-2 py-1 rounded-pill"><i class="fas fa-star me-1" style="font-size: 10px;"></i>New</span>' :
                                        student.statusId == 1 ?
                                        '<span class="badge bg-secondary bg-gradient px-2 py-1 rounded-pill"><i class="fas fa-history me-1" style="font-size: 10px;"></i>Old</span>' : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</span>
                            <small class="text-muted">${Utils.escapeHtml(student.student_category || '')}</small>
                        </div>
                    </td>
                    <td>
                        ${this.getCompactStatusBadge(student)}
                    </td>
                    <td>
                        <span class="d-flex align-items-center gap-1">
                            <i class="fas fa-${student.gender === 'Male' ? 'mars text-primary' : 'venus text-pink'}"></i>
                            ${Utils.escapeHtml(student.gender || 'N/A')}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <i class="fas fa-calendar-alt text-muted" style="font-size: 12px;"></i>
                            <span>${Utils.formatDate(student.created_at, 'short')}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2 justify-content-end">
                            <div class="btn-group" role="group">
                                <button type="button"
                                        class="btn btn-sm btn-soft-info rounded-start"
                                        onclick="window.viewStudent(${student.id})"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="View Student Details">
                                    <i class="fas fa-eye"></i>
                                    <span class="d-none d-xl-inline-block ms-1">View</span>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-soft-warning"
                                        onclick="window.editStudent(${student.id})"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Edit Student">
                                    <i class="fas fa-edit"></i>
                                    <span class="d-none d-xl-inline-block ms-1">Edit</span>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-soft-danger rounded-end"
                                        onclick="window.deleteStudent(${student.id})"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Delete Student">
                                    <i class="fas fa-trash-alt"></i>
                                    <span class="d-none d-xl-inline-block ms-1">Delete</span>
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                fragment.appendChild(row);
            });

            tbody.innerHTML = '';
            tbody.appendChild(fragment);

            // Initialize tooltips
            Utils.initializeTooltips();

            // Update checkAll state
            this.updateCheckAllState();
        },

        renderCardView: function(students) {
            const container = document.getElementById('studentsCardsContainer');
            if (!container) return;

            if (!students || students.length === 0) {
                container.innerHTML = '';
                const emptyState = document.getElementById('emptyState');
                if (emptyState) emptyState.classList.remove('d-none');
                return;
            }

            const emptyState = document.getElementById('emptyState');
            if (emptyState) emptyState.classList.add('d-none');

            // Use DocumentFragment for better performance
            const fragment = document.createDocumentFragment();

            students.forEach(student => {
                const col = document.createElement('div');
                col.className = 'col-xl-3 col-lg-4 col-md-6 mb-4';

                col.innerHTML = `
                    <div class="student-profile-card" data-id="${student.id}">
                        <div class="checkbox-container">
                            <div class="form-check">
                                <input class="form-check-input student-checkbox" type="checkbox"
                                       value="${student.id}" onchange="window.updateBulkActionsVisibility()">
                            </div>
                        </div>
                        <div class="card-header">
                            <div class="header-content">
                                <h5 class="student-name">${Utils.escapeHtml(student.lastname || '')} ${Utils.escapeHtml(student.firstname || '')}</h5>
                                <span class="student-admission">${Utils.escapeHtml(student.admissionNo || 'N/A')}</span>
                            </div>
                            <div class="avatar-container">
                                ${this.getOptimizedAvatar(student, true)}
                            </div>
                        </div>
                        <div class="card-body">
                            ${this.getStatusBadge(student, true)}
                            <div class="student-info-grid">
                                <div class="info-item">
                                    <span class="info-label">Class</span>
                                    <span class="info-value">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Gender</span>
                                    <span class="info-value">${Utils.escapeHtml(student.gender || 'N/A')}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Age</span>
                                    <span class="info-value">${Utils.escapeHtml(student.age || 'N/A')}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Registered</span>
                                    <span class="info-value">${Utils.formatDate(student.created_at, 'short')}</span>
                                </div>
                            </div>
                            <div class="action-buttons">
                                <button class="action-btn view-btn" onclick="window.viewStudent(${student.id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn edit-btn" onclick="window.editStudent(${student.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="action-btn delete-btn" onclick="window.deleteStudent(${student.id})">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                fragment.appendChild(col);
            });

            container.innerHTML = '';
            container.appendChild(fragment);

            this.updateCheckAllState();
        },

        getOptimizedAvatar: function(student, isCard = false) {
            const initials = Utils.getInitials(student.firstname, student.lastname);
            const colors = ['#4361ee', '#3a0ca3', '#f72585', '#4cc9f0', '#7209b7', '#f8961e', '#2ec4b6', '#e71d36'];
            const colorIndex = (student.id?.toString().length || 0) % colors.length;
            const bgColor = colors[colorIndex];
            const size = isCard ? '80px' : '45px';
            const fontSize = isCard ? '28px' : '16px';

            if (student.picture && student.picture !== 'unnamed.jpg') {
                return `
                    <div class="avatar-circle" style="width: ${size}; height: ${size};">
                        <img src="/storage/images/student_avatars/${Utils.escapeHtml(student.picture)}"
                             alt="${Utils.escapeHtml(student.firstname || 'Student')}"
                             class="rounded-circle border border-2 border-white shadow-sm"
                             style="width: ${size}; height: ${size}; object-fit: cover;"
                             loading="lazy"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-initials rounded-circle border border-2 border-white shadow-sm"
                             style="width: ${size}; height: ${size}; background: ${bgColor}; color: white; display: none; align-items: center; justify-content: center; font-weight: bold; font-size: ${fontSize};">
                            ${initials}
                        </div>
                    </div>
                `;
            }

            return `
                <div class="avatar-initials rounded-circle border border-2 border-white shadow-sm"
                     style="width: ${size}; height: ${size}; background: ${bgColor}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: ${fontSize};">
                    ${initials}
                </div>
            `;
        },

        getStatusBadge: function(student, isCard = false) {
            let badges = '';

            if (student.student_status === 'Active') {
                badges += `<span class="status-badge status-active">
                            <i class="fas fa-check-circle"></i> Active
                        </span>`;
            } else if (student.student_status === 'Inactive') {
                badges += `<span class="status-badge status-inactive">
                            <i class="fas fa-pause-circle"></i> Inactive
                        </span>`;
            }

            if (student.statusId == 2) {
                badges += `<span class="status-badge status-new ${!isCard ? 'ms-2' : ''}">
                            <i class="fas fa-star"></i> New Student
                        </span>`;
            } else if (student.statusId == 1) {
                badges += `<span class="status-badge status-old ${!isCard ? 'ms-2' : ''}">
                            <i class="fas fa-history"></i> Old Student
                        </span>`;
            }

            return badges;
        },

        getCompactStatusBadge: function(student) {
            let badges = '';

            if (student.student_status === 'Active') {
                badges += '<span class="badge bg-success bg-gradient px-2 py-1 rounded-pill"><span class="status-dot active"></span>Active</span>';
            } else if (student.student_status === 'Inactive') {
                badges += '<span class="badge bg-secondary bg-gradient px-2 py-1 rounded-pill"><span class="status-dot inactive"></span>Inactive</span>';
            }

            return badges;
        },

        updateCheckAllState: function() {
            const checkAll = document.getElementById('checkAll');
            const checkAllTable = document.getElementById('checkAllTable');

            const totalCheckboxes = document.querySelectorAll('.student-checkbox').length;
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked').length;

            if (checkAll) {
                checkAll.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
                checkAll.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }

            if (checkAllTable) {
                checkAllTable.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
                checkAllTable.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }
        },

        toggleView: function(viewType) {
            AppState.ui.currentView = viewType;

            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');

            if (!tableView || !cardView || !tableViewBtn || !cardViewBtn) return;

            if (viewType === 'table') {
                tableView.classList.remove('d-none');
                cardView.classList.add('d-none');
                tableViewBtn.classList.add('active');
                cardViewBtn.classList.remove('active');

                if (AppState.pagination.data.length > 0) {
                    this.renderTableView(AppState.pagination.data);
                }
            } else {
                tableView.classList.add('d-none');
                cardView.classList.remove('d-none');
                tableViewBtn.classList.remove('active');
                cardViewBtn.classList.add('active');

                if (AppState.pagination.data.length > 0) {
                    this.renderCardView(AppState.pagination.data);
                }
            }
        }
    };

    // ============================================================================
    // SELECTION MANAGER - Bulk selection handling
    // ============================================================================
    const SelectionManager = {
        toggleSelectAll: function(e) {
            const isChecked = e.target.checked;
            const checkboxes = document.querySelectorAll('.student-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) {
                    parent.classList.toggle('selected', isChecked);
                }

                if (isChecked) {
                    AppState.ui.selectedStudents.add(checkbox.value);
                } else {
                    AppState.ui.selectedStudents.delete(checkbox.value);
                }
            });

            RenderManager.updateCheckAllState();
            this.updateBulkActionsVisibility();
        },

        updateBulkActionsVisibility: function() {
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

            // Update selected students set
            AppState.ui.selectedStudents.clear();
            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                AppState.ui.selectedStudents.add(cb.value);
            });
        },

        getSelectedStudentIds: function() {
            return Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        },

        clearAllSelections: function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) {
                    parent.classList.remove('selected');
                }
            });

            AppState.ui.selectedStudents.clear();

            const checkAll = document.getElementById('checkAll');
            const checkAllTable = document.getElementById('checkAllTable');

            if (checkAll) checkAll.checked = false;
            if (checkAllTable) checkAllTable.checked = false;

            this.updateBulkActionsVisibility();
        },

        initializeCheckboxes: function() {
            const checkAll = document.getElementById('checkAll');
            const checkAllTable = document.getElementById('checkAllTable');

            if (checkAll) {
                checkAll.removeEventListener('change', this.toggleSelectAll);
                checkAll.addEventListener('change', (e) => this.toggleSelectAll(e));
            }

            if (checkAllTable) {
                checkAllTable.removeEventListener('change', this.toggleSelectAll);
                checkAllTable.addEventListener('change', (e) => this.toggleSelectAll(e));
            }

            // Delegate event listener for dynamic checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('student-checkbox')) {
                    SelectionManager.updateBulkActionsVisibility();
                    RenderManager.updateCheckAllState();
                }
            });
        }
    };

    // ============================================================================
    // VIEW MODAL MANAGER - Enhanced student profile modal
    // ============================================================================
    const ViewModalManager = {
        populateEnhancedViewModal: function(student) {
            Utils.log('Populating enhanced view modal', student);

            const setText = (id, value, defaultValue = '-') => {
                const element = document.getElementById(id);
                if (element) element.textContent = value || defaultValue;
            };

            const setHtml = (id, html) => {
                const element = document.getElementById(id);
                if (element) element.innerHTML = html;
            };

            // Set student photo
            const photoElement = document.getElementById('viewStudentPhoto');
            if (photoElement) {
                if (student.picture && student.picture !== 'unnamed.jpg') {
                    photoElement.src = `/storage/images/student_avatars/${student.picture}`;
                    photoElement.onerror = function() {
                        this.src = '/theme/layouts/assets/media/avatars/blank.png';
                    };
                } else {
                    photoElement.src = '/theme/layouts/assets/media/avatars/blank.png';
                }
            }

            // Set status indicator
            const statusIndicator = document.getElementById('studentStatusIndicator');
            if (statusIndicator) {
                const isActive = student.student_status === 'Active';
                statusIndicator.className = `position-absolute bottom-0 end-0 rounded-circle p-2 border border-2 border-white ${isActive ? 'bg-success' : 'bg-secondary'}`;
                statusIndicator.title = isActive ? 'Active Student' : 'Inactive Student';
            }

            // Basic Information
            const fullName = `${student.lastname || ''} ${student.firstname || ''} ${student.othername || ''}`.trim();
            setText('viewFullName', fullName);
            setText('viewFullNameDetail', fullName);
            setText('viewAdmissionNumber', student.admissionNo);
            setText('viewAdmissionNo', student.admissionNo);

            // Class Display
            const classDisplay = `${student.schoolclass || ''} ${student.arm ? '- ' + student.arm : ''}`.trim();
            setText('viewClassDisplay', classDisplay);
            setText('viewCurrentClass', classDisplay);
            setHtml('viewClassBadge', `<i class="fas fa-school me-1"></i>${classDisplay || 'Not Assigned'}`);

            // Student Type
            const studentType = student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : 'N/A';
            setText('viewStudentType', studentType);
            setHtml('viewStudentTypeBadge', `<i class="fas fa-user-tag me-1"></i>${studentType}`);

            // Admission Date
            if (student.admission_date) {
                setText('viewAdmittedDate', Utils.formatDate(student.admission_date, 'long'));
                setText('viewAdmissionDate', Utils.formatDate(student.admission_date));
            }

            // Gender
            const gender = student.gender || '-';
            const genderIcon = gender === 'Male' ? 'mars' : gender === 'Female' ? 'venus' : 'genderless';
            setText('viewGenderText', gender);
            setHtml('viewGenderDetail', `<i class="fas fa-${genderIcon} me-1"></i>${gender}`);

            // Age
            const age = student.age || Utils.calculateAge(student.dateofbirth) || 'N/A';
            setText('viewAge', age);
            setText('viewAgeDetail', age);

            // Title
            setText('viewTitle', student.title || '-');

            // Date of Birth
            if (student.dateofbirth) {
                setText('viewDOB', Utils.formatDate(student.dateofbirth, 'long'));
            } else {
                setText('viewDOB', '-');
            }

            // Place of Birth
            setText('viewPlaceOfBirth', student.placeofbirth || '-');

            // Blood Group
            setText('viewBloodGroupDetail', student.blood_group || '-');
            setText('viewBloodGroupAdditional', student.blood_group || '-');

            // Religion
            setText('viewReligionDetail', student.religion || '-');

            // Contact Information
            setText('viewPhoneNumber', student.phone_number || '-');
            setText('viewEmailAddress', student.email || '-');
            setText('viewPermanentAddress', student.permanent_address || '-');
            setText('viewCity', student.city || '-');
            setText('viewStateOrigin', student.state || '-');
            setText('viewLGA', student.local || '-');
            setText('viewNationality', student.nationality || '-');

            // Future Ambition
            setText('viewFutureAmbition', student.future_ambition || '-');

            // Academic Information
            setText('viewArm', student.arm || '-');
            setText('viewStudentCategory', student.student_category || '-');

            // Student Status Badge
            const studentStatus = student.student_status || 'Inactive';
            const statusBadgeClass = studentStatus === 'Active' ? 'bg-success' : 'bg-secondary';
            const statusIcon = studentStatus === 'Active' ? 'check-circle' : 'pause-circle';
            setHtml('viewStudentStatus', `<span class="badge ${statusBadgeClass}"><i class="fas fa-${statusIcon} me-1"></i>${studentStatus}</span>`);

            // School House
            setText('viewSchoolHouse', student.school_house || student.sport_house || '-');

            // Previous School
            setText('viewLastSchool', student.last_school || '-');
            setText('viewLastClass', student.last_class || '-');
            setText('viewReasonForLeaving', student.reason_for_leaving || '-');

            // Parent Information - Father
            setText('viewFatherFullName', student.father_name || student.father || '-');
            setText('viewFatherPhone', student.father_phone || '-');
            setText('viewFatherOccupation', student.father_occupation || '-');
            setText('viewFatherCityState', student.father_city || '-');
            setText('viewFatherAddress', student.office_address || '-');

            const fatherStatusBadge = document.getElementById('fatherStatusBadge');
            if (fatherStatusBadge) {
                if (student.father_name || student.father) {
                    fatherStatusBadge.textContent = 'Active Contact';
                    fatherStatusBadge.className = 'badge bg-success ms-2';
                } else {
                    fatherStatusBadge.textContent = 'Not Provided';
                    fatherStatusBadge.className = 'badge bg-secondary ms-2';
                }
            }

            // Parent Information - Mother
            setText('viewMotherFullName', student.mother_name || student.mother || '-');
            setText('viewMotherPhone', student.mother_phone || '-');
            setText('viewParentEmail', student.parent_email || '-');
            setText('viewParentAddress', student.parent_address || '-');

            const motherStatusBadge = document.getElementById('motherStatusBadge');
            if (motherStatusBadge) {
                if (student.mother_name || student.mother) {
                    motherStatusBadge.textContent = 'Active Contact';
                    motherStatusBadge.className = 'badge bg-danger ms-2';
                } else {
                    motherStatusBadge.textContent = 'Not Provided';
                    motherStatusBadge.className = 'badge bg-secondary ms-2';
                }
            }

            // Additional Information
            setText('viewNIN', student.nin_number || '-');
            setText('viewMotherTongue', student.mother_tongue || '-');

            // Set last updated timestamp
            const now = new Date();
            setText('studentProfileLastUpdated', `Last updated: ${now.toLocaleDateString()} ${now.toLocaleTimeString()}`);
        },

        async fetchStudentTermInfo(studentId) {
            try {
                // Get system active term
                const systemInfo = await ApiService.getActiveTermSystem();

                // Get student's active term
                const activeResponse = await ApiService.getStudentActiveTerm(studentId);
                const activeTerm = activeResponse.success ? activeResponse.data : null;

                const setText = (id, value) => {
                    const element = document.getElementById(id);
                    if (element) element.textContent = value || '-';
                };

                const setHtml = (id, html) => {
                    const element = document.getElementById(id);
                    if (element) element.innerHTML = html;
                };

                if (systemInfo.term) {
                    setText('viewCurrentTerm', systemInfo.term.term || systemInfo.term.name || 'Not Set');
                    setText('viewCurrentSession', systemInfo.session?.session || systemInfo.session?.name || 'Not Set');

                    const isActiveInCurrentTerm = activeTerm &&
                        activeTerm.term_id == systemInfo.term.id &&
                        activeTerm.session_id == systemInfo.session.id;

                    const alertElement = document.getElementById('currentTermAlert');
                    if (alertElement) {
                        if (isActiveInCurrentTerm) {
                            alertElement.innerHTML = `
                                <div class="alert alert-success py-2 px-3 mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Active in Current Term</strong>
                                    <p class="mb-0 small">Student is registered and active in the current academic term.</p>
                                </div>
                            `;
                            setHtml('viewCurrentTermStatus', '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>');
                        } else {
                            alertElement.innerHTML = `
                                <div class="alert alert-warning py-2 px-3 mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Not in Current Term</strong>
                                    <p class="mb-0 small">Student is not registered for the current academic term.</p>
                                </div>
                            `;
                            setHtml('viewCurrentTermStatus', '<span class="badge bg-warning"><i class="fas fa-pause-circle me-1"></i>Not Registered</span>');
                        }
                    }
                }

                // Fetch term history
                await this.fetchTermHistory(studentId);

            } catch (error) {
                Utils.log('Error fetching term information', error, 'error');
            }
        },

        async fetchTermHistory(studentId) {
            try {
                const response = await ApiService.getStudentAllTerms(studentId);
                const terms = response.success ? response.data : [];

                const loadingElement = document.getElementById('termHistoryLoading');
                const contentElement = document.getElementById('termHistoryContent');

                if (loadingElement) loadingElement.style.display = 'none';
                if (contentElement) contentElement.style.display = 'block';

                if (!terms || terms.length === 0) {
                    contentElement.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No term registration history found.</p>
                        </div>
                    `;
                    return;
                }

                let tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>S/N</th>
                                    <th>Term</th>
                                    <th>Session</th>
                                    <th>Class</th>
                                    <th>Arm</th>
                                    <th>Status</th>
                                    <th>Registered Date</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                terms.forEach((term, index) => {
                    const isCurrent = term.is_current ?
                        '<span class="badge bg-info"><i class="fas fa-star me-1"></i>Current</span>' :
                        '<span class="badge bg-secondary">Past</span>';

                    const regDate = term.created_at ? Utils.formatDate(term.created_at) : 'N/A';

                    tableHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><span class="fw-semibold">${Utils.escapeHtml(term.term_name || 'N/A')}</span></td>
                            <td>${Utils.escapeHtml(term.session_name || 'N/A')}</td>
                            <td>${Utils.escapeHtml(term.class_name || 'N/A')}</td>
                            <td>${Utils.escapeHtml(term.arm_name || 'N/A')}</td>
                            <td>${isCurrent}</td>
                            <td>${regDate}</td>
                        </tr>
                    `;
                });

                tableHtml += `
                            </tbody>
                        </table>
                    </div>
                `;

                contentElement.innerHTML = tableHtml;

            } catch (error) {
                Utils.log('Error fetching term history', error, 'error');
                const loadingElement = document.getElementById('termHistoryLoading');
                const contentElement = document.getElementById('termHistoryContent');

                if (loadingElement) loadingElement.style.display = 'none';
                if (contentElement) {
                    contentElement.style.display = 'block';
                    contentElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to load term history. Please try again.
                        </div>
                    `;
                }
            }
        }
    };

    // ============================================================================
    // EDIT FORM MANAGER - Student edit functionality
    // ============================================================================
    const EditFormManager = {
        populateEditForm: function(student) {
            Utils.log('Populating edit form', student);

            const fields = [
                { id: 'editStudentId', value: student.id },
                { id: 'editAdmissionNo', value: student.admissionNo || '' },
                { id: 'editAdmissionYear', value: student.admissionYear || '' },
                { id: 'editAdmissionDate', value: student.admissionDate || (student.admission_date ? student.admission_date.split('T')[0] : '') },
                { id: 'editTitle', value: student.title || '' },
                { id: 'editFirstname', value: student.firstname || '' },
                { id: 'editLastname', value: student.lastname || '' },
                { id: 'editOthername', value: student.othername || '' },
                { id: 'editPermanentAddress', value: student.permanent_address || student.home_address2 || '' },
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
                { id: 'editFatherName', value: student.father_name || student.father || '' },
                { id: 'editFatherPhone', value: student.father_phone || '' },
                { id: 'editFatherOccupation', value: student.father_occupation || '' },
                { id: 'editFatherCity', value: student.father_city || '' },
                { id: 'editMotherName', value: student.mother_name || student.mother || '' },
                { id: 'editMotherPhone', value: student.mother_phone || '' },
                { id: 'editParentEmail', value: student.parent_email || '' },
                { id: 'editParentAddress', value: student.parent_address || '' },
                { id: 'editStudentCategory', value: student.student_category || '' },
                { id: 'editSchoolHouse', value: student.schoolhouseid || student.school_house || student.sport_house || '' },
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

            // Set status (Old/New)
            const statusRadios = document.querySelectorAll('#editStudentModal input[name="statusId"]');
            if (statusRadios.length > 0) {
                const studentStatusId = student.statusId || '';
                statusRadios.forEach(radio => {
                    radio.checked = (parseInt(radio.value) === parseInt(studentStatusId));
                });
            }

            // Set student activity status
            const studentStatusRadios = document.querySelectorAll('#editStudentModal input[name="student_status"]');
            if (studentStatusRadios.length > 0) {
                const studentActivityStatus = student.student_status || '';
                studentStatusRadios.forEach(radio => {
                    radio.checked = (radio.value === studentActivityStatus);
                });
            }

            // Set admission mode
            if (student.admissionNo) {
                const autoMode = document.getElementById('editAdmissionAuto');
                const manualMode = document.getElementById('editAdmissionManual');
                if (autoMode && manualMode) {
                    // Default to manual mode when editing
                    manualMode.checked = true;
                    autoMode.checked = false;
                }
            }

            // Set avatar
            const avatarElement = document.getElementById('editStudentAvatar');
            if (avatarElement) {
                if (student.picture && student.picture !== 'unnamed.jpg') {
                    const avatarUrl = `/storage/images/student_avatars/${student.picture}`;
                    avatarElement.src = avatarUrl;
                } else {
                    avatarElement.src = '/theme/layouts/assets/media/avatars/blank.png';
                }
            }

            // Set state and LGA
            if (student.state || student.local) {
                this.setEditStateAndLGA(student.state, student.local);
            }

            // Calculate age
            if (student.dateofbirth) {
                const age = Utils.calculateAge(student.dateofbirth);
                const ageInput = document.getElementById('editAgeInput');
                if (ageInput) ageInput.value = age;
            }

            // Update form action
            const form = document.getElementById('editStudentForm');
            if (form && student.id) {
                form.action = `/student/${student.id}`;
            }
        },

        setEditStateAndLGA: function(stateName, lgaName) {
            const stateSelect = document.getElementById('editState');
            const lgaSelect = document.getElementById('editLocal');

            if (!stateSelect || !lgaSelect) return;

            // Set state value
            if (stateName) {
                stateSelect.value = stateName;

                // Trigger change event to populate LGAs
                const event = new Event('change', { bubbles: true });
                stateSelect.dispatchEvent(event);

                // Set LGA value after a short delay
                setTimeout(() => {
                    if (lgaName) {
                        lgaSelect.value = lgaName;
                    }
                }, 100);
            }
        },

        resetEditStateDropdown: function() {
            const stateSelect = document.getElementById('editState');
            const lgaSelect = document.getElementById('editLocal');

            if (stateSelect) {
                stateSelect.value = '';
            }
            if (lgaSelect) {
                lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                lgaSelect.disabled = true;
            }
        }
    };

    // ============================================================================
    // REPORT MANAGER - Complete column sorting and report generation
    // ============================================================================
    const ReportManager = {
        sortableInstance: null,
        columnOrder: [],

        initializeReportModal: function() {
            Utils.log('Initializing report modal...');

            const container = document.getElementById('columnsContainer');
            if (!container) {
                Utils.log('Column container not found', null, 'error');
                return;
            }

            // Initialize column ordering with Sortable.js
            this.initializeColumnOrdering();

            // Set up event listeners for checkboxes
            container.querySelectorAll('.column-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', () => this.updateColumnOrder());
                checkbox.addEventListener('change', () => this.updatePreview());
            });

            // Initialize column sorting
            this.initializeColumnSorting();

            // Initial preview update
            this.updatePreview();

            Utils.log('Report modal initialized');
        },

        initializeColumnOrdering: function() {
            const container = document.getElementById('columnsContainer');
            const hiddenOrderInput = document.getElementById('columnsOrderInput');

            if (!container || !hiddenOrderInput) {
                Utils.log('Column container or hidden input not found', null, 'error');
                return;
            }

            // Check if Sortable is available
            if (typeof Sortable === 'undefined') {
                Utils.log('Sortable.js not loaded, using static column order', null, 'warn');
                // Fallback: just update order from checkboxes
                this.updateColumnOrder();
                return;
            }

            // Destroy existing instance if any
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }

            // Initialize Sortable with improved configuration
            this.sortableInstance = new Sortable(container, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                handle: '.drag-handle',
                filter: '.column-checkbox, .form-check-label',
                preventOnFilter: false,
                onEnd: () => {
                    this.updateColumnOrder();
                    this.updatePreview();
                },
                onStart: (evt) => {
                    // Add visual feedback during drag
                    evt.item.classList.add('dragging');
                },
                onMove: (evt) => {
                    // Prevent dragging onto checkboxes
                    return !evt.related.classList.contains('column-checkbox') &&
                           !evt.related.classList.contains('form-check-label');
                }
            });

            Utils.log('Sortable initialized');
        },

        initializeColumnSorting: function() {
            // Get all draggable items
            const columnItems = document.querySelectorAll('.draggable-item');

            // Add click handlers for column headers to sort
            columnItems.forEach(item => {
                const checkbox = item.querySelector('.column-checkbox');
                const label = item.querySelector('.form-check-label');

                if (label) {
                    label.addEventListener('click', (e) => {
                        // Toggle checkbox when label is clicked
                        if (checkbox && e.target === label) {
                            checkbox.checked = !checkbox.checked;

                            // Trigger change event
                            const changeEvent = new Event('change', { bubbles: true });
                            checkbox.dispatchEvent(changeEvent);
                        }
                    });
                }
            });
        },

        updateColumnOrder: function() {
            const container = document.getElementById('columnsContainer');
            const hiddenOrderInput = document.getElementById('columnsOrderInput');

            if (!container || !hiddenOrderInput) return;

            // Get all draggable items in their current DOM order
            const columnItems = container.querySelectorAll('.draggable-item');
            const order = [];

            columnItems.forEach(item => {
                const checkbox = item.querySelector('.column-checkbox');
                if (checkbox && checkbox.checked) {
                    order.push(checkbox.value);
                }
            });

            this.columnOrder = order;
            hiddenOrderInput.value = order.join(',');

            Utils.log('Column order updated', order);
        },

        updatePreview: function() {
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
        },

        resetColumnOrder: function() {
            const container = document.getElementById('columnsContainer');
            if (!container) return;

            // Reset to default order (alphabetical by column key)
            const defaultColumns = ['admissionNo', 'lastname', 'firstname', 'class', 'gender'];

            // Reorder items based on default columns
            const items = Array.from(container.querySelectorAll('.draggable-item'));

            // Sort items based on default order
            items.sort((a, b) => {
                const aVal = a.dataset.column || '';
                const bVal = b.dataset.column || '';
                const aIndex = defaultColumns.indexOf(aVal);
                const bIndex = defaultColumns.indexOf(bVal);

                if (aIndex === -1 && bIndex === -1) return 0;
                if (aIndex === -1) return 1;
                if (bIndex === -1) return -1;
                return aIndex - bIndex;
            });

            // Reorder in DOM
            items.forEach(item => container.appendChild(item));

            // Update checkboxes
            defaultColumns.forEach(col => {
                const checkbox = container.querySelector(`.column-checkbox[value="${col}"]`);
                if (checkbox) checkbox.checked = true;
            });

            // Update preview and order
            this.updateColumnOrder();
            this.updatePreview();
        },

        generateReport: async function() {
            Utils.log('Generate report clicked');

            const form = document.getElementById('printReportForm');
            if (!form) {
                Utils.log('Report form not found', null, 'error');
                Utils.showError('Report form not found. Please try again.');
                return;
            }

            // Get selected columns
            const selectedCheckboxes = form.querySelectorAll('input[name="columns[]"]:checked');
            const selectedColumns = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedColumns.length === 0) {
                Utils.showError('Please select at least one column to include in the report.', 'Warning');
                return;
            }

            // Get column order from hidden input
            const columnsOrderInput = document.getElementById('columnsOrderInput');
            let columnOrder = columnsOrderInput ? columnsOrderInput.value : '';

            // If no specific order, use the order from DOM
            if (!columnOrder) {
                const container = document.getElementById('columnsContainer');
                if (container) {
                    const items = container.querySelectorAll('.draggable-item');
                    const order = [];
                    items.forEach(item => {
                        const checkbox = item.querySelector('.column-checkbox');
                        if (checkbox && checkbox.checked) {
                            order.push(checkbox.value);
                        }
                    });
                    columnOrder = order.join(',');
                }
            }

            // Get form values
            const classId = form.querySelector('[name="class_id"]')?.value || '';
            const status = form.querySelector('[name="status"]')?.value || '';
            const termId = form.querySelector('[name="term_id"]')?.value || '';
            const sessionId = form.querySelector('[name="session_id"]')?.value || '';

            const formatElements = form.querySelectorAll('[name="format"]');
            let format = 'pdf';
            formatElements.forEach(element => {
                if (element.checked) {
                    format = element.value;
                }
            });

            const includeHeader = form.querySelector('[name="include_header"]')?.checked || true;
            const includeLogo = form.querySelector('[name="include_logo"]')?.checked || true;
            const orientation = form.querySelector('[name="orientation"]')?.value || 'portrait';
            const template = form.querySelector('[name="template"]')?.value || 'default';
            const confidential = form.querySelector('[name="confidential"]')?.checked || false;
            const excludePhotos = form.querySelector('[name="exclude_photos"]')?.checked || false;

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
            const params = {
                class_id: classId,
                term_id: termId,
                session_id: sessionId,
                status: status,
                columns: selectedColumns.join(','),
                columns_order: columnOrder,
                format: format,
                orientation: orientation,
                include_header: includeHeader ? '1' : '0',
                include_logo: includeLogo ? '1' : '0',
                template: template,
                confidential: confidential ? '1' : '0',
                exclude_photos: excludePhotos ? '1' : '0',
                optimize_large_reports: '1'
            };

            try {
                const response = await ApiService.generateReport(params);

                Swal.close();

                // Create a blob from the response
                const blob = new Blob([response.data], {
                    type: response.headers['content-type']
                });

                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;

                // Get filename from content-disposition header
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
                Utils.showSuccess(`Report generated successfully and downloaded as ${format.toUpperCase()}`);

            } catch (error) {
                Swal.close();

                Utils.log('Error generating report', error, 'error');

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
                    errorMessage = 'Request timeout. The report generation is taking too long. Try with fewer students or different filters.';
                }

                Utils.showError(errorMessage);
            }
        }
    };

    // ============================================================================
    // STATE AND LGA MANAGER - Nigerian states and LGAs
    // ============================================================================
    const StateLGAManager = {
        initializeAddStateDropdown: function() {
            const stateSelect = document.getElementById('addState');
            const lgaSelect = document.getElementById('addLocal');

            if (!stateSelect || !lgaSelect) {
                Utils.log('State or LGA dropdown not found for add modal', null, 'error');
                return;
            }

            // Clear existing options
            stateSelect.innerHTML = '<option value="">Select State</option>';
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
            lgaSelect.disabled = true;

            // Populate states
            NIGERIAN_STATES.forEach(state => {
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });

            // Add change event listener
            stateSelect.removeEventListener('change', this.handleStateChange);
            stateSelect.addEventListener('change', (e) => this.handleStateChange(e, 'add'));
        },

        initializeEditStateDropdown: function() {
            const stateSelect = document.getElementById('editState');
            const lgaSelect = document.getElementById('editLocal');

            if (!stateSelect || !lgaSelect) {
                Utils.log('State or LGA dropdown not found for edit modal', null, 'error');
                return;
            }

            // Clear existing options
            stateSelect.innerHTML = '<option value="">Select State</option>';
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
            lgaSelect.disabled = true;

            // Populate states
            NIGERIAN_STATES.forEach(state => {
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });

            // Add change event listener
            stateSelect.removeEventListener('change', this.handleStateChange);
            stateSelect.addEventListener('change', (e) => this.handleStateChange(e, 'edit'));
        },

        handleStateChange: function(event, modalType = 'add') {
            const selectedState = event.target.value;
            const lgaSelect = modalType === 'add' ? document.getElementById('addLocal') : document.getElementById('editLocal');

            if (!lgaSelect) return;

            // Clear LGA dropdown
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';

            if (selectedState) {
                const state = NIGERIAN_STATES.find(s => s.name === selectedState);
                lgaSelect.disabled = false;

                if (state) {
                    // Populate LGAs for selected state
                    state.lgas.forEach(lga => {
                        const option = document.createElement('option');
                        option.value = lga;
                        option.textContent = lga;
                        lgaSelect.appendChild(option);
                    });
                }
            } else {
                lgaSelect.disabled = true;
            }
        },

        resetAddStateDropdown: function() {
            const stateSelect = document.getElementById('addState');
            const lgaSelect = document.getElementById('addLocal');

            if (stateSelect) {
                stateSelect.value = '';
            }
            if (lgaSelect) {
                lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                lgaSelect.disabled = true;
            }
        }
    };

    // ============================================================================
    // ADMISSION NUMBER MANAGER - Auto-generate admission numbers
    // ============================================================================
    const AdmissionNumberManager = {
        async updateAdmissionNumber(prefix = '') {
            const yearSelect = document.getElementById(`${prefix}admissionYear`);
            const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
            const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`);

            if (!yearSelect || !admissionNoInput) return;

            const year = yearSelect.value;
            const baseFormat = `TCC/${year}/`;

            if (admissionMode && admissionMode.value === 'auto') {
                admissionNoInput.readOnly = true;

                try {
                    const response = await axios.get(`/students/last-admission-number?year=${year}`);

                    if (response.data.success) {
                        admissionNoInput.value = response.data.admissionNo;
                    } else {
                        Utils.showError(response.data.message || 'Failed to generate admission number');
                        admissionNoInput.value = `${baseFormat}0871`;
                    }
                } catch (error) {
                    Utils.log('Error generating admission number', error, 'error');
                    Utils.showError('Failed to generate admission number');
                    admissionNoInput.value = `${baseFormat}0871`;
                }
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
        },

        toggleAdmissionInput: function(prefix = '') {
            const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`);
            const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
            const yearSelect = document.getElementById(`${prefix}admissionYear`);

            if (!admissionMode || !admissionNoInput || !yearSelect) return;

            const year = yearSelect.value;
            const baseFormat = `TCC/${year}/`;

            if (admissionMode.value === 'auto') {
                admissionNoInput.readOnly = true;
                this.updateAdmissionNumber(prefix);
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
    };

    // ============================================================================
    // CURRENT TERM MANAGER - Bulk update current term
    // ============================================================================
    const CurrentTermManager = {
        showUpdateCurrentTermModal: function(studentId = null) {
            let selectedIds = [];

            if (studentId) {
                selectedIds = [studentId];
            } else {
                selectedIds = SelectionManager.getSelectedStudentIds();
            }

            if (selectedIds.length === 0) {
                Utils.showError('Please select at least one student.', 'No Selection');
                return;
            }

            const form = document.getElementById('updateCurrentTermForm');
            if (form) {
                form.reset();
            }

            const selectedCountEl = document.getElementById('selectedStudentsCount');
            if (selectedCountEl) {
                selectedCountEl.textContent = selectedIds.length;
            }

            const modal = new bootstrap.Modal(document.getElementById('updateCurrentTermModal'));
            modal.show();
        },

        async updateCurrentTerm() {
            const selectedIds = SelectionManager.getSelectedStudentIds();
            const form = document.getElementById('updateCurrentTermForm');

            if (!form) return;

            const classId = form.querySelector('[name="schoolclassId"]')?.value;
            const termId = form.querySelector('[name="termId"]')?.value;
            const sessionId = form.querySelector('[name="sessionId"]')?.value;

            if (!classId || !termId || !sessionId) {
                Utils.showError('Please select class, term, and session.', 'Missing Fields');
                return;
            }

            try {
                Swal.fire({
                    title: 'Updating...',
                    text: 'Please wait while updating current term.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const data = {
                    student_ids: selectedIds,
                    schoolclassId: classId,
                    termId: termId,
                    sessionId: sessionId,
                    is_current: true
                };

                const response = await ApiService.updateBulkCurrentTerm(data);

                const modal = bootstrap.Modal.getInstance(document.getElementById('updateCurrentTermModal'));
                if (modal) modal.hide();

                Swal.close();

                Utils.showSuccess(response.data.message || `Current term updated for ${selectedIds.length} student(s).`);

                // Refresh the student list
                await StudentManager.fetchStudents();

            } catch (error) {
                Swal.close();

                Utils.log('Error updating current term', error, 'error');

                let errorMessage = 'Failed to update current term.';
                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }

                Utils.showError(errorMessage);
            }
        }
    };

    // ============================================================================
    // EVENT LISTENER MANAGER - Centralized event registration
    // ============================================================================
    const EventManager = {
        initializeAll: function() {
            this.initializeViewToggles();
            this.initializeFilterEvents();
            this.initializeModalEvents();
            this.initializeAdmissionEvents();
            this.initializeFormSubmissions();
            this.initializeCheckboxes();
            this.initializePerPageSelector();
        },

        initializeViewToggles: function() {
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');

            if (tableViewBtn) {
                tableViewBtn.removeEventListener('click', this.handleTableViewClick);
                tableViewBtn.addEventListener('click', () => RenderManager.toggleView('table'));
            }

            if (cardViewBtn) {
                cardViewBtn.removeEventListener('click', this.handleCardViewClick);
                cardViewBtn.addEventListener('click', () => RenderManager.toggleView('card'));
            }
        },

        initializeFilterEvents: function() {
            // Search with debounce
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.removeEventListener('input', FilterManager.debouncedSearch);
                searchInput.addEventListener('input', FilterManager.debouncedSearch);
            }

            // Filter changes
            const classFilter = document.getElementById('schoolclass-filter');
            const statusFilter = document.getElementById('status-filter');
            const genderFilter = document.getElementById('gender-filter');

            if (classFilter) {
                classFilter.removeEventListener('change', this.handleFilterChange);
                classFilter.addEventListener('change', () => {
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }

            if (statusFilter) {
                statusFilter.removeEventListener('change', this.handleFilterChange);
                statusFilter.addEventListener('change', () => {
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }

            if (genderFilter) {
                genderFilter.removeEventListener('change', this.handleFilterChange);
                genderFilter.addEventListener('change', () => {
                    AppState.pagination.currentPage = 1;
                    StudentManager.fetchStudents();
                });
            }

            // Filter button
            const filterBtn = document.querySelector('[onclick="filterData()"]');
            if (filterBtn) {
                filterBtn.removeEventListener('click', this.handleFilterClick);
                filterBtn.addEventListener('click', () => FilterManager.applyFilters());
            }

            // Reset button
            const resetBtn = document.querySelector('[onclick="resetFilters()"]');
            if (resetBtn) {
                resetBtn.removeEventListener('click', this.handleResetClick);
                resetBtn.addEventListener('click', () => FilterManager.resetFilters());
            }
        },

        initializeModalEvents: function() {
            // Add Student Modal
            const addModal = document.getElementById('addStudentModal');
            if (addModal) {
                addModal.removeEventListener('hidden.bs.modal', StateLGAManager.resetAddStateDropdown);
                addModal.addEventListener('hidden.bs.modal', () => StateLGAManager.resetAddStateDropdown());

                addModal.removeEventListener('shown.bs.modal', this.handleAddModalShown);
                addModal.addEventListener('shown.bs.modal', () => {
                    StateLGAManager.initializeAddStateDropdown();
                    AdmissionNumberManager.updateAdmissionNumber('');
                });
            }

            // Edit Student Modal
            const editModal = document.getElementById('editStudentModal');
            if (editModal) {
                editModal.removeEventListener('hidden.bs.modal', EditFormManager.resetEditStateDropdown);
                editModal.addEventListener('hidden.bs.modal', () => EditFormManager.resetEditStateDropdown());

                editModal.removeEventListener('shown.bs.modal', this.handleEditModalShown);
                editModal.addEventListener('shown.bs.modal', () => {
                    StateLGAManager.initializeEditStateDropdown();
                });
            }

            // Report Modal
            const reportModal = document.getElementById('printStudentReportModal');
            if (reportModal) {
                reportModal.removeEventListener('show.bs.modal', this.handleReportModalShow);
                reportModal.addEventListener('show.bs.modal', () => {
                    setTimeout(() => ReportManager.initializeReportModal(), 100);
                });

                reportModal.removeEventListener('hidden.bs.modal', this.handleReportModalHide);
                reportModal.addEventListener('hidden.bs.modal', () => {
                    // Cleanup Sortable instance
                    if (ReportManager.sortableInstance) {
                        ReportManager.sortableInstance.destroy();
                        ReportManager.sortableInstance = null;
                    }
                });
            }

            // Update Current Term Modal
            const confirmUpdateBtn = document.getElementById('confirmUpdateCurrentTerm');
            if (confirmUpdateBtn) {
                confirmUpdateBtn.removeEventListener('click', CurrentTermManager.updateCurrentTerm);
                confirmUpdateBtn.addEventListener('click', () => CurrentTermManager.updateCurrentTerm());
            }
        },

        initializeAdmissionEvents: function() {
            // Admission year selects
            const admissionYear = document.getElementById('admissionYear');
            const editAdmissionYear = document.getElementById('editAdmissionYear');

            if (admissionYear) {
                admissionYear.removeEventListener('change', this.handleAdmissionYearChange);
                admissionYear.addEventListener('change', () => AdmissionNumberManager.updateAdmissionNumber(''));
            }

            if (editAdmissionYear) {
                editAdmissionYear.removeEventListener('change', this.handleEditAdmissionYearChange);
                editAdmissionYear.addEventListener('change', () => AdmissionNumberManager.updateAdmissionNumber('edit'));
            }

            // Admission mode radios
            const admissionModes = document.querySelectorAll('input[name="admissionMode"]');
            admissionModes.forEach(radio => {
                radio.removeEventListener('change', this.handleAdmissionModeChange);
                radio.addEventListener('change', function() {
                    const prefix = this.id.includes('edit') ? 'edit' : '';
                    AdmissionNumberManager.toggleAdmissionInput(prefix);
                });
            });
        },

        initializeFormSubmissions: function() {
            // Add Student Form
            const addForm = document.getElementById('addStudentForm');
            if (addForm) {
                addForm.removeEventListener('submit', this.handleAddFormSubmit);
                addForm.addEventListener('submit', async (e) => {
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

                            await StudentManager.fetchStudents();

                            Utils.showSuccess(response.data.message || 'Student registered successfully.');
                        }
                    } catch (error) {
                        let errorMessage = 'Failed to save student.';

                        if (error.response?.data?.message) {
                            errorMessage = error.response.data.message;
                        }

                        if (error.response?.data?.errors) {
                            const errors = error.response.data.errors;
                            let errorList = '';
                            for (const field in errors) {
                                errorList += `<li>${Utils.escapeHtml(errors[field].join(', '))}</li>`;
                            }
                            errorMessage = `<div class="text-start">
                                <strong>Validation Errors:</strong>
                                <ul class="mb-0">${errorList}</ul>
                            </div>`;
                        }

                        Utils.showError(errorMessage);
                    }
                });
            }

            // Edit Student Form
            const editForm = document.getElementById('editStudentForm');
            if (editForm) {
                editForm.removeEventListener('submit', this.handleEditFormSubmit);
                editForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const form = e.target;
                    const formData = new FormData(form);
                    formData.append('_method', 'PATCH');

                    try {
                        const response = await axios.post(form.action, formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        });

                        if (response.data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
                            modal.hide();

                            await StudentManager.fetchStudents();

                            Utils.showSuccess(response.data.message || 'Student updated successfully.');
                        }
                    } catch (error) {
                        let errorMessage = 'Failed to update student.';

                        if (error.response?.data?.message) {
                            errorMessage = error.response.data.message;
                        }

                        if (error.response?.data?.errors) {
                            const errors = error.response.data.errors;
                            let errorList = '';
                            for (const field in errors) {
                                errorList += `<li>${Utils.escapeHtml(errors[field].join(', '))}</li>`;
                            }
                            errorMessage = `<div class="text-start">
                                <strong>Validation Errors:</strong>
                                <ul class="mb-0">${errorList}</ul>
                            </div>`;
                        }

                        Utils.showError(errorMessage);
                    }
                });
            }
        },

        initializeCheckboxes: function() {
            SelectionManager.initializeCheckboxes();
        },

        initializePerPageSelector: function() {
            PaginationManager.initializePerPageSelector();
        },

        handleFilterChange: function() {
            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        },

        handleFilterClick: function() {
            FilterManager.applyFilters();
        },

        handleResetClick: function() {
            FilterManager.resetFilters();
        },

        handleAddModalShown: function() {
            StateLGAManager.initializeAddStateDropdown();
            AdmissionNumberManager.updateAdmissionNumber('');
        },

        handleEditModalShown: function() {
            StateLGAManager.initializeEditStateDropdown();
        },

        handleReportModalShow: function() {
            setTimeout(() => ReportManager.initializeReportModal(), 100);
        },

        handleReportModalHide: function() {
            if (ReportManager.sortableInstance) {
                ReportManager.sortableInstance.destroy();
                ReportManager.sortableInstance = null;
            }
        },

        handleAdmissionYearChange: function() {
            AdmissionNumberManager.updateAdmissionNumber('');
        },

        handleEditAdmissionYearChange: function() {
            AdmissionNumberManager.updateAdmissionNumber('edit');
        },

        handleAdmissionModeChange: function(e) {
            const prefix = e.target.id.includes('edit') ? 'edit' : '';
            AdmissionNumberManager.toggleAdmissionInput(prefix);
        },

        handleAddFormSubmit: async function(e) {
            e.preventDefault();
            // Handled in initializeFormSubmissions
        },

        handleEditFormSubmit: async function(e) {
            e.preventDefault();
            // Handled in initializeFormSubmissions
        }
    };

    // ============================================================================
    // INITIALIZATION - Start the application
    // ============================================================================
    function initializeApplication() {
        Utils.log('Initializing Student Management System...');

        // Ensure Axios is available
        if (!Utils.ensureAxios()) {
            Utils.showError('Failed to initialize application. Please refresh the page.');
            return;
        }

        // Initialize all event listeners
        EventManager.initializeAll();

        // Initialize state dropdowns
        StateLGAManager.initializeAddStateDropdown();
        StateLGAManager.initializeEditStateDropdown();

        // Initialize admission numbers
        AdmissionNumberManager.updateAdmissionNumber('');
        AdmissionNumberManager.updateAdmissionNumber('edit');

        // Load initial data
        StudentManager.fetchStudents();

        Utils.log('Student Management System initialized successfully');
    }

    // ============================================================================
    // EXPORT GLOBAL FUNCTIONS - Make functions available globally
    // ============================================================================
    window.fetchStudents = () => StudentManager.fetchStudents();
    window.filterData = () => FilterManager.applyFilters();
    window.resetFilters = () => FilterManager.resetFilters();
    window.viewStudent = (id) => StudentManager.viewStudent(id);
    window.editStudent = (id) => StudentManager.editStudent(id);
    window.deleteStudent = (id) => StudentManager.deleteStudent(id);
    window.deleteMultiple = () => StudentManager.deleteMultiple();
    window.toggleView = (view) => RenderManager.toggleView(view);
    window.updateAdmissionNumber = (prefix) => AdmissionNumberManager.updateAdmissionNumber(prefix);
    window.toggleAdmissionInput = (prefix) => AdmissionNumberManager.toggleAdmissionInput(prefix);
    window.previewImage = function(input, targetId = 'addStudentAvatar') {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const target = document.getElementById(targetId);
            if (target) {
                target.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    };
    window.calculateAge = Utils.calculateAge;
    window.generateReport = () => ReportManager.generateReport();
    window.showUpdateCurrentTermModal = (id) => CurrentTermManager.showUpdateCurrentTermModal(id);
    window.updateCurrentTerm = () => CurrentTermManager.updateCurrentTerm();
    window.getSelectedStudentIds = () => SelectionManager.getSelectedStudentIds();
    window.updateBulkActionsVisibility = () => SelectionManager.updateBulkActionsVisibility();
    window.printStudentCard = function(studentId) {
        window.open(`/student/${studentId}/id-card`, '_blank');
    };
    window.printStudentDetails = function(studentId) {
        window.open(`/student/${studentId}/print`, '_blank');
    };
    window.sendMessage = function(studentId) {
        Swal.fire({
            title: 'Send Message',
            html: `
                <div class="text-start">
                    <label class="form-label">Message</label>
                    <textarea id="messageText" class="form-control" rows="4" placeholder="Type your message here..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Send',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4361ee',
            preConfirm: () => {
                const message = document.getElementById('messageText')?.value;
                if (!message) {
                    Swal.showValidationMessage('Message cannot be empty');
                    return false;
                }
                return axios.post('/student/send-message', {
                    student_id: studentId,
                    message: message
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Utils.showSuccess('Message sent successfully');
            }
        });
    };

    // ============================================================================
    // DOM CONTENT LOADED - Start the application
    // ============================================================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApplication);
    } else {
        // DOM already loaded, initialize immediately
        initializeApplication();
    }

})();
</script>
{{-- <!-- Include Sortable.js for drag and drop functionality -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Include Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> --}}

@endsection
