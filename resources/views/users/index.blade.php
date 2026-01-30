@extends('layouts.master')

@section('content')
<?php
use Spatie\Permission\Models\Role;
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page title -->
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

            <!-- Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                <!-- Filters Row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search users...">
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
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                            <i class="bi bi-funnel align-baseline me-1"></i> Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Users
                                        <span class="badge bg-dark-subtle text-dark ms-1">{{ $data->count() }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                        @can('Create user')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal">
                                                <i class="bi bi-plus-circle align-baseline me-1"></i> Add User
                                            </button>
                                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                                <i class="bi bi-person-plus align-baseline me-1"></i> Add Student
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="userList">
                                        <thead class="table-active">
                                            <tr>
                                                <th scope="col" style="width: 50px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                                        <label class="form-check-label" for="checkAll"></label>
                                                    </div>
                                                </th>
                                                <th class="sort" data-sort="name">Name</th>
                                                <th class="sort" data-sort="email">Email</th>
                                                <th class="sort" data-sort="role">Role</th>
                                                <th class="sort" data-sort="datereg">Date Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($data as $user)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="name">
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('users.show', $user->id) }}" class="text-reset">
                                                                {{ $user->name }}
                                                            </a>
                                                        </h6>
                                                    </td>
                                                    <td class="email">{{ $user->email }}</td>
                                                    <td class="role">
                                                        @if ($user->getRoleNames()->isNotEmpty())
                                                            @foreach ($user->getRoleNames() as $roleName)
                                                                <span class="badge bg-primary-subtle text-primary">{{ $roleName }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">No Role</span>
                                                        @endif
                                                    </td>
                                                    <td class="datereg">{{ $user->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('View user')
                                                                <li>
                                                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-soft-primary btn-icon">
                                                                        <i class="ph-eye"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                            @can('Update user')
                                                                <li>
                                                                    <button type="button" class="btn btn-sm btn-soft-secondary btn-icon edit-item-btn" data-id="{{ $user->id }}">
                                                                        <i class="ph-pencil"></i>
                                                                    </button>
                                                                </li>
                                                            @endcan
                                                            @can('Delete user')
                                                                <li>
                                                                    <button type="button" class="btn btn-sm btn-soft-danger btn-icon remove-item-btn" data-id="{{ $user->id }}">
                                                                        <i class="ph-trash"></i>
                                                                    </button>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        No users found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add User Modal -->
            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">Add New User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="add-user-form">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Roles</label>
                                    <select name="roles[]" class="form-select" multiple required>
                                        @foreach (Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                                <div class="alert alert-danger d-none" id="add-user-error"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Create User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Student Modal -->
            <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Student as User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="student-search" class="form-label">Search by name or admission number</label>
                                <input type="text" id="student-search" class="form-control" placeholder="Type to search..." autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="student-select" class="form-label">Select Student</label>
                                <select id="student-select" class="form-select" required>
                                    <option value="">-- Search above to load students --</option>
                                </select>
                            </div>
                            <div class="alert alert-info small mb-0">
                                <i class="ri-information-fill me-1"></i>
                                Username will be admission number (slashes replaced with underscore)
                            </div>
                            <div class="alert alert-danger mt-3 d-none" id="student-select-error"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="proceed-to-credentials" disabled>Next: Set Credentials</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Credentials Modal -->
            <div class="modal fade" id="setStudentCredentialsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Set Login Credentials</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="add-student-credentials-form">
                            <div class="modal-body">
                                <input type="hidden" name="student_id" id="student-id-field">
                                <input type="hidden" name="name" id="student-name-field">

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="student-user-email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username (Admission No)</label>
                                    <input type="text" name="username" id="student-username" class="form-control" readonly required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="student-password" class="form-control" required>
                                        <button type="button" class="btn btn-outline-secondary" id="generate-temp-password">Generate</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="student-password_confirmation" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Roles <span class="text-danger">*</span></label>
                                    <select name="roles[]" id="student-role" class="form-select" multiple required>
                                        @foreach (Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="alert alert-danger d-none" id="student-credentials-error"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Back</button>
                                <button type="submit" class="btn btn-primary">Create Student Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Modal -->
            <div class="modal fade" id="whatsappModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Send Credentials via WhatsApp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Phone Number (international format)</label>
                                <input type="tel" class="form-control" id="whatsapp-phone" placeholder="+1234567890" required>
                            </div>
                            <div id="whatsapp-preview" class="d-none mb-3">
                                <p><strong>Message preview:</strong></p>
                                <div class="border p-3 bg-light rounded" id="whatsapp-message-preview"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" id="send-whatsapp-btn">Send via WhatsApp</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/list.js/list.min.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ────────────────────────────────────────────────────────────────
// Global variables & Chart
// ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {

    // Users by Role Chart
    const roleChartCanvas = document.getElementById('usersByRoleChart');
    if (roleChartCanvas) {
        new Chart(roleChartCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($role_counts ?? [])),
                datasets: [{
                    label: 'Number of Users',
                    data: @json(array_values($role_counts ?? [])),
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#858796'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // ────────────────────────────────────────────────────────────────
    // Add Student Modal – AJAX loading
    // ────────────────────────────────────────────────────────────────

    const addStudentModalEl   = document.getElementById('addStudentModal');
    const credentialsModalEl  = document.getElementById('setStudentCredentialsModal');

    if (!addStudentModalEl || !credentialsModalEl) return;

    const addStudentModal     = new bootstrap.Modal(addStudentModalEl);
    const credentialsModal    = new bootstrap.Modal(credentialsModalEl);

    const studentSearch       = document.getElementById('student-search');
    const studentSelect       = document.getElementById('student-select');
    const proceedBtn          = document.getElementById('proceed-to-credentials');
    const studentError        = document.getElementById('student-select-error');

    let currentStudent = null;

    let choicesInstance = null;
    if (typeof Choices !== 'undefined') {
        choicesInstance = new Choices(studentSelect, {
            searchEnabled: true,
            removeItemButton: false,
            placeholderValue: '-- Select a student --',
            noResultsText: 'No students found',
            noChoicesText: 'Start typing to search...'
        });
    }

    // Load students when modal is shown
    addStudentModalEl.addEventListener('show.bs.modal', function () {
        loadStudents('');
    });

    function loadStudents(searchTerm = '') {
        if (choicesInstance) {
            choicesInstance.clearChoices();
            choicesInstance.setChoices([{ value: '', label: 'Loading...', disabled: true }], 'value', 'label', true);
        } else {
            studentSelect.innerHTML = '<option value="">Loading...</option>';
        }

        proceedBtn.disabled = true;

        let url = '{{ route("get.students") }}';
        if (searchTerm.trim()) {
            url += `?search=${encodeURIComponent(searchTerm.trim())}`;
        }

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                studentError.textContent = data.message || 'Could not load students';
                studentError.classList.remove('d-none');
                return;
            }

            studentError.classList.add('d-none');

            const students = data.students || [];

            if (choicesInstance) {
                const options = students.map(s => ({
                    value: s.id,
                    label: `${s.name} (${s.admissionNo})`,
                    customProperties: {
                        'data-name': s.name,
                        'data-email': s.email || '',
                        'data-admission': s.admissionNo || ''
                    }
                }));
                choicesInstance.setChoices(options, 'value', 'label', true);
            } else {
                studentSelect.innerHTML = '<option value="">-- Select student --</option>';
                students.forEach(s => {
                    const opt = new Option(`${s.name} (${s.admissionNo})`, s.id);
                    opt.dataset.name = s.name;
                    opt.dataset.email = s.email || '';
                    opt.dataset.admission = s.admissionNo || '';
                    studentSelect.add(opt);
                });
            }

            proceedBtn.disabled = students.length === 0;
        })
        .catch(() => {
            studentError.textContent = 'Network error – please try again';
            studentError.classList.remove('d-none');
        });
    }

    // Live search
    studentSearch?.addEventListener('input', debounce(() => {
        loadStudents(studentSearch.value);
    }, 350));

    // Selection change
    studentSelect?.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption.value) {
            proceedBtn.disabled = true;
            currentStudent = null;
            return;
        }

        currentStudent = {
            id: selectedOption.value,
            name: selectedOption.dataset.name || '',
            email: selectedOption.dataset.email || '',
            admissionNo: selectedOption.dataset.admission || ''
        };

        proceedBtn.disabled = false;
    });

    // Proceed button
    proceedBtn?.addEventListener('click', function () {
        if (!currentStudent) return;

        document.getElementById('student-id-field').value = currentStudent.id;
        document.getElementById('student-name-field').value = currentStudent.name;
        document.getElementById('student-user-email').value = currentStudent.email;
        document.getElementById('student-username').value = (currentStudent.admissionNo || '').replace(/[\/\\]/g, '_');

        addStudentModal.hide();
        setTimeout(() => credentialsModal.show(), 300);
    });

    // Generate password
    document.getElementById('generate-temp-password')?.addEventListener('click', function () {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('student-password').value = password;
        document.getElementById('student-password_confirmation').value = password;
    });

    // Create student user form submit
    document.getElementById('add-student-credentials-form')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("users.store-student") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                credentialsModal.hide();
                setTimeout(() => location.reload(), 2200);
            } else {
                const errorContainer = document.getElementById('student-credentials-error');
                errorContainer.innerHTML = '';
                if (result.errors) {
                    Object.values(result.errors).forEach(errArr => {
                        errArr.forEach(msg => {
                            errorContainer.innerHTML += `<div>${msg}</div>`;
                        });
                    });
                } else {
                    errorContainer.innerHTML = result.message || 'An error occurred';
                }
                errorContainer.classList.remove('d-none');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Failed to connect to server', 'error');
        });
    });

    // Simple debounce utility
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

    // You can add your existing List.js / filterData() / edit / delete logic here
    // Example placeholder:
    // window.filterData = function() { ... }

});

</script>

@endsection
