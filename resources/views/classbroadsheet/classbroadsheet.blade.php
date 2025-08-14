@extends('layouts.master')

@section('content')
<style>
    .highlight-red { color: red !important; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
    .table-active { background-color: rgba(0, 0, 0, 0.05); }
    .table-centered th, .table-centered td { text-align: center; vertical-align: middle; }
    .table-nowrap th, .table-nowrap td { white-space: nowrap; }
    .sort.cursor-pointer:hover { background-color: #f5f5f5; }
    .form-control.teacher-comment-input,
    .form-control.guidance-comment-input,
    .form-control.remark-input,
    .form-control.absence-input { width: 100%; min-width: 150px; }
    .form-control.signature-input { max-width: 300px; }
    .btn-primary { margin-top: 1rem; }
    .signature-container { display: flex; align-items: center; gap: 10px; }

    /* Mobile-specific styles */
    @media (max-width: 991px) {
        .desktop-table {
            display: none;
        }
        
        .mobile-cards {
            display: block;
        }
        
        .student-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .student-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .student-details h6 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .student-meta {
            font-size: 14px;
            color: #6c757d;
        }
        
        .student-body {
            padding: 15px;
        }
        
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .subject-item {
            text-align: center;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .subject-name {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
        }
        
        .subject-score {
            font-size: 18px;
            font-weight: bold;
            color: #212529;
        }
        
        .subject-score.highlight-red {
            color: red !important;
        }
        
        .comments-section {
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
        }
        
        .comment-group {
            margin-bottom: 15px;
        }
        
        .comment-label {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }
        
        .form-control.mobile-comment {
            font-size: 14px;
            min-height: 38px;
        }
        
        .search-box {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            padding: 10px 0;
            margin-bottom: 15px;
        }
        
        .mobile-header-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-badge {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #0056b3;
        }
    }
    
    @media (min-width: 992px) {
        .mobile-cards {
            display: none;
        }
        
        .desktop-table {
            display: block;
        }
    }
    
    /* Enhanced mobile search */
    @media (max-width: 767px) {
        .subjects-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .mobile-header-info {
            flex-direction: column;
            gap: 10px;
        }
        
        .student-header {
            padding: 12px;
        }
        
        .student-body {
            padding: 12px;
        }
        
        .signature-container {
            flex-direction: column;
            align-items: flex-end;
        }
    }
    
    /* Search functionality styles */
    .search-highlight {
        background-color: #fff3cd;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    .no-results {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .results-count {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 15px;
        text-align: center;
    }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Class Broadsheet</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Class Broadsheet</a></li>
                                <li class="breadcrumb-item active">Class Broadsheet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error and Success Messages -->
            @if ($errors->any())
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('status') || session('success'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') ?: session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($students->isNotEmpty())
                <!-- Class Broadsheet Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Broadsheet for {{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Desktop Info Section -->
                                <div class="row g-3 desktop-table">
                                    <div class="d-flex flex-wrap flex-stack mb-4">
                                        <div class="d-flex flex-column flex-grow-1">
                                            <div class="d-flex flex-wrap">
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-building fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold">{{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Class</div>
                                                </div>
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar fs-3 text-success me-2"></i>
                                                        <div class="fs-2 fw-bold text-success">{{ $schoolterm }} | {{ $schoolsession }}</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Term | Session</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Info Section -->
                                <div class="mobile-cards">
                                    <div class="mobile-header-info">
                                        <div class="info-badge">
                                            <i class="bi bi-building me-1"></i>
                                            Class: {{ $schoolclass ? $schoolclass->schoolclass . ' ' . $schoolclass->arm : 'N/A' }}
                                        </div>
                                        <div class="info-badge">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ $schoolterm }} | {{ $schoolsession }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm bg-white">
                                        <form id="commentsForm" action="{{ route('classbroadsheet.updateComments', [$schoolclassid, $sessionid, $termid]) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            
                                            <!-- Search Box -->
                                            <div class="search-box mb-3">
                                                <input type="text" class="form-control search" placeholder="Search students, admission no, or comments..." id="searchInput">
                                                <div class="results-count mt-2" id="resultsCount" style="display: none;"></div>
                                            </div>

                                            <!-- Desktop Table View -->
                                            <div class="desktop-table">
                                                <div class="mt-3 result-table">
                                                    <div id="studentListTable" class="table-responsive">
                                                        <table class="table table-centered align-middle table-nowrap mb-0">
                                                            <thead class="table-active">
                                                                <tr>
                                                                    <th class="sort cursor-pointer" data-sort="sn">SN</th>
                                                                    <th class="sort cursor-pointer" data-sort="admissionno">Admission No</th>
                                                                    <th class="sort cursor-pointer" data-sort="name">Student Name</th>
                                                                    <th class="sort cursor-pointer" data-sort="gender">Gender</th>
                                                                    @foreach ($subjects as $subject)
                                                                        <th class="sort cursor-pointer" data-sort="subject-{{ \Illuminate\Support\Str::slug($subject->subject) }}">{{ $subject->subject }}</th>
                                                                    @endforeach
                                                                    <th class="sort cursor-pointer" data-sort="teacher-comment">Class Teacher's Comment</th>
                                                                    <th class="sort cursor-pointer" data-sort="guidance-comment">Guidance Counselor's Comment</th>
                                                                    <th class="sort cursor-pointer" data-sort="remark-other-activities">Remark on Other Activities</th>
                                                                    <th class="sort cursor-pointer" data-sort="absence-count">No. of Times Absent</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="list">
                                                                @forelse ($students as $key => $student)
                                                                    @php
                                                                        $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                                        $imagePath = asset('storage/student_avatars/' . $picture);
                                                                        $fileExists = file_exists(storage_path('app/public/student_avatars/' . $picture));
                                                                        $defaultImageExists = file_exists(storage_path('app/public/student_avatars/unnamed.jpg'));
                                                                        $profile = $personalityProfiles->where('studentid', $student->id)->first();
                                                                    @endphp
                                                                    <tr class="student-row" data-student-id="{{ $student->id }}">
                                                                        <td class="sn">{{ $key + 1 }}</td>
                                                                        <td class="admissionno" data-admissionno="{{ $student->admissionNo }}">{{ $student->admissionNo }}</td>
                                                                        <td class="name" data-name="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}">
                                                                            <div class="d-flex align-items-center">
                                                                                <img src="{{ $imagePath }}"
                                                                                     alt="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}"
                                                                                     class="rounded-circle avatar-sm student-image"
                                                                                     data-bs-toggle="modal"
                                                                                     data-bs-target="#imageViewModal"
                                                                                     data-image="{{ $imagePath }}"
                                                                                     data-admissionno="{{ $student->admissionNo }}"
                                                                                     data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                                                                     data-default-exists="{{ $defaultImageExists ? 'true' : 'false' }}"
                                                                                     onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.log('Table image failed to load for admissionno: {{ $student->admissionNo ?? 'unknown' }}, picture: {{ $student->picture ?? 'none' }}');" />
                                                                                <div class="ms-3">
                                                                                    <h6 class="mb-0">
                                                                                        <a href="{{ route('myclass.studentpersonalityprofile', [$student->id, $schoolclassid, $termid, $sessionid]) }}"
                                                                                           class="text-reset">
                                                                                            {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                                        </a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="gender" data-gender="{{ $student->gender ?? 'N/A' }}">{{ $student->gender ?? 'N/A' }}</td>
                                                                        @foreach ($subjects as $subject)
                                                                            @php
                                                                                $score = $scores->where('student_id', $student->id)->where('subject_name', $subject->subject)->first();
                                                                            @endphp
                                                                            <td class="subject-{{ \Illuminate\Support\Str::slug($subject->subject) }}"
                                                                                data-subject-{{ \Illuminate\Support\Str::slug($subject->subject) }}="{{ $score ? $score->total : '-' }}"
                                                                                align="center" style="font-size: 14px;"
                                                                                @if ($score && is_numeric($score->total) && $score->total <= 50) class="highlight-red" @endif>
                                                                                {{ $score ? $score->total : '-' }}
                                                                            </td>
                                                                        @endforeach
                                                                        <td class="teacher-comment">
                                                                            <input type="text" class="form-control teacher-comment-input"
                                                                                   name="teacher_comments[{{ $student->id }}]"
                                                                                   value="{{ $profile ? $profile->classteachercomment : '' }}"
                                                                                   data-teacher-comment="{{ $profile ? $profile->classteachercomment : 'N/A' }}"
                                                                                   placeholder="Enter teacher's comment">
                                                                        </td>
                                                                        <td class="guidance-comment">
                                                                            <input type="text" class="form-control guidance-comment-input"
                                                                                   name="guidance_comments[{{ $student->id }}]"
                                                                                   value="{{ $profile ? $profile->guidancescomment : '' }}"
                                                                                   data-guidance-comment="{{ $profile ? $profile->guidancescomment : 'N/A' }}"
                                                                                   placeholder="Enter guidance counselor's comment">
                                                                        </td>
                                                                        <td class="remark-other-activities">
                                                                            <input type="text" class="form-control remark-input"
                                                                                   name="remarks_on_other_activities[{{ $student->id }}]"
                                                                                   value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                                                                   data-remark-other-activities="{{ $profile ? $profile->remark_on_other_activities : 'N/A' }}"
                                                                                   placeholder="Enter remark on other activities">
                                                                        </td>
                                                                        <td class="absence-count">
                                                                            <input type="number" class="form-control absence-input"
                                                                                   name="no_of_times_school_absent[{{ $student->id }}]"
                                                                                   value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                                                                   data-absence-count="{{ $profile ? $profile->no_of_times_school_absent : '0' }}"
                                                                                   min="0" placeholder="Enter absence count">
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="{{ 9 + count($subjects) }}" class="noresult" style="display: block;">
                                                                            <div class="text-center">
                                                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                                                           colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                                                                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                                                                <p class="text-muted mb-0">We've searched for the student data but did not find any matches.</p>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="mobile-cards">
                                                <div id="mobileStudentCards">
                                                    @forelse ($students as $key => $student)
                                                        @php
                                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                            $imagePath = asset('storage/student_avatars/' . $picture);
                                                            $fileExists = file_exists(storage_path('app/public/student_avatars/' . $picture));
                                                            $defaultImageExists = file_exists(storage_path('app/public/student_avatars/unnamed.jpg'));
                                                            $profile = $personalityProfiles->where('studentid', $student->id)->first();
                                                        @endphp
                                                        <div class="student-card" data-student-id="{{ $student->id }}" 
                                                             data-search-content="{{ strtolower($student->lastname . ' ' . $student->firstname . ' ' . $student->othername . ' ' . $student->admissionNo . ' ' . ($profile ? $profile->classteachercomment : '') . ' ' . ($profile ? $profile->guidancescomment : '') . ' ' . ($profile ? $profile->remark_on_other_activities : '') . ' ' . ($profile ? $profile->no_of_times_school_absent : '0')) }}">
                                                            
                                                            <!-- Student Header -->
                                                            <div class="student-header">
                                                                <div class="student-info">
                                                                    <img src="{{ $imagePath }}"
                                                                         alt="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}"
                                                                         class="rounded-circle avatar-sm student-image"
                                                                         data-bs-toggle="modal"
                                                                         data-bs-target="#imageViewModal"
                                                                         data-image="{{ $imagePath }}"
                                                                         data-admissionno="{{ $student->admissionNo }}"
                                                                         data-file-exists="{{ $fileExists ? 'true' : 'false' }}"
                                                                         data-default-exists="{{ $defaultImageExists ? 'true' : 'false' }}"
                                                                         onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';" />
                                                                    <div class="student-details">
                                                                        <h6>
                                                                            <a href="{{ route('myclass.studentpersonalityprofile', [$student->id, $schoolclassid, $termid, $sessionid]) }}"
                                                                               class="text-reset text-decoration-none">
                                                                                {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                            </a>
                                                                        </h6>
                                                                        <div class="student-meta">
                                                                            <span class="me-3"><strong>SN:</strong> {{ $key + 1 }}</span>
                                                                            <span class="me-3"><strong>Admission:</strong> {{ $student->admissionNo }}</span>
                                                                            <span><strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Student Body -->
                                                            <div class="student-body">
                                                                <!-- Subjects Grid -->
                                                                <div class="subjects-grid">
                                                                    @foreach ($subjects as $subject)
                                                                        @php
                                                                            $score = $scores->where('student_id', $student->id)->where('subject_name', $subject->subject)->first();
                                                                        @endphp
                                                                        <div class="subject-item">
                                                                            <div class="subject-name">{{ $subject->subject }}</div>
                                                                            <div class="subject-score @if ($score && is_numeric($score->total) && $score->total <= 50) highlight-red @endif">
                                                                                {{ $score ? $score->total : '-' }}
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>

                                                                <!-- Comments Section -->
                                                                <div class="comments-section">
                                                                    <div class="comment-group">
                                                                        <div class="comment-label">Class Teacher's Comment</div>
                                                                        <input type="text" class="form-control mobile-comment teacher-comment-input"
                                                                               name="teacher_comments[{{ $student->id }}]"
                                                                               value="{{ $profile ? $profile->classteachercomment : '' }}"
                                                                               placeholder="Enter teacher's comment">
                                                                    </div>
                                                                    <div class="comment-group">
                                                                        <div class="comment-label">Guidance Counselor's Comment</div>
                                                                        <input type="text" class="form-control mobile-comment guidance-comment-input"
                                                                               name="guidance_comments[{{ $student->id }}]"
                                                                               value="{{ $profile ? $profile->guidancescomment : '' }}"
                                                                               placeholder="Enter guidance counselor's comment">
                                                                    </div>
                                                                    <div class="comment-group">
                                                                        <div class="comment-label">Remark on Other Activities</div>
                                                                        <input type="text" class="form-control mobile-comment remark-input"
                                                                               name="remarks_on_other_activities[{{ $student->id }}]"
                                                                               value="{{ $profile ? $profile->remark_on_other_activities : '' }}"
                                                                               placeholder="Enter remark on other activities">
                                                                    </div>
                                                                    <div class="comment-group">
                                                                        <div class="comment-label">No. of Times Absent</div>
                                                                        <input type="number" class="form-control mobile-comment absence-input"
                                                                               name="no_of_times_school_absent[{{ $student->id }}]"
                                                                               value="{{ $profile ? $profile->no_of_times_school_absent : '' }}"
                                                                               min="0" placeholder="Enter absence count">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="no-results">
                                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                                       colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                                            <p class="text-muted mb-0">We've searched for the student data but did not find any matches.</p>
                                                        </div>
                                                    @endforelse
                                                </div>
                                                
                                                <div id="noMobileResults" class="no-results" style="display: none;">
                                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                               colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                                                    <h5 class="mt-2">No matches found</h5>
                                                    <p class="text-muted mb-0">Try adjusting your search terms.</p>
                                                </div>
                                            </div>

                                            <!-- Save Button and Signature Input -->
                                            <div class="d-flex justify-content-end mt-3 signature-container">
                                                <div class="form-group">
                                                    <label for="signature" class="comment-label">Upload Signature (JPG, PNG, or PDF)</label>
                                                    <input type="file" class="form-control signature-input" name="signature" id="signature" accept=".jpg,.jpeg,.png,.pdf" >
                                                </div>
                                                <button type="submit" class="btn btn-primary">Save Data</button>
                                            </div>

                                            <!-- Desktop Pagination -->
                                            <div class="desktop-table">
                                                <div class="d-flex justify-content-end mt-3">
                                                    <div class="pagination-wrap hstack gap-2">
                                                        <span>Showing <span id="pagination-showing">0</span> of <span id="pagination-total">0</span> entries</span>
                                                        <ul class="pagination listjs-pagination mb-0"></ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image View Modal -->
                <div class="row">
                    <div class="col-12">
                        <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Student Picture</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img id="enlargedImage" src="" alt="Student Picture" class="img-fluid" />
                                        <div class="placeholder-text">No image available</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            No student data found for this class, term, and session.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Inline Script to Pass Subjects to JavaScript -->
<script>
    window.subjects = [
        @foreach ($subjects as $subject)
            '{{ \Illuminate\Support\Str::slug($subject->subject) }}',
        @endforeach
    ];
</script>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Show content after DOM is loaded to prevent FOUC
        document.querySelector('.main-content').classList.add('loaded');

        // Form submission handler to synchronize inputs
        const form = document.getElementById('commentsForm');
        form.addEventListener('submit', function (event) {
            // Synchronize mobile and desktop inputs
            const studentRows = document.querySelectorAll('.desktop-table .student-row');
            const studentCards = document.querySelectorAll('.mobile-cards .student-card');

            studentRows.forEach(row => {
                const studentId = row.getAttribute('data-student-id');
                const mobileCard = Array.from(studentCards).find(card => card.getAttribute('data-student-id') === studentId);

                if (mobileCard) {
                    // Sync mobile inputs to desktop inputs
                    const desktopTeacherInput = row.querySelector('.teacher-comment-input');
                    const desktopGuidanceInput = row.querySelector('.guidance-comment-input');
                    const desktopRemarkInput = row.querySelector('.remark-input');
                    const desktopAbsenceInput = row.querySelector('.absence-input');

                    const mobileTeacherInput = mobileCard.querySelector('.teacher-comment-input');
                    const mobileGuidanceInput = mobileCard.querySelector('.guidance-comment-input');
                    const mobileRemarkInput = mobileCard.querySelector('.remark-input');
                    const mobileAbsenceInput = mobileCard.querySelector('.absence-input');

                    // Update desktop inputs with mobile values if they exist
                    if (mobileTeacherInput && desktopTeacherInput) {
                        desktopTeacherInput.value = mobileTeacherInput.value;
                    }
                    if (mobileGuidanceInput && desktopGuidanceInput) {
                        desktopGuidanceInput.value = mobileGuidanceInput.value;
                    }
                    if (mobileRemarkInput && desktopRemarkInput) {
                        desktopRemarkInput.value = mobileRemarkInput.value;
                    }
                    if (mobileAbsenceInput && desktopAbsenceInput) {
                        desktopAbsenceInput.value = mobileAbsenceInput.value;
                    }
                }
            });

            // Remove mobile inputs to avoid duplicate names in the form
            document.querySelectorAll('.mobile-cards .teacher-comment-input, .mobile-cards .guidance-comment-input, .mobile-cards .remark-input, .mobile-cards .absence-input').forEach(input => {
                input.remove();
            });

            // Log form data for debugging
            const formData = new FormData(form);
            console.log('Form data:', Object.fromEntries(formData));

            // Client-side validation to prevent empty submission
            const teacherInputs = document.querySelectorAll('.teacher-comment-input');
            const guidanceInputs = document.querySelectorAll('.guidance-comment-input');
            const remarkInputs = document.querySelectorAll('.remark-input');
            const absenceInputs = document.querySelectorAll('.absence-input');
            let hasInput = false;

            teacherInputs.forEach(input => {
                if (input.value.trim() !== '') hasInput = true;
            });
            guidanceInputs.forEach(input => {
                if (input.value.trim() !== '') hasInput = true;
            });
            remarkInputs.forEach(input => {
                if (input.value.trim() !== '') hasInput = true;
            });
            absenceInputs.forEach(input => {
                if (input.value.trim() !== '') hasInput = true;
            });

            if (!hasInput) {
                event.preventDefault();
                alert('Please enter at least one field (comment, remark, or absence count) before submitting.');
            }
        });

        // Search functionality (unchanged)
        const searchInput = document.getElementById('searchInput');
        const resultsCount = document.getElementById('resultsCount');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();

                // Desktop table search
                const desktopRows = document.querySelectorAll('.desktop-table .student-row');
                let desktopVisibleCount = 0;

                desktopRows.forEach(row => {
                    const admissionNo = row.querySelector('.admissionno').textContent.toLowerCase();
                    const name = row.querySelector('.name').textContent.toLowerCase();
                    const teacherComment = row.querySelector('.teacher-comment-input')?.value.toLowerCase() || '';
                    const guidanceComment = row.querySelector('.guidance-comment-input')?.value.toLowerCase() || '';
                    const remarkActivity = row.querySelector('.remark-input')?.value.toLowerCase() || '';
                    const absenceCount = row.querySelector('.absence-input')?.value.toLowerCase() || '';

                    const searchContent = `${admissionNo} ${name} ${teacherComment} ${guidanceComment} ${remarkActivity} ${absenceCount}`;

                    if (searchTerm === '' || searchContent.includes(searchTerm)) {
                        row.style.display = '';
                        desktopVisibleCount++;

                        if (searchTerm) {
                            highlightSearchTerm(row, searchTerm);
                        } else {
                            removeHighlights(row);
                        }
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mobile cards search
                const mobileCards = document.querySelectorAll('.mobile-cards .student-card');
                const noMobileResults = document.getElementById('noMobileResults');
                let mobileVisibleCount = 0;

                mobileCards.forEach(card => {
                    const searchContent = card.getAttribute('data-search-content') || '';

                    if (searchTerm === '' || searchContent.includes(searchTerm)) {
                        card.style.display = '';
                        mobileVisibleCount++;

                        if (searchTerm) {
                            highlightSearchMobile(card, searchTerm);
                        } else {
                            removeHighlightsMobile(card);
                        }
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (noMobileResults) {
                    if (mobileCards.length > 0 && mobileVisibleCount === 0 && searchTerm) {
                        noMobileResults.style.display = 'block';
                    } else {
                        noMobileResults.style.display = 'none';
                    }
                }

                if (resultsCount) {
                    if (searchTerm && (desktopVisibleCount > 0 || mobileVisibleCount > 0)) {
                        resultsCount.textContent = `${desktopVisibleCount + mobileVisibleCount} result(s) found`;
                        resultsCount.style.display = 'block';
                    } else if (searchTerm && desktopVisibleCount === 0 && mobileVisibleCount === 0) {
                        resultsCount.textContent = `No matches found`;
                        resultsCount.style.display = 'block';
                    } else {
                        resultsCount.style.display = 'none';
                    }
                }
            });
        }

        // Highlight functions (unchanged)
        function highlightSearchTerm(element, term) {
            const highlightClass = 'search-highlight';
            const tagsToSearch = ['.admissionno', '.name'];
            tagsToSearch.forEach(selector => {
                const el = element.querySelector(selector);
                if (el) {
                    const originalText = el.textContent;
                    const regex = new RegExp(`(${term})`, 'gi');
                    el.innerHTML = originalText.replace(regex, `<span class="${highlightClass}">$1</span>`);
                }
            });
        }

        function removeHighlights(element) {
            const highlightClass = 'search-highlight';
            element.querySelectorAll('.' + highlightClass).forEach(span => {
                span.replaceWith(span.textContent);
            });
        }

        function highlightSearchMobile(card, term) {
            // Extend if needed for mobile view
        }

        function removeHighlightsMobile(card) {
            // Clear highlights in mobile view if implemented
        }
    });
</script>


@endsection
