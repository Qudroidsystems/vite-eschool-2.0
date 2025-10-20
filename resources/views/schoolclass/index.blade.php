@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
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
            <!-- End page title -->

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
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create school-class')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addSchoolClassModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Create School Class</button>
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
                                                <th class="min-w-125px sort cursor-pointer" data-sort="schoolclassid">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="schoolclass">School Class</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="arm">Arm</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="classcategory">Category</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = ($all_classes->currentPage() - 1) * $all_classes->perPage() @endphp
                                            @forelse ($all_classes as $class)
                                                <tr>
                                                    <td class="id" data-id="{{ $class->id }}">
                                                        <div class="form-check form-check-sm form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="schoolclassid">{{ ++$i }}</td>
                                                    <td class="schoolclass" data-schoolclass="{{ $class->schoolclass }}">{{ $class->schoolclass }}</td>
                                                    <td class="arm" data-arm-id="{{ $class->arm_id }}" data-arm="{{ $class->arm_name }}">{{ $class->arm_name }}</td>
                                                    <td class="classcategory" data-category-ids="{{ $class->classcategoryids }}" data-classcategory="{{ $class->classcategory }}">{{ $class->classcategory }}</td>
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
                                                    <td colspan="6" class="noresult" style="display: none;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
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
                            <h5 id="exampleModalLabel" class="modal-title">Add School Class</h5>
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
                            <h5 id="editModalLabel" class="modal-title">Edit School Class</h5>
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
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script src="{{ asset('theme/layouts/assets/js/schoolclass-list.init.js') }}"></script>

<script>
    window.routeUrls = {
        storeSchoolClass: '{{ route("schoolclass.store") }}',
        updateSchoolClass: '{{ route("schoolclass.update", "PLACEHOLDER") }}',
        destroySchoolClass: '{{ route("schoolclass.destroy", "PLACEHOLDER") }}',
        getArms: '{{ route("schoolclass.getarms", "PLACEHOLDER") }}'
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addForm = document.getElementById('add-schoolclass-form');
        const editForm = document.getElementById('edit-schoolclass-form');
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const addModal = new bootstrap.Modal(document.getElementById('addSchoolClassModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));

        let currentEditId = null;

        // Handle Add Form Submission
        if (addForm) {
            addForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(addForm);
                const submitBtn = document.getElementById('add-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

                axios.post(window.routeUrls.storeSchoolClass, formData)
                    .then(function (response) {
                        Swal.fire('Success!', response.data.message, 'success');
                        addModal.hide();
                        addForm.reset();
                        location.reload(); // Reload to show new records
                    })
                    .catch(function (error) {
                        if (error.response && error.response.status === 422) {
                            const errors = error.response.data.errors;
                            let errorMsg = '';
                            for (let key in errors) {
                                errorMsg += errors[key].join('<br>');
                            }
                            document.getElementById('add-alert-error-msg').innerHTML = errorMsg;
                            document.getElementById('add-alert-error-msg').classList.remove('d-none');
                        } else {
                            Swal.fire('Error!', error.response?.data?.message || 'Something went wrong', 'error');
                        }
                    })
                    .finally(function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Add Class';
                    });
            });
        }

        // Handle Edit Button Click
        document.querySelectorAll('.edit-item-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                currentEditId = row.querySelector('.id').dataset.id;
                const schoolclass = row.querySelector('.schoolclass').dataset.schoolclass;
                const armId = row.querySelector('.arm').dataset.armId;
                const categoryIdsStr = row.querySelector('.classcategory').dataset.categoryIds;

                document.getElementById('edit-id-field').value = currentEditId;
                document.getElementById('edit-schoolclass').value = schoolclass;

                // Set arm radio
                document.querySelectorAll('#edit-arm-radios input[type="radio"]').forEach(radio => {
                    radio.checked = radio.value == armId;
                });

                // Reset category checkboxes
                document.querySelectorAll('#edit-category-checkboxes input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Check the original categories
                if (categoryIdsStr) {
                    const categoryIds = categoryIdsStr.split(',');
                    categoryIds.forEach(catId => {
                        const checkbox = document.querySelector(`#edit-category-${catId.trim()}`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                }

                document.getElementById('edit-alert-error-msg').classList.add('d-none');
                editModal.show();
            });
        });

        // Handle Edit Form Submission
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!currentEditId) return;

                const formData = new FormData(editForm);
                formData.append('_method', 'PUT');

                const submitBtn = document.getElementById('update-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

                const updateUrl = window.routeUrls.updateSchoolClass.replace('PLACEHOLDER', currentEditId);
                axios.post(updateUrl, formData)
                    .then(function (response) {
                        Swal.fire('Success!', response.data.message, 'success');
                        editModal.hide();
                        editForm.reset();
                        currentEditId = null;
                        location.reload(); // Reload to show updated records
                    })
                    .catch(function (error) {
                        if (error.response && error.response.status === 422) {
                            const errors = error.response.data.errors;
                            let errorMsg = '';
                            for (let key in errors) {
                                errorMsg += errors[key].join('<br>');
                            }
                            document.getElementById('edit-alert-error-msg').innerHTML = errorMsg;
                            document.getElementById('edit-alert-error-msg').classList.remove('d-none');
                        } else {
                            Swal.fire('Error!', error.response?.data?.message || 'Something went wrong', 'error');
                        }
                    })
                    .finally(function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Update';
                    });
            });
        }

        // Handle Delete Button Click
        document.querySelectorAll('.remove-item-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                currentEditId = row.querySelector('.id').dataset.id; // Reuse for delete id
                deleteModal.show();
            });
        });

        // Handle Delete Confirmation
        document.getElementById('delete-record').addEventListener('click', function () {
            if (!currentEditId) return;

            const submitBtn = this;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

            const destroyUrl = window.routeUrls.destroySchoolClass.replace('PLACEHOLDER', currentEditId);
            axios.delete(destroyUrl)
                .then(function (response) {
                    Swal.fire('Success!', response.data.message, 'success');
                    deleteModal.hide();
                    location.reload();
                })
                .catch(function (error) {
                    Swal.fire('Error!', error.response?.data?.message || 'Something went wrong', 'error');
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Delete';
                });
        });

        // Handle Add Modal Close - Reset form
        addModal._element.addEventListener('hidden.bs.modal', function () {
            if (addForm) {
                addForm.reset();
                document.getElementById('add-alert-error-msg').classList.add('d-none');
            }
        });

        // Handle Edit Modal Close - Reset form
        editModal._element.addEventListener('hidden.bs.modal', function () {
            if (editForm) {
                editForm.reset();
                document.getElementById('edit-alert-error-msg').classList.add('d-none');
                currentEditId = null;
            }
        });
    });
</script>
@endsection