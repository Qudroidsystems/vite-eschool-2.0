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
                        <h4 class="mb-sm-0">Users</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">User Management</a></li>
                                <li class="breadcrumb-item active">Users</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Users by Role Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Users by Role</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="usersByRoleChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

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

            <div id="userList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search users">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idRole" data-choices data-choices-search-false data-choices-removeItem>
                                            <option value="all">Select Role</option>
                                            @foreach ($roles as $role => $name)
                                                <option value="{{ $role }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <select class="form-control" id="idEmail" data-choices data-choices-search-false data-choices-removeItem>
                                            <option value="all">Select Email</option>
                                            @foreach ($data as $user)
                                                <option value="{{ $user->email }}">{{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Users <span class="badge bg-dark-subtle text-dark ms-1">{{ $data->count() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create user')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Add User</button>
                                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="bi bi-person-plus align-baseline me-1"></i> Add Student</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="userList">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="name">Name</th>
                                                <th class="sort cursor-pointer" data-sort="email">Email</th>
                                                <th class="sort cursor-pointer" data-sort="role">Role</th>
                                                <th class="sort cursor-pointer" data-sort="datereg">Date Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($data as $key => $user)
                                                <tr>
                                                    <td class="id" data-id="{{ $user->id }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="name" data-name="{{ $user->name }}">
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <h6 class="mb-0"><a href="{{ route('users.show', $user->id) }}" class="text-reset products">{{ $user->name }}</a></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="email" data-email="{{ $user->email }}">{{ $user->email }}</td>
                                                    <td class="role" data-roles="{{ $user->getRoleNames()->implode(',') }}">
                                                        <div>
                                                            @if(!empty($user->getRoleNames()))
                                                                @foreach($user->getRoleNames() as $val)
                                                                    <label class="badge bg-primary">{{ $val }}</label>
                                                                @endforeach
                                                            @else
                                                                <label class="badge bg-secondary">No roles</label>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="datereg">{{ $user->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('View user')
                                                                <li>
                                                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Update user')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete user')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold" id="pagination-showing"></span> of <span class="fw-semibold" id="pagination-total"></span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                                            <ul class="pagination listjs-pagination mb-0"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add User Modal -->
            <div id="showModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-user-form">
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select id="role" name="roles[]" class="form-control" multiple required>
                                        @foreach (Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-user-form">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <div class="mb-3">
                                    <label for="edit-name" class="form-label">Name</label>
                                    <input type="text" id="edit-name" name="name" class="form-control" placeholder="Enter name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-email" class="form-label">Email</label>
                                    <input type="email" id="edit-email" name="email" class="form-control" placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-role" class="form-label">Role</label>
                                    <select id="edit-role" name="roles[]" class="form-control" multiple required>
                                        @foreach (Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-password" class="form-label">Password (optional)</label>
                                    <input type="password" id="edit-password" name="password" class="form-control" placeholder="Enter new password">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" id="edit-password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete User Modal -->
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
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this record?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                <button type="button" class="btn w-sm btn-light btn-hover" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn w-sm btn-danger btn-hover" id="delete-record">Yes, Delete!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Student Modal -->
            <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Student as User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="student-search" class="form-label">Search student</label>
                                <input type="text" id="student-search" class="form-control" placeholder="Admission no, name..." autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="student-select" class="form-label">Select Student</label>
                                <select id="student-select" class="form-control" required>
                                    <option value="">-- Choose a student --</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <small>Username will be the admission number (slashes replaced with underscore).</small>
                            </div>
                            <div class="alert alert-danger d-none" id="student-select-error"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="proceed-to-credentials" disabled>Proceed to Credentials</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Set Student Credentials Modal -->
            <div id="setStudentCredentialsModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Set Credentials for Student</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-student-credentials-form">
                            <div class="modal-body">
                                <input type="hidden" id="student-id-field" name="student_id">
                                <input type="hidden" id="student-name-field" name="name">
                                <div class="mb-3">
                                    <label for="student-user-email" class="form-label">Email</label>
                                    <input type="email" id="student-user-email" name="email" class="form-control" placeholder="Enter email (required)" required>
                                    <div class="form-text">Prefilled from student record if available; edit if needed.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="student-username" class="form-label">Username (Admission Number)</label>
                                    <input type="text" id="student-username" name="username" class="form-control" readonly required>
                                </div>
                                <div class="mb-3">
                                    <label for="student-password" class="form-label">Temporary Password</label>
                                    <div class="input-group">
                                        <input type="password" id="student-password" name="password" class="form-control" placeholder="Temporary password will be generated" required>
                                        <button type="button" class="btn btn-outline-secondary" id="generate-temp-password" type="button">Generate</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="student-password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" id="student-password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="student-role" class="form-label">Role <span class="text-danger">*</span> (Select at least one)</label>
                                    <select id="student-role" name="roles[]" class="form-control" multiple required>
                                        @foreach (Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Hold Ctrl/Cmd to select multiple roles.</div>
                                </div>
                                <div class="alert alert-danger d-none" id="student-credentials-error"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="resetStudentCredentialsModal()">Close</button>
                                <button type="submit" class="btn btn-primary" id="create-student-user">Create User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Modal -->
            <div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="whatsappModalLabel">Send Credentials via WhatsApp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Enter the phone number to send the username and password via WhatsApp.</p>
                            <div class="mb-3">
                                <label for="whatsapp-phone" class="form-label">Phone Number (e.g., +1234567890)</label>
                                <input type="tel" class="form-control" id="whatsapp-phone" placeholder="Enter phone number" required>
                                <input type="hidden" id="whatsapp-user-id" value="">
                                <input type="hidden" id="whatsapp-email" value="">
                                <input type="hidden" id="whatsapp-password" value="">
                            </div>
                            <div id="whatsapp-link-container" class="mb-3 d-none">
                                <p>Click the link below to open WhatsApp with the pre-filled message:</p>
                                <a href="#" id="whatsapp-link" target="_blank" class="btn btn-success">Open WhatsApp</a>
                                <p class="mt-2"><strong>Preview:</strong> <span id="whatsapp-message-preview"></span></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="generate-whatsapp-link">Generate Link</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('theme/layouts/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/list.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/choices.min.js') }}" defer></script>
<script src="{{ asset('js/user-list.init.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Chart Initialization
    var ctx = document.getElementById("usersByRoleChart")?.getContext("2d");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: @json(array_keys($role_counts)),
                datasets: [{
                    label: "Users by Role",
                    data: @json(array_values($role_counts)),
                    backgroundColor: ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b"],
                    borderColor: ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b"],
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { position: "top" } }
            }
        });
    }

    // ── Add Student Modal Logic ────────────────────────────────────────────────

    const addStudentModalEl = document.getElementById('addStudentModal');
    const credentialsModalEl = document.getElementById('setStudentCredentialsModal');

    if (!addStudentModalEl || !credentialsModalEl) return;

    const addStudentModal = new bootstrap.Modal(addStudentModalEl);
    const credentialsModal = new bootstrap.Modal(credentialsModalEl);

    const searchInput = document.getElementById('student-search');
    const studentSelect = document.getElementById('student-select');
    const proceedBtn = document.getElementById('proceed-to-credentials');
    const errorEl = document.getElementById('student-select-error');

    let selectedStudent = null;

    // Load students on modal open
    addStudentModalEl.addEventListener('show.bs.modal', () => loadStudents(''));

    function loadStudents(search = '') {
        proceedBtn.disabled = true;
        errorEl?.classList.add('d-none');

        let url = '{{ route("get.students") }}';
        if (search.trim()) url += `?search=${encodeURIComponent(search.trim())}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    errorEl.textContent = data.message || 'Failed to load students';
                    errorEl.classList.remove('d-none');
                    return;
                }

                studentSelect.innerHTML = '<option value="">Choose a student...</option>';
                data.students.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = `${s.name} (${s.admissionNo})`;
                    opt.dataset.name = s.name;
                    opt.dataset.email = s.email || '';
                    opt.dataset.admission = s.admissionNo || '';
                    studentSelect.appendChild(opt);
                });

                proceedBtn.disabled = data.students.length === 0;
            })
            .catch(() => {
                errorEl.textContent = 'Network error – please try again';
                errorEl.classList.remove('d-none');
            });
    }

    // Live search
    searchInput?.addEventListener('input', debounce(e => {
        loadStudents(e.target.value.trim());
    }, 350));

    // Selection handler
    studentSelect?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) {
            proceedBtn.disabled = true;
            selectedStudent = null;
            return;
        }

        selectedStudent = {
            id: opt.value,
            name: opt.dataset.name,
            email: opt.dataset.email,
            admissionNo: opt.dataset.admission
        };
        proceedBtn.disabled = false;
    });

    // Proceed to credentials
    proceedBtn?.addEventListener('click', () => {
        if (!selectedStudent) return;

        document.getElementById('student-id-field').value = selectedStudent.id;
        document.getElementById('student-name-field').value = selectedStudent.name;
        document.getElementById('student-user-email').value = selectedStudent.email;
        document.getElementById('student-username').value = (selectedStudent.admissionNo || '').replace(/[\/\\]/g, '_');

        addStudentModal.hide();
        setTimeout(() => credentialsModal.show(), 300);
    });

    // Generate temporary password
    document.getElementById('generate-temp-password')?.addEventListener('click', () => {
        const temp = Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-4).toUpperCase();
        document.getElementById('student-password').value = temp;
        document.getElementById('student-password_confirmation').value = temp;
    });

    // Submit student creation form with confirmation for student role
    document.getElementById('add-student-credentials-form')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const rolesSelect = document.getElementById('student-role');
        const selectedRoles = Array.from(rolesSelect.selectedOptions).map(opt => opt.value);
        const hasStudentRole = selectedRoles.includes('student');

        const doSubmit = () => {
            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("users.store-student") }}', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success');
                    credentialsModal.hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const errorEl = document.getElementById('student-credentials-error');
                    errorEl.innerHTML = data.errors
                        ? Object.values(data.errors).flat().join('<br>')
                        : data.message || 'Error occurred';
                    errorEl.classList.remove('d-none');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Network error – please try again', 'error');
            });
        };

        if (hasStudentRole) {
            Swal.fire({
                title: 'Confirm Student Role',
                html: 'You are assigning the <strong>student</strong> role.<br><br>' +
                      'This links the user to student-specific features.<br>' +
                      '<small>Deleting this user account <strong>will not</strong> delete the student record from studentRegistration.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Create Student Account',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) doSubmit();
            });
        } else {
            doSubmit();
        }
    });

    // Reset credentials modal on close
    function resetStudentCredentialsModal() {
        document.getElementById('student-id-field').value = '';
        document.getElementById('student-name-field').value = '';
        document.getElementById('student-user-email').value = '';
        document.getElementById('student-username').value = '';
        document.getElementById('student-password').value = '';
        document.getElementById('student-password_confirmation').value = '';
        document.getElementById('student-credentials-error')?.classList.add('d-none');
    }

    credentialsModalEl?.addEventListener('hidden.bs.modal', resetStudentCredentialsModal);

    // Debounce helper
    function debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});
</script>
@endsection
