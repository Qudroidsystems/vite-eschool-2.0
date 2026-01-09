@extends('layouts.master')

@section('content')
<?php
use Spatie\Permission\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Role Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Role Management</a></li>
                                <li class="breadcrumb-item active">Role Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Error and success messages -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

            <!-- Back button -->
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center g-2">
                        <div class="col-lg-3 me-auto"></div>
                        <div class="col-lg-auto">
                            <div class="hstack gap-2">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary"><< Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h5 class="text-decoration-underline mb-3 pb-1">View Role Details</h5>
                </div>
            </div>

            <div class="row">
                <!-- Role permissions -->
                <div class="col-xl-3 col-lg-6">
                    <div class="card" id="networks">
                        <div class="card-header d-flex">
                            <h5 class="card-title mb-0 flex-grow-1 {{ $role->badge }}">{{ $role->name }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <tbody class="list">
                                        <tr>
                                            <td colspan="2">
                                                <button type="button" class="btn btn-light btn-active-primary" data-bs-toggle="modal" data-bs-target="#editRoleModalgrid">Edit Role</button>
                                            </td>
                                        </tr>
                                        @foreach ($rolePermissions as $rm)
                                            <tr>
                                                <td>---</td>
                                                <td class="click text-center">{{ $rm->name }}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users assigned to role -->
                <div class="col-xxl-8 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">Users Assigned: ({{ $userRoleCount }})</h4>
                            <div class="flex-shrink-0">
                                <div class="nav nav-pills gap-1" id="popularProperty" role="tablist" aria-orientation="vertical">
                                    @can('Update user-role')
                                        <button type="button" class="btn btn-light btn-sm btn-active-success my-1" data-bs-toggle="modal" data-bs-target="#addUserModalgrid">Add User</button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="popularPropertyContent">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered align-middle table-nowrap mb-0" id="userList">
                                            <thead class="table-active">
                                                <tr>
                                                    <th>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="option" id="checkAll">
                                                            <label class="form-check-label" for="checkAll"></label>
                                                        </div>
                                                    </th>
                                                    <th class="sort cursor-pointer" data-sort="name">User</th>
                                                    <th class="sort cursor-pointer" data-sort="datereg">Joined Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list form-check-all">
                                                @forelse ($usersWithRole as $user)
                                                    <tr data-id="{{ $user->id }}">
                                                        <td class="id" data-id="{{ $user->id }}">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="chk_child">
                                                                <label class="form-check-label"></label>
                                                            </div>
                                                        </td>
                                                        <td class="name">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-xs me-2">
                                                                    <img src="{{ $user->avatar_url }}"
                                                                         alt="{{ $user->name }}"
                                                                         class="rounded-circle avatar-xs"
                                                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF'">
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0">
                                                                        <a href="{{ route('users.show', $user->id) }}" class="text-reset products">{{ $user->name }}</a>
                                                                    </h6>
                                                                    <small class="text-muted">
                                                                        @if($user->isStudent() && $user->student)
                                                                            {{ $user->student->admissionNo ?? '' }}
                                                                        @elseif($user->isStaff() && $user->staffemploymentDetails)
                                                                            {{ $user->staffemploymentDetails->designation ?? 'Staff' }}
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="datereg">{{ $user->created_at->format('Y-m-d') }}</td>
                                                        <td>
                                                            <ul class="d-flex gap-2 list-unstyled mb-0">
                                                                @can('Remove user-role')
                                                                    <li>
                                                                        <a class="dropdown-item remove-item-btn" href="javascript:void(0);"
                                                                           data-bs-toggle="modal"
                                                                           data-bs-target="#deleteRecordModal"
                                                                           data-url="{{ route('roles.removeuserrole', ['userid' => $user->id, 'roleid' => $role->id]) }}">
                                                                            <i class="bi bi-trash3 me-1 align-baseline"></i> Remove User
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="noresult" style="display: block;">No results found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-3 align-items-center" id="pagination-element">
                                        <div class="col-sm">
                                            <div class="text-muted text-center text-sm-start">
                                                Showing <span class="fw-semibold">{{ $usersWithRole->count() }}</span> of <span class="fw-semibold">{{ $usersWithRole->total() }}</span> Results
                                            </div>
                                        </div>
                                        <div class="col-sm-auto mt-3 mt-sm-0">
                                            <div class="pagination-wrap hstack gap-2 justify-content-center">
                                                <a class="page-item pagination-prev {{ $usersWithRole->onFirstPage() ? 'disabled' : '' }}" href="javascript:void(0);" data-url="{{ $usersWithRole->previousPageUrl() }}">
                                                    <i class="mdi mdi-chevron-left align-middle"></i>
                                                </a>
                                                <ul class="pagination listjs-pagination mb-0">
                                                    @foreach ($usersWithRole->links()->elements[0] as $page => $url)
                                                        <li class="page-item {{ $usersWithRole->currentPage() == $page ? 'active' : '' }}">
                                                            <a class="page-link" href="javascript:void(0);" data-url="{{ $url }}">{{ $page }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <a class="page-item pagination-next {{ $usersWithRole->hasMorePages() ? '' : 'disabled' }}" href="javascript:void(0);" data-url="{{ $usersWithRole->nextPageUrl() }}">
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

            <!-- Delete confirmation modal -->
            <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-md-5">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="bi bi-trash display-4"></i>
                                </div>
                                <div class="mt-4">
                                    <h3 class="mb-2">Are you sure?</h3>
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this user from the role?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                <button type="button" class="btn w-sm btn-light btn-hover" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn w-sm btn-danger btn-hover" id="delete-record">Yes, Remove It!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add user modal with tabs -->
            <div class="modal fade" id="addUserModalgrid" tabindex="-1" aria-labelledby="addUserModalLabel" aria-modal="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel">Add Users to {{ $role->name }} Role</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addUserRoleForm" class="form" action="{{ route('roles.updateuserrole') }}" method="POST">
                                @csrf
                                <input type="hidden" name="roleid" value="{{ $role->id }}" />

                                <!-- Tab navigation -->
                                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#staff-tab" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-user-tie"></i></span>
                                            <span class="d-none d-sm-block">
                                                <i class="fas fa-user-tie me-1"></i> Staff
                                                <span class="badge bg-primary rounded-pill ms-1" id="staff-count">0</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#student-tab" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-user-graduate"></i></span>
                                            <span class="d-none d-sm-block">
                                                <i class="fas fa-user-graduate me-1"></i> Students
                                                <span class="badge bg-primary rounded-pill ms-1" id="student-count">0</span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab content -->
                                <div class="tab-content">
                                    <!-- Staff Tab -->
                                    <!-- Staff Tab -->
<div class="tab-pane fade show active" id="staff-tab" role="tabpanel">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Staff Members</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all-staff">
                    <label class="form-check-label" for="select-all-staff">
                        Select All Staff
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="staff-list-container">
        <!-- Staff list will be populated here -->
        @php
            // Get all users not already in this role
            $allUsersNotInRole = \App\Models\User::whereDoesntHave('roles', function ($q) use ($role) {
                $q->where('name', $role->name);
            })->get();

            // Identify staff users by:
            // 1. Users who have staff employment details
            // 2. Users with staff pictures
            // 3. Users with qualifications (typically staff)
            // 4. Users who are not students (no student_id)
            $staffUsers = $allUsersNotInRole->filter(function ($user) {
                // Check for staff employment details
                if ($user->staffemploymentDetails) {
                    return true;
                }

                // Check for staff picture
                if ($user->staffPicture) {
                    return true;
                }

                // Check for qualifications (staff typically have these)
                if ($user->qualifications()->exists()) {
                    return true;
                }

                // Check if user has a student_id - if not, likely staff
                if (!$user->student_id) {
                    return true;
                }

                return false;
            })->sortBy('name');

            // Also exclude users who are definitely students
            $staffUsers = $staffUsers->reject(function ($user) {
                // Explicitly exclude users with student records
                if ($user->student) {
                    return true;
                }

                // Exclude users who have student in their name (like student accounts)
                if (stripos($user->name, 'student') !== false) {
                    return true;
                }

                return false;
            });
        @endphp

        @forelse($staffUsers as $staff)
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card user-card mb-3" data-user-id="{{ $staff->id }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md me-3">
                            <img src="{{ $staff->avatar_url }}"
                                 alt="{{ $staff->name }}"
                                 class="rounded-circle avatar-md"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&color=7F9CF5&background=EBF4FF'">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $staff->name }}</h6>
                            <p class="text-muted mb-0">{{ $staff->email }}</p>
                            <p class="text-muted mb-0 small">
                                @if($staff->staffemploymentDetails)
                                    {{ $staff->staffemploymentDetails->designation ?? 'Staff Member' }}
                                @elseif($staff->qualifications()->exists())
                                    Staff ({{ $staff->qualifications->count() }} qualification(s))
                                @else
                                    Staff Member
                                @endif
                            </p>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input staff-checkbox user-checkbox"
                                   type="checkbox"
                                   value="{{ $staff->id }}"
                                   name="users[]"
                                   id="staff-{{ $staff->id }}"
                                   data-type="staff">
                            <label class="form-check-label" for="staff-{{ $staff->id }}"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                @if($allUsersNotInRole->isEmpty())
                    All users are already assigned to this role.
                @else
                    No staff members found.
                    <br><small>Staff are identified by: having staff employment details, staff pictures, qualifications, or not having a student ID.</small>
                @endif
            </div>
        </div>
        @endforelse
    </div>
</div>

                                    <!-- Student Tab -->
                                   <!-- Student Tab -->
<div class="tab-pane fade" id="student-tab" role="tabpanel">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Students</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all-students">
                    <label class="form-check-label" for="select-all-students">
                        Select All Students
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="student-list-container">
        <!-- Student list will be populated here -->
        @php
            // Identify student users
            $studentUsers = $allUsersNotInRole->filter(function ($user) {
                // Check if user has student_id
                if ($user->student_id) {
                    return true;
                }

                // Check if user has student record
                if ($user->student) {
                    return true;
                }

                // Check if user name contains "student"
                if (stripos($user->name, 'student') !== false) {
                    return true;
                }

                // Exclude users we identified as staff
                if ($user->staffemploymentDetails || $user->staffPicture || $user->qualifications()->exists()) {
                    return false;
                }

                // Default: consider as student if not clearly staff
                return false;
            })->sortBy('name');
        @endphp

        @forelse($studentUsers as $studentUser)
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card user-card mb-3" data-user-id="{{ $studentUser->id }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md me-3">
                            <img src="{{ $studentUser->avatar_url }}"
                                 alt="{{ $studentUser->name }}"
                                 class="rounded-circle avatar-md"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($studentUser->name) }}&color=7F9CF5&background=EBF4FF'">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $studentUser->name }}</h6>
                            <p class="text-muted mb-0">
                                @if($studentUser->student && $studentUser->student->admissionNo)
                                    {{ $studentUser->student->admissionNo }}
                                @elseif($studentUser->student_id)
                                    Student ID: {{ $studentUser->student_id }}
                                @else
                                    Student
                                @endif
                            </p>
                            <p class="text-muted mb-0 small">
                                @if($studentUser->student && $studentUser->student->currentClass)
                                    Class: {{ $studentUser->student->currentClass->schoolclass->schoolclass ?? 'Not Assigned' }}
                                @endif
                            </p>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input student-checkbox user-checkbox"
                                   type="checkbox"
                                   value="{{ $studentUser->id }}"
                                   name="users[]"
                                   id="student-{{ $studentUser->id }}"
                                   data-type="student">
                            <label class="form-check-label" for="student-{{ $studentUser->id }}"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                @if($allUsersNotInRole->isEmpty())
                    All users are already assigned to this role.
                @else
                    No students found.
                    <br><small>Students are identified by: having student_id, student records, or not being identified as staff.</small>
                @endif
            </div>
        </div>
        @endforelse
    </div>
</div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-info d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <div>
                                                Selected: <span id="selected-count">0</span> user(s)
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" id="submit-btn">Add Selected Users</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit role modal -->
            <div class="modal fade" id="editRoleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalgridLabel">Edit Role</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('roles.update', $role->id) }}" method="POST" class="form" id="kt_modal_update_role_form">
                                @csrf
                                @method('PATCH')
                                <div class="row g-3">
                                    <div class="col-xxl-6">
                                        <label for="name" class="form-label">Role Name</label>
                                        <input type="text" class="form-control" placeholder="Enter a role name" name="name" value="{{ old('name', $role->name) }}" required>
                                    </div>
                                    <div class="col-xxl-6">
                                        <label for="badge" class="form-label">Role Badge</label>
                                        <select name="badge" class="form-control" data-kt-select2="true" data-placeholder="Select option" data-allow-clear="true">
                                            <option></option>
                                            <option value="badge bg-light" {{ $role->badge == 'badge bg-light' ? 'selected' : '' }}>Light grey</option>
                                            <option value="badge bg-dark" {{ $role->badge == 'badge bg-dark' ? 'selected' : '' }}>Dark</option>
                                            <option value="badge bg-primary" {{ $role->badge == 'badge bg-primary' ? 'selected' : '' }}>Blue</option>
                                            <option value="badge bg-secondary" {{ $role->badge == 'badge bg-secondary' ? 'selected' : '' }}>Light blue</option>
                                            <option value="badge bg-success" {{ $role->badge == 'badge bg-success' ? 'selected' : '' }}>Light green</option>
                                            <option value="badge bg-info" {{ $role->badge == 'badge bg-info' ? 'selected' : '' }}>Purple</option>
                                            <option value="badge bg-warning" {{ $role->badge == 'badge bg-warning' ? 'selected' : '' }}>Yellow</option>
                                            <option value="badge bg-danger" {{ $role->badge == 'badge bg-danger' ? 'selected' : '' }}>Red</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                <tbody class="text-gray-600 fw-semibold">
                                                    <tr>
                                                        <td class="text-gray-800">
                                                            Administrator Access
                                                            <span class="ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="Allows a full access to the system">
                                                                <i class="ki-duotone ki-information fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <label class="form-check form-check-custom form-check-solid me-9">
                                                                <input class="form-check-input" type="checkbox" value="" id="kt_roles_select_all" />
                                                                <span class="form-check-label" for="kt_roles_select_all">Select all</span>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    @foreach (array_unique($perm_title) as $value)
                                                        @php
                                                            $permission = \Spatie\Permission\Models\Permission::where('title', $value)->get();
                                                        @endphp
                                                        <tr>
                                                            <td class="text-gray-800">{{ $value }}</td>
                                                            @foreach ($permission as $v)
                                                                @php
                                                                    $word = '';
                                                                    if (str_contains($v->name, 'View ')) {
                                                                        $word = 'View';
                                                                    } elseif (str_contains($v->name, 'Create ')) {
                                                                        $word = 'Create';
                                                                    } elseif (str_contains($v->name, 'Update ')) {
                                                                        $word = 'Edit';
                                                                    } elseif (str_contains($v->name, 'Delete ')) {
                                                                        $word = 'Delete';
                                                                    } elseif (str_contains($v->name, 'Update user-role')) {
                                                                        $word = 'Update user role';
                                                                    } elseif (str_contains($v->name, 'Add user-role')) {
                                                                        $word = 'Add user role';
                                                                    } elseif (str_contains($v->name, 'Remove user-role')) {
                                                                        $word = 'Remove user role';
                                                                    }
                                                                @endphp
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <div class="form-check form-check-outline form-check-primary mb-3">
                                                                            <input class="form-check-input" type="checkbox" value="{{ $v->id }}" name="permission[]"
                                                                                {{ $role->hasPermissionTo($v->name) ? 'checked' : '' }}>
                                                                            <label class="form-check-label">{{ $word }}</label>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSS for styling -->
            <style>
                /* User card styling */
                .user-card {
                    border: 1px solid #e9ecef;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    border-radius: 8px;
                }

                .user-card:hover {
                    border-color: #405189;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    transform: translateY(-2px);
                }

                .user-card.selected {
                    background-color: rgba(64, 81, 137, 0.05);
                    border-color: #405189;
                }

                .user-card .card-body {
                    padding: 1rem;
                }

                .user-card .avatar-md {
                    width: 48px;
                    height: 48px;
                    min-width: 48px;
                }

                .user-card .avatar-md img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border: 2px solid #fff;
                    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
                }

                .user-card h6 {
                    font-size: 0.875rem;
                    font-weight: 600;
                    margin-bottom: 0.25rem;
                    color: #495057;
                }

                .user-card p.text-muted {
                    font-size: 0.75rem;
                    margin-bottom: 0.125rem;
                    line-height: 1.3;
                }

                .user-card .form-check {
                    margin-left: auto;
                    padding-left: 0;
                }

                .user-card .form-check-input {
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                    border: 2px solid #adb5bd;
                }

                .user-card .form-check-input:checked {
                    background-color: #405189;
                    border-color: #405189;
                }

                .user-card .form-check-input:focus {
                    box-shadow: 0 0 0 0.2rem rgba(64, 81, 137, 0.25);
                }

                /* Tab styling */
                .nav-tabs-custom {
                    border-bottom: 1px solid #e9ecef;
                }

                .nav-tabs-custom .nav-link {
                    color: #6c757d;
                    font-weight: 500;
                    padding: 0.75rem 1.5rem;
                    border: none;
                    position: relative;
                    background: none;
                    transition: all 0.3s ease;
                }

                .nav-tabs-custom .nav-link:hover {
                    color: #405189;
                    background-color: rgba(64, 81, 137, 0.05);
                }

                .nav-tabs-custom .nav-link.active {
                    color: #405189;
                    background-color: transparent;
                    border: none;
                }

                .nav-tabs-custom .nav-link.active::after {
                    content: '';
                    position: absolute;
                    bottom: -1px;
                    left: 0;
                    right: 0;
                    height: 2px;
                    background-color: #405189;
                }

                .nav-tabs-custom .nav-link .badge {
                    font-size: 0.65rem;
                    padding: 0.2rem 0.5rem;
                }

                /* Selected count badge */
                #selected-count {
                    font-weight: 600;
                    color: #405189;
                }

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .user-card .d-flex {
                        flex-direction: column;
                        text-align: center;
                    }

                    .user-card .avatar-md {
                        margin: 0 auto 1rem auto;
                    }

                    .user-card .form-check {
                        margin: 1rem auto 0 auto;
                    }

                    .nav-tabs-custom .nav-link {
                        padding: 0.5rem 1rem;
                        font-size: 0.875rem;
                    }
                }

                /* Avatar sizes */
                .avatar-xs {
                    width: 32px;
                    height: 32px;
                    min-width: 32px;
                }

                .avatar-xs img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                /* Checkbox label cursor */
                .form-check-label {
                    cursor: pointer;
                }

                /* Tab content animation */
                .tab-content {
                    padding-top: 1rem;
                }

                /* User list in table */
                #userList .avatar-xs img {
                    border: 2px solid #fff;
                    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
                }
            </style>
        </div><!-- End Page-content -->
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Select All for Permissions in Edit Role Modal
    const selectAllCheckbox = document.getElementById("kt_roles_select_all");
    const permissionCheckboxes = document.querySelectorAll('input[name="permission[]"]');

    if (selectAllCheckbox && permissionCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener("change", function () {
            permissionCheckboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });

        function updateSelectAllState() {
            const allChecked = Array.from(permissionCheckboxes).every(checkbox => checkbox.checked);
            const someChecked = Array.from(permissionCheckboxes).some(checkbox => checkbox.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }

        permissionCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", updateSelectAllState);
        });

        updateSelectAllState();
    }

    // Initialize Add User Modal functionality
    const addUserModal = document.getElementById('addUserModalgrid');
    let addUserModalInitialized = false;

    function initializeAddUserModal() {
        if (addUserModalInitialized) return;
        addUserModalInitialized = true;

        console.log('Initializing Add User Modal...');

        // Get DOM elements
        const selectAllStaffCheckbox = document.getElementById("select-all-staff");
        const selectAllStudentsCheckbox = document.getElementById("select-all-students");
        const staffCheckboxes = document.querySelectorAll('.staff-checkbox');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        const allUserCheckboxes = document.querySelectorAll('.user-checkbox');
        const staffCountElement = document.getElementById('staff-count');
        const studentCountElement = document.getElementById('student-count');
        const selectedCountElement = document.getElementById('selected-count');
        const userCards = document.querySelectorAll('.user-card');
        const submitBtn = document.getElementById('submit-btn');
        const addUserForm = document.getElementById("addUserRoleForm");

        // Initialize counts
        updateCounts();

        // Select All Staff functionality
        if (selectAllStaffCheckbox) {
            selectAllStaffCheckbox.addEventListener("change", function () {
                const isChecked = this.checked;
                console.log('Select All Staff toggled:', isChecked);

                staffCheckboxes.forEach((checkbox) => {
                    checkbox.checked = isChecked;
                    const card = checkbox.closest('.user-card');
                    if (card) {
                        card.classList.toggle('selected', isChecked);
                    }
                });

                updateCounts();
            });
        }

        // Select All Students functionality
        if (selectAllStudentsCheckbox) {
            selectAllStudentsCheckbox.addEventListener("change", function () {
                const isChecked = this.checked;
                console.log('Select All Students toggled:', isChecked);

                studentCheckboxes.forEach((checkbox) => {
                    checkbox.checked = isChecked;
                    const card = checkbox.closest('.user-card');
                    if (card) {
                        card.classList.toggle('selected', isChecked);
                    }
                });

                updateCounts();
            });
        }

        // Individual checkbox change handlers
        allUserCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                const card = this.closest('.user-card');
                if (card) {
                    card.classList.toggle('selected', this.checked);
                }
                updateCounts();
                updateSelectAllStates();
            });
        });

        // Card click handler (toggle checkbox)
        userCards.forEach((card) => {
            card.addEventListener('click', function (e) {
                // Don't toggle if clicking on checkbox or label
                if (e.target.type === 'checkbox' || e.target.tagName === 'LABEL') {
                    return;
                }

                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        // Form submission validation
        if (addUserForm) {
            addUserForm.addEventListener("submit", function (e) {
                const selectedCount = getSelectedCount();
                if (selectedCount === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Users Selected',
                        text: 'Please select at least one user to add to this role.',
                        confirmButtonColor: '#405189'
                    });
                } else {
                    // Show loading on submit button
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Adding...';
                    submitBtn.disabled = true;
                }
            });
        }

        // Tab change handler
        const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function (e) {
                updateSelectAllStates();
            });
        });

        // Update counts function
        function updateCounts() {
            const staffCount = staffCheckboxes.length;
            const studentCount = studentCheckboxes.length;
            const selectedCount = getSelectedCount();

            if (staffCountElement) staffCountElement.textContent = staffCount;
            if (studentCountElement) studentCountElement.textContent = studentCount;
            if (selectedCountElement) selectedCountElement.textContent = selectedCount;

            console.log('Counts updated:', { staffCount, studentCount, selectedCount });
        }

        // Get selected count
        function getSelectedCount() {
            let count = 0;
            allUserCheckboxes.forEach(checkbox => {
                if (checkbox.checked) count++;
            });
            return count;
        }

        // Update select all states
        function updateSelectAllStates() {
            // Update staff select all
            if (selectAllStaffCheckbox && staffCheckboxes.length > 0) {
                const allStaffChecked = Array.from(staffCheckboxes).every(checkbox => checkbox.checked);
                const someStaffChecked = Array.from(staffCheckboxes).some(checkbox => checkbox.checked);
                selectAllStaffCheckbox.checked = allStaffChecked;
                selectAllStaffCheckbox.indeterminate = someStaffChecked && !allStaffChecked;
            }

            // Update student select all
            if (selectAllStudentsCheckbox && studentCheckboxes.length > 0) {
                const allStudentsChecked = Array.from(studentCheckboxes).every(checkbox => checkbox.checked);
                const someStudentsChecked = Array.from(studentCheckboxes).some(checkbox => checkbox.checked);
                selectAllStudentsCheckbox.checked = allStudentsChecked;
                selectAllStudentsCheckbox.indeterminate = someStudentsChecked && !allStudentsChecked;
            }
        }

        // Initial update
        updateSelectAllStates();

        // Reset modal when closed
        addUserModal.addEventListener('hidden.bs.modal', function () {
            // Reset form but keep initialization flag
            addUserModalInitialized = false;

            // Reset submit button
            if (submitBtn) {
                submitBtn.innerHTML = 'Add Selected Users';
                submitBtn.disabled = false;
            }
        });
    }

    if (addUserModal) {
        addUserModal.addEventListener('shown.bs.modal', function () {
            setTimeout(initializeAddUserModal, 100);
        });
    }

    // Delete Record Modal Handling
    const deleteRecordModal = document.getElementById('deleteRecordModal');
    const deleteRecordButton = document.getElementById('delete-record');
    if (deleteRecordModal && deleteRecordButton) {
        deleteRecordModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const url = button.getAttribute('data-url');
            const userName = button.closest('tr').querySelector('.name a').textContent;

            // Update modal message with user name
            const modalMessage = deleteRecordModal.querySelector('.text-muted.fs-lg');
            if (modalMessage && userName) {
                modalMessage.textContent = `Are you sure you want to remove ${userName} from this role?`;
            }

            deleteRecordButton.onclick = function () {
                console.log('Sending DELETE request to:', url);

                // Show loading on button
                const originalText = deleteRecordButton.innerHTML;
                deleteRecordButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Removing...';
                deleteRecordButton.disabled = true;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            confirmButtonColor: '#405189',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to remove user from role.',
                            confirmButtonColor: '#405189'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while removing the user from the role.',
                        confirmButtonColor: '#405189'
                    });
                })
                .finally(() => {
                    // Reset button
                    deleteRecordButton.innerHTML = originalText;
                    deleteRecordButton.disabled = false;

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(deleteRecordModal);
                    modal.hide();
                });
            };
        });
    }

    // Pagination handling
    const paginationLinks = document.querySelectorAll('#pagination-element a[data-url]');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            if (url && !this.classList.contains('disabled')) {
                window.location.href = url;
            }
        });
    });

    // Table checkbox handling
    const tableCheckAll = document.getElementById('checkAll');
    const tableCheckboxes = document.querySelectorAll('#userList tbody input[name="chk_child"]');

    if (tableCheckAll && tableCheckboxes.length > 0) {
        tableCheckAll.addEventListener('change', function() {
            tableCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const row = checkbox.closest('tr');
                if (row) {
                    row.classList.toggle('table-active', this.checked);
                }
            });
        });

        tableCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('tr');
                if (row) {
                    row.classList.toggle('table-active', this.checked);
                }

                // Update "check all" state
                const allChecked = Array.from(tableCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(tableCheckboxes).some(cb => cb.checked);
                tableCheckAll.checked = allChecked;
                tableCheckAll.indeterminate = someChecked && !allChecked;
            });
        });
    }
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Popover(tooltipTriggerEl);
    });
});
</script>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Include SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
