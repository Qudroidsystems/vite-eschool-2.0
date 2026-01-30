@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- View-Only Banner for Students -->
            @if(auth()->user()->hasRole('student'))
                <div class="alert alert-info alert-border-left border-info mb-4" role="alert">
                    <i class="ri-information-fill me-2"></i>
                    <strong>View Only Mode</strong><br>
                    As a student, you can view your profile details but cannot edit them here. Contact admin or staff for any updates.
                </div>
            @endif

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $user->name }} - Profile Settings</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                                <li class="breadcrumb-item active">Profile Settings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xxl-12">

                    <!-- Tabs Navigation -->
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-4">
                        <ul class="nav nav-pills arrow-navtabs nav-secondary gap-2 flex-grow-1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="personal-tab" data-bs-toggle="tab" href="#personalDetails" role="tab" aria-controls="personalDetails" aria-selected="true">
                                    <i class="ri-user-line me-1"></i> Personal Details
                                </a>
                            </li>

                            @if($isStaff)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="employment-tab" data-bs-toggle="tab" href="#employmentInfo" role="tab" aria-controls="employmentInfo" aria-selected="false">
                                    <i class="ri-briefcase-line me-1"></i> Employment
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="qualifications-tab" data-bs-toggle="tab" href="#qualifications" role="tab" aria-controls="qualifications" aria-selected="false">
                                    <i class="ri-graduation-cap-line me-1"></i> Qualifications
                                </a>
                            </li>
                            @endif

                            @if($isStudent && $studentData)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="student-tab" data-bs-toggle="tab" href="#studentInfo" role="tab" aria-controls="studentInfo" aria-selected="false">
                                    <i class="ri-user-star-line me-1"></i> Student Info
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="parent-tab" data-bs-toggle="tab" href="#parentInfo" role="tab" aria-controls="parentInfo" aria-selected="false">
                                    <i class="ri-parent-line me-1"></i> Parent Info
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="academic-tab" data-bs-toggle="tab" href="#academicInfo" role="tab" aria-controls="academicInfo" aria-selected="false">
                                    <i class="ri-book-line me-1"></i> Academic
                                </a>
                            </li>
                            @endif

                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="security-tab" data-bs-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">
                                    <i class="ri-lock-line me-1"></i> Security
                                </a>
                            </li>
                        </ul>

                        <div class="flex-shrink-0 ms-auto">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Users
                            </a>
                        </div>
                    </div>

                    <!-- Tab Content Container -->
                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content" id="profileTabContent">

                                <!-- Personal Details Tab -->
                                <div class="tab-pane fade show active" id="personalDetails" role="tabpanel" aria-labelledby="personal-tab">
                                    <div class="text-center mb-5">
                                        <div class="position-relative d-inline-block">
                                            <div class="avatar-xxl">
                                                @php
                                                    $avatarUrl = asset('images/default-avatar.png');
                                                    $hasAvatar = false;

                                                    if ($user->avatar) {
                                                        if ($user->isStaff()) {
                                                            $avatarUrl = asset('storage/staff_avatars/' . $user->avatar);
                                                            $hasAvatar = true;
                                                        } elseif ($user->isStudent()) {
                                                            $avatarUrl = asset('storage/student_avatars/' . $user->avatar);
                                                            $hasAvatar = true;
                                                        } else {
                                                            $avatarUrl = asset('storage/avatars/' . $user->avatar);
                                                            $hasAvatar = true;
                                                        }
                                                    }
                                                    elseif ($user->isStaff() && $staffPicture?->picture) {
                                                        $avatarUrl = asset('storage/staff_avatars/' . $staffPicture->picture);
                                                        $hasAvatar = true;
                                                    }
                                                    elseif ($user->isStudent() && $studentPicture?->picture) {
                                                        $avatarUrl = asset('storage/student_avatars/' . $studentPicture->picture);
                                                        $hasAvatar = true;
                                                    }

                                                    $initials = strtoupper(
                                                        substr($user->first_name ?? ($user->name ? explode(' ', $user->name)[0] : 'U'), 0, 1) .
                                                        substr($user->last_name ?? (explode(' ', $user->name)[1] ?? ''), 0, 1)
                                                    );
                                                @endphp

                                                @if($hasAvatar)
                                                    <img src="{{ $avatarUrl }}?t={{ time() }}"
                                                         alt="Profile"
                                                         class="rounded-circle img-thumbnail"
                                                         style="width: 150px; height: 150px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-title rounded-circle bg-light text-primary fs-1"
                                                         style="width: 150px; height: 150px; line-height: 150px;">
                                                        {{ $initials }}
                                                    </div>
                                                @endif
                                            </div>
                                            <h4 class="mt-3 mb-1">{{ $user->name }}</h4>
                                            <p class="text-muted mb-0">{{ $user->email }}</p>
                                            <div class="mt-2">
                                                @foreach($user->roles as $role)
                                                    <span class="badge bg-info me-1">{{ $role->name }}</span>
                                                @endforeach
                                                @if($isStudent && $studentData?->admissionNo)
                                                    <span class="badge bg-success">Admission: {{ $studentData->admissionNo }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if(!auth()->user()->hasRole('student'))
                                            <!-- Editable form for staff/admin -->
                                            <form action="{{ route('profile.update-info') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $user->id }}">
                                                <div class="row g-3">
                                                    <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Personal Information</h5></div>
                                                    <div class="col-md-6">
                                                        <label>First Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="fname" class="form-control" value="{{ old('fname', $userbio?->firstname ?? $studentData?->firstname ?? '') }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Last Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="lname" class="form-control" value="{{ old('lname', $userbio?->lastname ?? $studentData?->lastname ?? '') }}" required>
                                                    </div>
                                                    <!-- ... rest of your personal fields as before ... -->
                                                    <div class="col-12 text-end mt-4">
                                                        <button type="reset" class="btn btn-light me-2">Reset</button>
                                                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save Changes</button>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <!-- View-only for students -->
                                            <div class="row g-3">
                                                <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Personal Information</h5></div>
                                                <div class="col-md-6"><label>First Name</label><input type="text" class="form-control" value="{{ $userbio?->firstname ?? $studentData?->firstname ?? 'N/A' }}" readonly></div>
                                                <div class="col-md-6"><label>Last Name</label><input type="text" class="form-control" value="{{ $userbio?->lastname ?? $studentData?->lastname ?? 'N/A' }}" readonly></div>
                                                <!-- ... add other personal fields as readonly ... -->
                                            </div>
                                        @endif
                                    </div>

                                <!-- Employment Info Tab -->
                                @if($isStaff)
                                <div class="tab-pane fade" id="employmentInfo" role="tabpanel" aria-labelledby="employment-tab">
                                    @if(!auth()->user()->hasRole('student'))
                                        <!-- Editable form -->
                                        <form action="{{ route('profile.update-employment-info') }}" method="POST">
                                            @csrf
                                            <!-- ... your full employment form fields here ... -->
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-light me-2">Reset</button>
                                                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Update Employment Info</button>
                                            </div>
                                        </form>
                                    @else
                                        <!-- View-only placeholder -->
                                        <div class="text-center py-5 text-muted">
                                            <i class="ri-briefcase-line fs-1"></i>
                                            <h5 class="mt-3">Employment Information</h5>
                                            <p>Not applicable for students.</p>
                                        </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Qualifications Tab -->
                                @if($isStaff)
                                <div class="tab-pane fade" id="qualifications" role="tabpanel" aria-labelledby="qualifications-tab">
                                    @if(!auth()->user()->hasRole('student'))
                                        <!-- Editable qualifications section -->
                                        <!-- Add New Qualification Form -->
                                        <div class="card mb-4 border">
                                            <div class="card-header bg-light">
                                                <h5 class="card-title mb-0"><i class="ri-add-circle-line me-2"></i>Add New Qualification</h5>
                                            </div>
                                            <div class="card-body">
                                                <form action="{{ route('profile.add-qualification') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <!-- ... your qualification form fields ... -->
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Existing Qualifications Table -->
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h5 class="card-title mb-0"><i class="ri-graduation-cap-line me-2"></i>Qualifications</h5>
                                            </div>
                                            <div class="card-body">
                                                @if($qualifications->count() > 0)
                                                    <!-- Table code as before -->
                                                @else
                                                    <p class="text-center text-muted py-4">No qualifications added.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-5 text-muted">
                                            <i class="ri-graduation-cap-line fs-1"></i>
                                            <h5 class="mt-3">Qualifications</h5>
                                            <p>Not applicable for students.</p>
                                        </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Student Info Tab -->
                                @if($isStudent && $studentData)
                                <div class="tab-pane fade" id="studentInfo" role="tabpanel" aria-labelledby="student-tab">
                                    <div class="row g-3">
                                        <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Student Information</h5></div>
                                        <div class="col-md-6"><label>Admission Number</label><input type="text" class="form-control" value="{{ $studentData?->admissionNo ?? 'N/A' }}" readonly></div>
                                        <!-- ... all other student fields as readonly ... -->
                                    </div>
                                </div>
                                @endif

                                <!-- Parent Info Tab -->
                                @if($isStudent && $parentData)
                                <div class="tab-pane fade" id="parentInfo" role="tabpanel" aria-labelledby="parent-tab">
                                    <div class="row g-3">
                                        <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Parent/Guardian Information</h5></div>
                                        <div class="col-md-6"><label>Father's Name</label><input type="text" class="form-control" value="{{ $parentData?->father ?? 'N/A' }}" readonly></div>
                                        <!-- ... other parent fields readonly ... -->
                                    </div>
                                </div>
                                @endif

                                <!-- Academic Info Tab -->
                                @if($isStudent)
                                <div class="tab-pane fade" id="academicInfo" role="tabpanel" aria-labelledby="academic-tab">
                                    <div class="row">
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0">Current Class</h5></div>
                                                <div class="card-body">
                                                    @if($currentClass?->schoolclass)
                                                        <!-- Current class details -->
                                                    @else
                                                        <p class="text-center text-muted py-4">No class assigned.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0">Class History</h5></div>
                                                <div class="card-body">
                                                    @if($classHistory?->count() > 0)
                                                        <!-- Table of history -->
                                                    @else
                                                        <p class="text-center text-muted py-4">No history available.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                    <div class="row">
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0">Email Address</h5></div>
                                                <div class="card-body">
                                                    <p><strong>Current Email:</strong> {{ $user->email }}</p>
                                                    @if(auth()->user()->hasRole('student'))
                                                        <small class="text-muted">Email changes must be requested through admin/staff.</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0">Password</h5></div>
                                                <div class="card-body">
                                                    <p><strong>Password Status:</strong> Set</p>
                                                    @if(auth()->user()->hasRole('student'))
                                                        <small class="text-muted">Password changes must be requested through admin/staff.</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Tab persistence - remember last active tab
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            localStorage.setItem('activeProfileTab', e.target.getAttribute('href'));
        });
    });

    const savedTab = localStorage.getItem('activeProfileTab');
    if (savedTab) {
        const tabElement = document.querySelector(`a[href="${savedTab}"]`);
        if (tabElement) {
            bootstrap.Tab.getOrCreateInstance(tabElement).show();
        }
    }
});
</script>
@endsection
