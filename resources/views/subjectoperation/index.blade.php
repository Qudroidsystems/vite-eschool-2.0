@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Subject Registration</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Student Management</a></li>
                                <li class="breadcrumb-item active">Subject Registration</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error!</strong> There were some problems with your input.<br>
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

            <div id="subjectList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idclass">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->schoolarm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <select class="form-control" id="idsession">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject Teachers Selection Card -->
                <div class="row" id="subjectTeachersCard">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Subject Teachers <span class="badge bg-primary-subtle text-primary ms-1" id="subjectTeacherCount">0</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects();">Select All</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="deselectAllSubjects();">Deselect All</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    Select the subjects you want to register or unregister students for. Only selected subjects will be used during registration or unregistration.
                                </div>
                                <div id="subjectTeachersContainer">
                                    @foreach ($schoolterms as $term)
                                        @if ($subjectTeachers && $subjectTeachers->where('termid', $term->id)->isNotEmpty())
                                            <h6 class="mt-3">{{ $term->term }}</h6>
                                            <div class="row">
                                                @foreach ($subjectTeachers->where('termid', $term->id) as $teacher)
                                                    <div class="col-md-4">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input subject-checkbox" type="checkbox" id="subject-{{ $teacher->subjectclassid }}"
                                                                data-subjectclassid="{{ $teacher->subjectclassid }}" data-staffid="{{ $teacher->userid }}"
                                                                data-termid="{{ $teacher->termid }}" checked>
                                                            <label class="form-check-label" for="subject-{{ $teacher->subjectclassid }}">
                                                                {{ $teacher->subjectname }} ({{ $teacher->staffname }})
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
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
                                    <div class="col-xxl-4">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search students">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idgender">
                                            <option value="ALL">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idadmission">
                                            <option value="ALL">Select Admission No</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
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
                                    <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $students ? $students->total() : 0 }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-inline-flex align-items-center">
                                        <button type="button" class="btn btn-primary d-none" id="register-selected-btn" onclick="registerSelectedStudentsBatch();" aria-label="Register selected students">
                                            Register Selected
                                        </button>
                                        <button type="button" class="btn btn-danger d-none ms-2" id="unregister-selected-btn" onclick="unregisterSelectedStudentsBatch();" aria-label="Unregister selected students">
                                            Unregister Selected
                                        </button>
                                        <div class="spinner-border text-primary ms-2 d-none" id="register-loading-spinner" role="status" aria-label="Loading">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">View Classes Registered</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="subjectListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th>SN</th>
                                                <th>Admission No</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Gender</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('subjectoperation.partials.student_rows')
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for Viewing Registered Classes -->
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="registeredClassesModalLabel">Registered Classes</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="registeredClassesContent"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>



                  <!-- Image View Modal -->
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.log('Enlarged image failed to load');">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('imageViewModal');
            if (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const imageSrc = button.getAttribute('data-image');
                    const modalImage = modal.querySelector('#enlargedImage');
                    
                    console.log('Modal opened, imageSrc:', imageSrc);
                    if (imageSrc) {
                        modalImage.src = imageSrc;
                    } else {
                        console.error('No data-image attribute found on the triggering element');
                        modalImage.src = '{{ asset('storage/student_avatars/unnamed.jpg') }}';
                    }
                });
            } else {
                console.error('Modal with ID imageViewModal not found');
            }
        });
    </script>
@endsection
