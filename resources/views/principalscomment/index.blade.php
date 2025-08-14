@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Principals Comment Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Principals Comment Management</a></li>
                                <li class="breadcrumb-item active">Principals Comments</li>
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

            <div id="principalsCommentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search principals comment assignments">
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
                                    <h5 class="card-title mb-0">Principals Comment Assignments <span class="badge bg-dark-subtle text-dark ms-1" id="total-records">{{ $principalscomments->count() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create principals-comment')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addPrincipalsCommentModal" id="create-principals-comment-btn"><i class="bi bi-plus-circle align-baseline me-1"></i> Create Principals Comment Assignment</button>
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
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="staffname">Staff</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sclass">Class</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="schoolarm">Arm</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="datereg">Date Updated</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = 0 @endphp
                                            @forelse ($principalscomments as $pc)
                                                <?php
                                                $picture = $pc->picture ?? 'unnamed.jpg';
                                                $imagePath = asset('storage/staff_avatars/' . $picture);
                                                $fileExists = file_exists(storage_path('app/public/staff_avatars/' . $picture));
                                                $defaultImageExists = file_exists(storage_path('app/public/staff_avatars/unnamed.jpg'));
                                                ?>
                                                <tr data-url="{{ route('principalscomment.destroy', $pc->pcid) }}">
                                                    <td class="id" data-id="{{ $pc->pcid }}">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="staffname" data-staffid="{{ $pc->staffid }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                                <a href="javascript:void(0);">
                                                                    <div class="symbol-label">
                                                                        <img src="{{ $imagePath }}"
                                                                             alt="{{ $pc->staffname }}"
                                                                             class="rounded-circle avatar-md staff-image"
                                                                             data-bs-toggle="modal"
                                                                             data-bs-target="#imageViewModal"
                                                                             data-image="{{ $imagePath }}"
                                                                             data-picture="{{ $pc->picture ?? 'none' }}"
                                                                             data-teachername="{{ $pc->staffname }}"
                                                                             data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                                                             data-default-exists="{{ $defaultImageExists ? 'true' : 'false' }}"
                                                                             onerror="this.src='{{ asset('storage/staff_avatars/unnamed.jpg') }}'; console.log('Table image failed to load for staff: {{ $pc->staffname ?? 'unknown' }}, picture: {{ $pc->picture ?? 'none' }}');" />
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="d-flex flex-column">
                                                                <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $pc->staffname }}</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="sclass" data-schoolclassid="{{ $pc->schoolclassid }}">{{ $pc->sclass }}</td>
                                                    <td class="schoolarm">{{ $pc->schoolarm }}</td>
                                                    <td class="datereg">{{ $pc->updated_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update principals-comment')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete principals-comment')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="noresult">
                                                    <td colspan="7" class="text-center">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Client-side pagination controls -->
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span id="showing-records">0</span> of <span id="total-records-footer">{{ $principalscomments->count() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination listjs-pagination"></ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Principals Comment Modal -->
                <div id="addPrincipalsCommentModal" class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">Add Principals Comment Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="add-principalscomment-form">
                                <div class="modal-body">
                                    <input type="hidden" id="add-id-field" name="id">
                                    <div class="mb-3">
                                        <label for="staffId" class="form-label">Staff</label>
                                        <select name="staffId" id="staffId" class="form-control" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Classes</label>
                                        <div class="checkbox-group" style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($schoolclasses as $class)
                                                <div class="form-check me-3">
                                                    <input class="form-check-input modal-checkbox" type="checkbox" name="schoolclassid[]" id="add-class-{{ $class->id }}" value="{{ $class->id }}">
                                                    <label class="form-check-label" for="add-class-{{ $class->id }}">
                                                        {{ $class->schoolclass }} ({{ $class->arm }})
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="add-btn">Add Principals Comment Assignment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Principals Comment Modal -->
                <div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="editModalLabel" class="modal-title">Edit Principals Comment Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="edit-principalscomment-form">
                                <div class="modal-body">
                                    <input type="hidden" id="edit-id-field" name="id">
                                    <div class="mb-3">
                                        <label for="edit-staffId" class="form-label">Staff</label>
                                        <select name="staffId" id="edit-staffId" class="form-control" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-schoolclassid" class="form-label">Class</label>
                                        <select name="schoolclassid" id="edit-schoolclassid" class="form-control" required>
                                            <option value="">Select Class</option>
                                            @foreach ($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} ({{ $class->arm }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                                <h4>Are you sure?</h4>
                                <p>You won't be able to revert this!</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="delete-record">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Preview Modal -->
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-labelledby="imageViewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="imageViewModalLabel" class="modal-title">Staff Image Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="preview-image" src="" alt="Staff Image" class="img-fluid" style="max-height: 400px;" />
                                <p id="preview-teachername" class="mt-3"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
    </div>
</div>
{{-- 
@section('scripts')
    <script src="{{ asset('js/pages/principalscomment.init.js') }}"></script>
@endsection --}}

@endsection