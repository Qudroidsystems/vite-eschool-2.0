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
                                            <input type="text" class="form-control search" placeholder="Search school classes" value="{{ request()->query('search') }}">
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
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
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
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-80px">SN</th>
                                                <th class="min-w-150px">School Class</th>
                                                <th class="min-w-100px">Arm</th>
                                                <th class="min-w-350px">Category</th>
                                                <th class="min-w-200px">Description</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            @php $i = ($all_classes->currentPage() - 1) * $all_classes->perPage() @endphp
                                            @forelse ($all_classes as $group)
                                                <tr>
                                                    <td>
                                                        <div class="form-check form-check-sm form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $group->schoolclass }}</td>
                                                    <td>{{ $group->arm_name ?? '—' }}</td>
                                                    <td style="white-space: normal; word-break: break-word; max-width: 450px;">
                                                        {{ $group->all_categories ?? '—' }}
                                                    </td>
                                                    <td>{{ $group->description ?? '—' }}</td>
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
                                                    <td colspan="7" class="text-center py-4">No school classes found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3 align-items-center">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $all_classes->count() }}</span> of <span class="fw-semibold">{{ $all_classes->total() }}</span> results
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
                                    <label for="add-description" class="form-label">Description (optional)</label>
                                    <textarea id="add-description" name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
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
                                    <label for="edit-description" class="form-label">Description (optional)</label>
                                    <textarea id="edit-description" name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
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
            <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true" data-bs-backdrop="static">
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
    /* Enlarge checkboxes and radios in modals */
    #addSchoolClassModal .form-check-input,
    #editModal .form-check-input {
        width: 1.5em;
        height: 1.5em;
        margin-top: 0.15em;
    }
    #addSchoolClassModal .form-check-label,
    #editModal .form-check-label {
        font-size: 1.1em;
        line-height: 1.5em;
        margin-left: 0.5em;
    }
    /* Ensure delete modal is above other modals and backdrop */
    #deleteRecordModal {
        z-index: 1055;
    }
    #deleteRecordModal .modal-backdrop {
        z-index: 1050;
    }
    td.category-cell {
        white-space: normal !important;
        word-break: break-word;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script src="{{ asset('theme/layouts/assets/js/schoolclass-list.init.js') }}"></script>

<script>
    window.routeUrls = {
        storeSchoolClass: '{{ route("schoolclass.store") }}',
        updateSchoolClass: '{{ route("schoolclass.update", ":id") }}',
        destroySchoolClass: '{{ route("schoolclass.destroy", ":id") }}',
        getArms: '{{ route("schoolclass.getarms", ":id") }}'
    };

    document.addEventListener('DOMContentLoaded', function () {
        const addForm = document.getElementById('add-schoolclass-form');
        const editForm = document.getElementById('edit-schoolclass-form');
        const editModalEl = document.getElementById('editModal');
        const addModalEl = document.getElementById('addSchoolClassModal');
        const deleteModalEl = document.getElementById('deleteRecordModal');

        if (!editModalEl || !addModalEl || !deleteModalEl) {
            console.error('Modals not found in DOM');
            return;
        }

        const editModal = new bootstrap.Modal(editModalEl);
        const addModal = new bootstrap.Modal(addModalEl);
        const deleteModal = new bootstrap.Modal(deleteModalEl);

        let currentEditId = null;

        // Add form
        if (addForm) {
            addForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(addForm);
                const btn = document.getElementById('add-btn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

                try {
                    const res = await axios.post(window.routeUrls.storeSchoolClass, formData);
                    Swal.fire('Success!', res.data.message || 'Added!', 'success');
                    addModal.hide();
                    addForm.reset();
                    location.reload();
                } catch (err) {
                    let msg = err.response?.data?.message || 'Failed to add';
                    if (err.response?.status === 422) {
                        msg = Object.values(err.response.data.errors).flat().join('<br>');
                    }
                    Swal.fire('Error!', msg, 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'Add Class';
                }
            });
        }

        // Edit buttons
        document.querySelectorAll('.edit-item-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                currentEditId = row.querySelector('.id')?.dataset.id;
                if (!currentEditId) {
                    console.error('No ID found on row');
                    return;
                }

                const schoolclass = row.querySelector('.schoolclass')?.dataset.schoolclass || '';
                const armId = row.querySelector('.arm')?.dataset.armId || '';
                const catIdsStr = row.querySelector('.classcategory')?.dataset.categoryIds || '';

                document.getElementById('edit-id-field').value = currentEditId;
                document.getElementById('edit-schoolclass').value = schoolclass;

                document.querySelectorAll('#edit-arm-radios input[type="radio"]').forEach(r => {
                    r.checked = (r.value === armId);
                });

                document.querySelectorAll('#edit-category-checkboxes input[type="checkbox"]').forEach(cb => {
                    cb.checked = false;
                });

                if (catIdsStr) {
                    catIdsStr.split(',').forEach(id => {
                        const cb = document.querySelector(`#edit-category-${id.trim()}`);
                        if (cb) cb.checked = true;
                    });
                }

                document.getElementById('edit-alert-error-msg')?.classList.add('d-none');
                editModal.show();
            });
        });

        // Edit form
        if (editForm) {
            editForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!currentEditId) return;

                const formData = new FormData(editForm);
                formData.append('_method', 'PUT');

                const btn = document.getElementById('update-btn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

                try {
                    const res = await axios.post(window.routeUrls.updateSchoolClass.replace(':id', currentEditId), formData);
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
        }

        // Delete buttons
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                currentEditId = row.querySelector('.id')?.dataset.id;
                if (currentEditId) deleteModal.show();
            });
        });

        document.getElementById('delete-record')?.addEventListener('click', async function () {
            if (!currentEditId) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

            try {
                const res = await axios.delete(window.routeUrls.destroySchoolClass.replace(':id', currentEditId));
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

        // Reset modals
        [addModalEl, editModalEl, deleteModalEl].forEach(modalEl => {
            modalEl.addEventListener('hidden.bs.modal', () => {
                if (modalEl.id === 'addSchoolClassModal') {
                    addForm?.reset();
                    document.getElementById('add-alert-error-msg')?.classList.add('d-none');
                }
                if (modalEl.id === 'editModal') {
                    editForm?.reset();
                    document.getElementById('edit-alert-error-msg')?.classList.add('d-none');
                    currentEditId = null;
                }
            });
        });
    });
</script>

@endsection

@endsection
