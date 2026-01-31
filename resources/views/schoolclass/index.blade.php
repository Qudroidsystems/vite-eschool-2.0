@extends('layouts.master')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">School Class Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">School Class Management</a></li>
                                <li class="breadcrumb-item active">School Classes</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
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

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="schoolClassList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" id="searchInput" class="form-control" placeholder="Search school classes..." value="{{ request()->query('search') }}">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
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
                                    <h5 class="card-title mb-0">School Classes <span class="badge bg-dark-subtle text-dark ms-1">{{ $all_classes->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions">
                                            <i class="ri-delete-bin-2-line align-bottom me-1"></i> Delete Selected
                                        </button>
                                        @can('Create school-class')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addSchoolClassModal">
                                                <i class="ri-add-circle-line align-bottom me-1"></i> Create School Class
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="schoolClassTable">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-125px">SN</th>
                                                <th class="min-w-150px">School Class</th>
                                                <th class="min-w-125px">Arm</th>
                                                <th class="min-w-350px">Category</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="schoolClassBody">
                                            @php $i = ($all_classes->currentPage() - 1) * $all_classes->perPage() @endphp
                                            @forelse ($all_classes as $class)
                                                <tr>
                                                    <td class="id" data-id="{{ $class->id }}">
                                                        <div class="form-check form-check-sm form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td>{{ ++$i }}</td>
                                                    <td class="schoolclass" data-schoolclass="{{ $class->schoolclass }}">{{ $class->schoolclass }}</td>
                                                    <td class="arm" data-arm-id="{{ $class->arm }}" data-arm="{{ $class->arm_name ?? '—' }}">{{ $class->arm_name ?? '—' }}</td>
                                                    <td class="classcategory" data-category-ids="{{ $class->classcategoryids ?? '' }}" data-classcategory="{{ $class->classcategory ?? '—' }}">{{ $class->classcategory ?? '—' }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update school-class')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete school-class')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">No school classes found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3 align-items-center">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $all_classes->count() }}</span> of <span class="fw-semibold">{{ $all_classes->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        {{ $all_classes->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add School Class Modal -->
            <div id="addSchoolClassModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add School Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-schoolclass-form">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <div class="mb-3">
                                    <label for="add-schoolclass" class="form-label">School Class</label>
                                    <input type="text" id="add-schoolclass" name="schoolclass" class="form-control" placeholder="Enter school class" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Arm(s)</label>
                                    <div class="d-flex flex-wrap gap-3" id="add-arm-checkboxes">
                                        @foreach ($arms as $arm)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input add-arm-checkbox" type="checkbox" value="{{ $arm->id }}" name="arm_id[]" id="add-arm-{{ $arm->id }}">
                                                <label class="form-check-label" for="add-arm-{{ $arm->id }}">{{ $arm->arm }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Category(s)</label>
                                    <div class="d-flex flex-wrap gap-3" id="add-category-checkboxes">
                                        @foreach ($classcategories as $category)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input add-category-checkbox" type="checkbox" value="{{ $category->id }}" name="classcategoryid[]" id="add-category-{{ $category->id }}">
                                                <label class="form-check-label" for="add-category-{{ $category->id }}">{{ $category->category }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="add-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add Class</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit School Class Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit School Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-schoolclass-form">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <div class="mb-3">
                                    <label for="edit-schoolclass" class="form-label">School Class</label>
                                    <input type="text" id="edit-schoolclass" name="schoolclass" class="form-control" placeholder="Enter school class" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Arm</label>
                                    <div class="d-flex flex-wrap gap-3" id="edit-arm-radios">
                                        @foreach ($arms as $arm)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input edit-arm-radio" type="radio" value="{{ $arm->id }}" name="arm_id" id="edit-arm-{{ $arm->id }}">
                                                <label class="form-check-label" for="edit-arm-{{ $arm->id }}">{{ $arm->arm }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Category(s)</label>
                                    <div class="d-flex flex-wrap gap-3" id="edit-category-checkboxes">
                                        @foreach ($classcategories as $category)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input edit-category-checkbox" type="checkbox" value="{{ $category->id }}" name="classcategoryid[]" id="edit-category-{{ $category->id }}">
                                                <label class="form-check-label" for="edit-category-{{ $category->id }}">{{ $category->category }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="deleteRecordModalLabel" class="modal-title">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <h4>Are you sure?</h4>
                            <p>You won't be able to revert this!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="delete-record">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .table td {
        vertical-align: middle;
    }
    .table .classcategory {
        white-space: normal;
        word-break: break-word;
        max-width: 400px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Route URLs (with placeholder)
    const routeUrls = {
        storeSchoolClass: '{{ route("schoolclass.store") }}',
        updateSchoolClass: '{{ route("schoolclass.update", ":id") }}',
        destroySchoolClass: '{{ route("schoolclass.destroy", ":id") }}',
        getArms: '{{ route("schoolclass.getarms", ":id") }}'
    };

    document.addEventListener('DOMContentLoaded', function () {
        console.log("DOM ready - initializing school class logic");

        const addForm = document.getElementById('add-schoolclass-form');
        const editForm = document.getElementById('edit-schoolclass-form');
        const editModalEl = document.getElementById('editModal');
        const addModalEl = document.getElementById('addSchoolClassModal');
        const deleteModalEl = document.getElementById('deleteRecordModal');

        if (!editModalEl || !addModalEl || !deleteModalEl) {
            console.error('Modal elements missing');
            return;
        }

        const editModal = new bootstrap.Modal(editModalEl);
        const addModal = new bootstrap.Modal(addModalEl);
        const deleteModal = new bootstrap.Modal(deleteModalEl);

        let currentEditId = null;

        // Simple native search (no List.js)
        const searchInput = document.querySelector('#searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('#schoolClassBody tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        // Check all checkbox
        document.getElementById('checkAll')?.addEventListener('change', function () {
            document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
                cb.checked = this.checked;
                cb.closest('tr')?.classList.toggle('table-active', this.checked);
            });
            toggleBulkButton();
        });

        function toggleBulkButton() {
            const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
            document.getElementById('remove-actions')?.classList.toggle('d-none', checked === 0);
        }

        // Checkbox change (delegation)
        document.querySelector('#schoolClassTable tbody')?.addEventListener('change', e => {
            if (e.target.matches('input[name="chk_child"]')) {
                const row = e.target.closest('tr');
                row?.classList.toggle('table-active', e.target.checked);
                toggleBulkButton();
            }
        });

        // Bulk delete
        document.getElementById('remove-actions')?.addEventListener('click', function () {
            const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
            if (checked.length === 0) return;

            const ids = Array.from(checked).map(cb => cb.closest('tr').querySelector('.id')?.dataset.id).filter(Boolean);

            Swal.fire({
                title: 'Delete Selected?',
                text: `This will delete ${ids.length} record(s).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete'
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

                try {
                    await Promise.all(ids.map(id => axios.delete(routeUrls.destroySchoolClass.replace(':id', id))));
                    Swal.fire('Deleted!', `${ids.length} record(s) removed.`, 'success');
                    location.reload();
                } catch (err) {
                    Swal.fire('Error!', err.response?.data?.message || 'Failed to delete', 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-delete-bin-2-line"></i> Delete Selected';
                }
            });
        });

        // Edit buttons (delegation)
        document.querySelector('#schoolClassTable tbody')?.addEventListener('click', e => {
            const btn = e.target.closest('.edit-item-btn');
            if (!btn) return;

            e.preventDefault();
            const row = btn.closest('tr');
            currentEditId = row?.querySelector('.id')?.dataset.id;

            if (!currentEditId) {
                Swal.fire('Error', 'Cannot find class ID', 'error');
                return;
            }

            const schoolclass = row.querySelector('.schoolclass')?.dataset.schoolclass || row.querySelector('.schoolclass')?.textContent.trim() || '';
            const armId = row.querySelector('.arm')?.dataset.armId || '';
            const catIdsStr = row.querySelector('.classcategory')?.dataset.categoryIds || '';

            document.getElementById('edit-id-field').value = currentEditId;
            document.getElementById('edit-schoolclass').value = schoolclass;

            document.querySelectorAll('#edit-arm-radios input[type="radio"]').forEach(radio => {
                radio.checked = (radio.value === armId);
            });

            document.querySelectorAll('#edit-category-checkboxes input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });

            if (catIdsStr) {
                catIdsStr.split(',').forEach(id => {
                    const clean = id.trim();
                    const cb = document.querySelector(`#edit-category-${clean}`);
                    if (cb) cb.checked = true;
                });
            }

            document.getElementById('edit-alert-error-msg')?.classList.add('d-none');
            editModal.show();
        });

        // Edit form
        editForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentEditId) return;

            const formData = new FormData(editForm);
            formData.append('_method', 'PUT');

            const btn = document.getElementById('update-btn');
            if (!btn) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            try {
                const res = await axios.post(
                    routeUrls.updateSchoolClass.replace(':id', currentEditId),
                    formData
                );
                Swal.fire('Success!', res.data.message || 'Updated!', 'success');
                editModal.hide();
                editForm.reset();
                currentEditId = null;
                location.reload();
            } catch (err) {
                let msg = err.response?.data?.message || 'Failed to update';
                if (err.response?.status === 422) {
                    msg = Object.values(err.response.data.errors).flat().join('<br>');
                }
                Swal.fire('Error!', msg, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Update';
            }
        });

        // Delete buttons (delegation)
        document.querySelector('#schoolClassTable tbody')?.addEventListener('click', e => {
            const btn = e.target.closest('.remove-item-btn');
            if (!btn) return;

            e.preventDefault();
            const row = btn.closest('tr');
            currentEditId = row?.querySelector('.id')?.dataset.id;

            if (currentEditId) deleteModal.show();
        });

        document.getElementById('delete-record')?.addEventListener('click', async () => {
            if (!currentEditId) return;

            const btn = document.getElementById('delete-record');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

            try {
                const res = await axios.delete(
                    routeUrls.destroySchoolClass.replace(':id', currentEditId)
                );
                Swal.fire('Success!', res.data.message || 'Deleted!', 'success');
                deleteModal.hide();
                location.reload();
            } catch (err) {
                Swal.fire('Error!', err.response?.data?.message || 'Failed to delete', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Delete';
            }
        });

        // Reset modals on hide
        addModalEl.addEventListener('hidden.bs.modal', () => {
            if (addForm) {
                addForm.reset();
                document.getElementById('add-alert-error-msg')?.classList.add('d-none');
            }
        });

        editModalEl.addEventListener('hidden.bs.modal', () => {
            if (editForm) {
                editForm.reset();
                document.getElementById('edit-alert-error-msg')?.classList.add('d-none');
                currentEditId = null;
            }
        });
    });
</script>

@endsection
