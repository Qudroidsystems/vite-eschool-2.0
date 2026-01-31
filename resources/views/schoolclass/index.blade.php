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
                                            <input type="text" class="form-control search" placeholder="Search school classes..." value="{{ request()->query('search') }}">
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
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="schoolclass-table">
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
                                                <tr data-id="{{ $group->id }}">
                                                    <td>
                                                        <div class="form-check form-check-sm form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td>{{ ++$i }}</td>
                                                    <td class="schoolclass-name">{{ $group->schoolclass }}</td>
                                                    <td class="arm-name" data-arm-id="{{ $group->arm_id ?? '' }}">{{ $group->arm_name ?? '—' }}</td>
                                                    <td class="categories" style="white-space: normal; word-break: break-word; max-width: 450px;"
                                                        data-category-ids="{{ implode(',', $group->categories->pluck('id')->toArray()) }}">
                                                        {{ $group->all_categories ?? '—' }}
                                                    </td>
                                                    <td class="description">{{ $group->description ?? '—' }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0 action-buttons">
                                                            @can('Update school-class')
                                                                <li>
                                                                    <button type="button" class="btn btn-subtle-secondary btn-icon btn-sm edit-btn">
                                                                        <i class="ph-pencil"></i>
                                                                    </button>
                                                                </li>
                                                            @endcan
                                                            @can('Delete school-class')
                                                                <li>
                                                                    <button type="button" class="btn btn-subtle-danger btn-icon btn-sm delete-btn">
                                                                        <i class="ph-trash"></i>
                                                                    </button>
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

            <!-- Add Modal -->
            <div class="modal fade" id="addSchoolClassModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add School Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="add-schoolclass-form">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="add-schoolclass" class="form-label">School Class <span class="text-danger">*</span></label>
                                    <input type="text" id="add-schoolclass" name="schoolclass" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add-description" class="form-label">Description</label>
                                    <textarea id="add-description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Arm(s)</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($arms as $arm)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input" type="checkbox" name="arm_id[]" value="{{ $arm->id }}" id="add-arm-{{ $arm->id }}">
                                                <label class="form-check-label" for="add-arm-{{ $arm->id }}">{{ $arm->arm }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category(s)</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($classcategories as $category)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input" type="checkbox" name="classcategoryid[]" value="{{ $category->id }}" id="add-cat-{{ $category->id }}">
                                                <label class="form-check-label" for="add-cat-{{ $category->id }}">{{ $category->category }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="add-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-submit-btn">Add Class</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editSchoolClassModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit School Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="edit-schoolclass-form">
                            @csrf
                            <input type="hidden" name="id" id="edit-id">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit-schoolclass" class="form-label">School Class <span class="text-danger">*</span></label>
                                    <input type="text" id="edit-schoolclass" name="schoolclass" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-description" class="form-label">Description</label>
                                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Arm</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($arms as $arm)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input edit-arm-radio" type="radio" name="arm_id" value="{{ $arm->id }}" id="edit-arm-{{ $arm->id }}">
                                                <label class="form-check-label" for="edit-arm-{{ $arm->id }}">{{ $arm->arm }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category(s)</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($classcategories as $category)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input edit-category" type="checkbox" name="classcategoryid[]" value="{{ $category->id }}" id="edit-cat-{{ $category->id }}">
                                                <label class="form-check-label" for="edit-cat-{{ $category->id }}">{{ $category->category }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="edit-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="edit-submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p>Are you sure you want to delete this school class?</p>
                            <p class="text-danger">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


    <script>
        window.schoolClassRoutes = {
            store:   "{{ route('schoolclass.store') }}",
            update:  "{{ route('schoolclass.update', ':id') }}",
            destroy: "{{ route('schoolclass.destroy', ':id') }}",
            getArms: "{{ route('schoolclass.getarms', ':id') ?? '' }}"  // optional if you still need it
        };
    </script>

    <!-- Keep your existing library includes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>

    <!-- Your main logic file -->
    {{-- <script src="{{ asset('theme/layouts/assets/js/schoolclass-list.init.js') }}"></script> --}}
@endsection
