@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Subject Vetting Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Subject Vetting Management</a></li>
                                <li class="breadcrumb-item active">Subject Vettings</li>
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

            <div id="subjectVettingList">
                <!-- Bar Chart for Vetting Status -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Vetting Status Distribution</h5>
                                <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                                    <canvas id="vettingStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search subject vetting assignments">
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
                                    <h5 class="card-title mb-0">Subject Vetting Assignments <span class="badge bg-dark-subtle text-dark ms-1" id="total-records">{{ $subjectvettings->count() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create subject-vettings')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addSubjectVettingModal" id="create-subject-vettings-btn"><i class="bi bi-plus-circle align-baseline me-1"></i> Create Subject Vetting Assignment</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_subject_vetting_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="vetting_username">Vetting Staff</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="subjectname">Subject</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sclass">Class</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="schoolarm">Arm</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="teachername">Teacher</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="termname">Term</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="sessionname">Session</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="datereg">Date Updated</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = 0 @endphp
                                            @forelse ($subjectvettings as $sv)
                                                <?php
                                                $picture = $sv->vetting_picture ?? 'unnamed.jpg';
                                                $imagePath = asset('storage/staff_avatars/' . $picture);
                                                $fileExists = file_exists(storage_path('app/public/staff_avatars/' . $picture));
                                                $defaultImageExists = file_exists(storage_path('app/public/staff_avatars/unnamed.jpg'));
                                                $rowClass = match ($sv->status ?? 'pending') {
                                                    'completed' => 'table-success',
                                                    'pending' => 'table-danger',
                                                    'rejected' => 'table-warning',
                                                    default => ''
                                                };
                                                ?>
                                                <tr data-url="{{ route('subjectvetting.destroy', $sv->svid) }}" class="{{ $rowClass }}">
                                                    <td class="id" data-id="{{ $sv->svid }}">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="vetting_username" data-vetting_userid="{{ $sv->vetting_userid }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                                <a href="javascript:void(0);">
                                                                    <div class="symbol-label">
                                                                        <img src="{{ $imagePath }}"
                                                                            alt="{{ $sv->vetting_username ?? 'Unknown Staff' }}"
                                                                            class="rounded-circle avatar-sm staff-image"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#imageViewModal"
                                                                            data-image="{{ $imagePath }}"
                                                                            data-picture="{{ $sv->vetting_picture ?? 'none' }}"
                                                                            data-teachername="{{ $sv->vetting_username ?? 'Unknown Staff' }}"
                                                                            data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                                                            data-default-exists="{{ $defaultImageExists ? 'true' : 'false' }}"
                                                                            onerror="this.src='{{ asset('storage/staff_avatars/unnamed.jpg') }}'; console.log('Table image failed to load for staff: {{ $sv->vetting_username ?? 'unknown' }}, picture: {{ $sv->vetting_picture ?? 'none' }}');" />
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="d-flex flex-column">
                                                                <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $sv->vetting_username ?? 'N/A' }}</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="subjectname" data-subjectclassid="{{ $sv->subjectclassid }}">{{ $sv->subjectname ?? 'N/A' }} {{ $sv->subjectcode ? '(' . $sv->subjectcode . ')' : '' }}</td>
                                                    <td class="sclass" data-schoolclassid="{{ $sv->schoolclassid }}">{{ $sv->sclass ?? 'N/A' }}</td>
                                                    <td class="schoolarm">{{ $sv->schoolarm ?? 'N/A' }}</td>
                                                    <td class="teachername" data-subtid="{{ $sv->subtid }}">{{ $sv->teachername ?? 'N/A' }}</td>
                                                    <td class="termname" data-termid="{{ $sv->termid }}">{{ $sv->termname ?? 'N/A' }}</td>
                                                    <td class="sessionname" data-sessionid="{{ $sv->sessionid }}">{{ $sv->sessionname ?? 'N/A' }}</td>
                                                    <td class="status">{{ $sv->status ?? 'pending' }}</td>
                                                    <td class="datereg">{{ $sv->updated_at ? $sv->updated_at->format('Y-m-d') : 'N/A' }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update subject-vettings')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete subject-vettings')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="noresult">
                                                    <td colspan="12" class="text-center">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Client-side pagination controls -->
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span id="showing-records">0</span> of <span id="total-records-footer">{{ $subjectvettings->count() }}</span> Results
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

                <!-- Add Subject Vetting Modal -->
                <div id="addSubjectVettingModal" class="modal fade" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="addModalLabel" class="modal-title">Add Subject Vetting Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="add-subjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" id="add-id-field" name="id">
                                    <div class="mb-3">
                                        <label for="userid" class="form-label">Vetting Staff</label>
                                        <select name="userid" id="userid" class="form-control" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Terms</label>
                                        <div class="checkbox-group" style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($terms as $term)
                                                <div class="form-check me-3">
                                                    <input class="form-check-input modal-checkbox" type="checkbox" name="termid[]" id="add-term-{{ $term->id }}" value="{{ $term->id }}">
                                                    <label class="form-check-label" for="add-term-{{ $term->id }}">
                                                        {{ $term->term }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sessionid" class="form-label">Session</label>
                                        <select name="sessionid" id="sessionid" class="form-control" required>
                                            <option value="">Select Session</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subject-Class Assignments</label>
                                        <div class="checkbox-group" style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($subjectclasses as $sc)
                                                <div class="form-check me-3">
                                                    <input class="form-check-input modal-checkbox" type="checkbox" name="subjectclassid[]" id="add-subjectclass-{{ $sc->scid }}" value="{{ $sc->scid }}" data-termid="{{ $sc->termid }}">
                                                    <label class="form-check-label" for="add-subjectclass-{{ $sc->scid }}">
                                                        {{ $sc->subjectname ?? 'N/A' }} {{ $sc->subjectcode ? '(' . $sc->subjectcode . ')' : '' }} - {{ $sc->sclass ?? 'N/A' }} {{ $sc->schoolarm ? '(' . $sc->schoolarm . ')' : '' }} - {{ $sc->teachername ?? 'N/A' }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="add-btn">Add Subject Vetting Assignment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Subject Vetting Modal -->
                <div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="editModalLabel" class="modal-title">Edit Subject Vetting Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="tablelist-form" autocomplete="off" id="edit-subjectvetting-form">
                                <div class="modal-body">
                                    <input type="hidden" id="edit-id-field" name="id">
                                    <div class="mb-3">
                                        <label for="edit-userid" class="form-label">Vetting Staff</label>
                                        <select name="userid" id="edit-userid" class="form-control" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($staff as $staff_member)
                                                <option value="{{ $staff_member->id }}">{{ $staff_member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-termid" class="form-label">Term</label>
                                        <select name="termid" id="edit-termid" class="form-control" required>
                                            <option value="">Select Term</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-sessionid" class="form-label">Session</label>
                                        <select name="sessionid" id="edit-sessionid" class="form-control" required>
                                            <option value="">Select Session</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-subjectclassid" class="form-label">Subject-Class</label>
                                        <select name="subjectclassid" id="edit-subjectclassid" class="form-control" required>
                                            <option value="">Select Subject-Class</option>
                                            @foreach ($subjectclasses as $sc)
                                                <option value="{{ $sc->scid }}">{{ $sc->subjectname ?? 'N/A' }} {{ $sc->subjectcode ? '(' . $sc->subjectcode . ')' : '' }} - {{ $sc->sclass ?? 'N/A' }} {{ $sc->schoolarm ? '(' . $sc->schoolarm . ')' : '' }} - {{ $sc->teachername ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-status" class="form-label">Status</label>
                                        <select name="status" id="edit-status" class="form-control" required>
                                            <option value="pending">Pending</option>
                                            <option value="completed">Completed</option>
                                            <option value="rejected">Rejected</option>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    // Pass status counts to JavaScript
    window.vettingStatusCounts = @json($statusCounts);
    console.log('Initial vettingStatusCounts:', window.vettingStatusCounts);
</script>

@endsection