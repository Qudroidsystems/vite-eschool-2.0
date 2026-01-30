@extends('layouts.master')

@section('content')
<?php use Spatie\Permission\Models\Role; ?>

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

            <!-- Chart -->
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
                    <strong>Whoops!</strong> There were some problems.<br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                                        <select class="form-control" id="idRole" data-choices data-choices-search-false>
                                            <option value="all">Select Role</option>
                                            @foreach ($roles as $role => $name)
                                                <option value="{{ $role }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <select class="form-control" id="idEmail" data-choices data-choices-search-false>
                                            <option value="all">Select Email</option>
                                            @foreach ($data as $user)
                                                <option value="{{ $user->email }}">{{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                            <i class="bi bi-funnel me-1"></i> Filters
                                        </button>
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
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                        @can('Create user')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#showModal">
                                                <i class="bi bi-plus-circle me-1"></i> Add User
                                            </button>
                                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                                <i class="bi bi-person-plus me-1"></i> Add Student
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
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
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
                                                            <a href="{{ route('users.show', $user->id) }}" class="text-reset">{{ $user->name }}</a>
                                                        </h6>
                                                    </td>
                                                    <td class="email">{{ $user->email }}</td>
                                                    <td class="role">
                                                        @if($user->getRoleNames()->isNotEmpty())
                                                            @foreach($user->getRoleNames() as $val)
                                                                <label class="badge bg-primary">{{ $val }}</label>
                                                            @endforeach
                                                        @else
                                                            <label class="badge bg-secondary">No roles</label>
                                                        @endif
                                                    </td>
                                                    <td class="datereg">{{ $user->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('View user')
                                                                <li>
                                                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-subtle-primary btn-icon btn-sm">
                                                                        <i class="ph-eye"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                            @can('Update user')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn">
                                                                        <i class="ph-pencil"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete user')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn">
                                                                        <i class="ph-trash"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="noresult">No results found</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold" id="pagination-showing"></span> of
                                            <span class="fw-semibold" id="pagination-total"></span> Results
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
            <div id="showModal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="add-user-form">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Roles</label>
                                    <select name="roles[]" class="form-control" multiple required>
                                        @foreach(Role::all() as $role)
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal (placeholder – implement your edit logic in user-list.init.js or here) -->
            <div id="editModal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="edit-user-form">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <!-- fields filled by JS -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-md-5 text-center">
                            <div class="text-danger">
                                <i class="bi bi-trash display-4"></i>
                            </div>
                            <h3 class="mt-4">Are you sure?</h3>
                            <p class="text-muted">This action cannot be undone.</p>
                            <div class="mt-4">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" id="delete-record">Yes, Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Student Modal -->
            <div id="addStudentModal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Student as User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Search Student</label>
                                <input type="text" id="student-search" class="form-control mb-2" placeholder="Name or Admission No...">
                                <select id="student-select" class="form-control" required>
                                    <option value="">-- Search to load --</option>
                                </select>
                            </div>
                            <div class="alert alert-info small">
                                Username = admission number (/ → _)
                            </div>
                            <div class="alert alert-danger d-none" id="student-select-error"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="proceed-to-credentials" disabled>Proceed</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credentials Modal -->
            <div id="setStudentCredentialsModal" class="modal fade" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Set Credentials</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                    <label class="form-label">Username</label>
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
                                    <label class="form-label">Roles</label>
                                    <select name="roles[]" id="student-role" class="form-control" multiple required>
                                        @foreach(Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="alert alert-danger d-none" id="student-credentials-error"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </form>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Chart
    const ctx = document.getElementById("usersByRoleChart")?.getContext("2d");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: @json(array_keys($role_counts)),
                datasets: [{
                    label: "Users",
                    data: @json(array_values($role_counts)),
                    backgroundColor: ["#4e73df","#1cc88a","#36b9cc","#f6c23e","#e74a3b"]
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    }

    // ── Add Student Flow ────────────────────────────────────────────────

    const addModal    = document.getElementById('addStudentModal');
    const credModal   = document.getElementById('setStudentCredentialsModal');
    const searchInput = document.getElementById('student-search');
    const selectEl    = document.getElementById('student-select');
    const proceedBtn  = document.getElementById('proceed-to-credentials');
    const errorDiv    = document.getElementById('student-select-error');

    if (!addModal || !credModal) return;

    const addM = new bootstrap.Modal(addModal);
    const credM = new bootstrap.Modal(credModal);

    let selected = null;

    function loadStudents(q = '') {
        proceedBtn.disabled = true;
        errorDiv.classList.add('d-none');

        let url = '{{ route("get.students") }}' + (q ? `?search=${encodeURIComponent(q)}` : '');

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    errorDiv.textContent = res.message || 'Error';
                    errorDiv.classList.remove('d-none');
                    return;
                }

                selectEl.innerHTML = '<option value="">Choose...</option>';
                res.students.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.text = `${s.name} (${s.admissionNo})`;
                    opt.dataset.name = s.name;
                    opt.dataset.email = s.email || '';
                    opt.dataset.admission = s.admissionNo || '';
                    selectEl.appendChild(opt);
                });

                proceedBtn.disabled = res.students.length === 0;
            })
            .catch(() => {
                errorDiv.textContent = 'Network error';
                errorDiv.classList.remove('d-none');
            });
    }

    searchInput.addEventListener('input', debounce(() => loadStudents(searchInput.value.trim()), 400));

    selectEl.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) {
            proceedBtn.disabled = true;
            selected = null;
            return;
        }
        selected = {
            id: opt.value,
            name: opt.dataset.name,
            email: opt.dataset.email,
            admission: opt.dataset.admission
        };
        proceedBtn.disabled = false;
    });

    proceedBtn.addEventListener('click', () => {
        if (!selected) return;
        document.getElementById('student-id-field').value = selected.id;
        document.getElementById('student-name-field').value = selected.name;
        document.getElementById('student-user-email').value = selected.email;
        document.getElementById('student-username').value = selected.admission.replace(/\//g, '_');
        addM.hide();
        setTimeout(() => credM.show(), 300);
    });

    // Password generator
    document.getElementById('generate-temp-password').addEventListener('click', () => {
        const p = Math.random().toString(36).slice(-10) + 'A1!';
        document.getElementById('student-password').value = p;
        document.getElementById('student-password_confirmation').value = p;
    });

    // Form submit with student role confirmation
    document.getElementById('add-student-credentials-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const roles = Array.from(document.getElementById('student-role').selectedOptions).map(o => o.value);
        const hasStudentRole = roles.includes('student');

        const submit = () => {
            const fd = new FormData(this);
            fd.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("users.store-student") }}', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire('Success', d.message, 'success');
                        credM.hide();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        const err = document.getElementById('student-credentials-error');
                        err.innerHTML = d.errors ? Object.values(d.errors).flat().join('<br>') : d.message;
                        err.classList.remove('d-none');
                    }
                });
        };

        if (hasStudentRole) {
            Swal.fire({
                title: 'Confirm Student Role',
                html: 'Assigning the <b>student</b> role links this account to student features.<br><br>Deleting this user <b>will not</b> delete the student record.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Proceed',
                cancelButtonText: 'Cancel'
            }).then(r => { if (r.isConfirmed) submit(); });
        } else {
            submit();
        }
    });

    function debounce(fn, ms) {
        let t;
        return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    }
});
</script>

@endsection
