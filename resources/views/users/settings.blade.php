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
                    <!-- Tabs -->
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-4">
                        <ul class="nav nav-pills arrow-navtabs nav-secondary gap-2 flex-grow-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails">
                                    <i class="ri-user-line me-1"></i> Personal Details
                                </a>
                            </li>
                            @if($isStaff)
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#employmentInfo">
                                    <i class="ri-briefcase-line me-1"></i> Employment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#qualifications">
                                    <i class="ri-graduation-cap-line me-1"></i> Qualifications
                                </a>
                            </li>
                            @endif
                            @if($isStudent && $studentData)
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#studentInfo">
                                    <i class="ri-user-star-line me-1"></i> Student Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#parentInfo">
                                    <i class="ri-parent-line me-1"></i> Parent Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#academicInfo">
                                    <i class="ri-book-line me-1"></i> Academic
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#security">
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

                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content">

                                <!-- Personal Details -->
                                <div class="tab-pane active" id="personalDetails" role="tabpanel">
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

                                        <!-- Student: View-only | Staff/Admin: Editable -->
                                        @if(!auth()->user()->hasRole('student'))
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
                                                    <div class="col-md-6">
                                                        <label>Other Names</label>
                                                        <input type="text" name="oname" class="form-control" value="{{ old('oname', $userbio?->othernames ?? $studentData?->othername ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Phone Number</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $userbio?->phone ?? $studentData?->phone_number ?? $user->phone_number ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Gender</label>
                                                        <select name="gender" class="form-control">
                                                            <option value="">Select</option>
                                                            <option value="male" {{ old('gender', $userbio?->gender ?? $studentData?->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                                            <option value="female" {{ old('gender', $userbio?->gender ?? $studentData?->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                                            <option value="other" {{ old('gender', $userbio?->gender ?? $studentData?->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                                                        </select>
                                                    </div>
                                                    @if(!$isStudent)
                                                    <div class="col-md-6">
                                                        <label>Marital Status</label>
                                                        <select name="maritalstatus" class="form-control">
                                                            <option value="">Select</option>
                                                            <option value="single" {{ old('maritalstatus', $userbio?->maritalstatus ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                                                            <option value="married" {{ old('maritalstatus', $userbio?->maritalstatus ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                                                            <option value="divorced" {{ old('maritalstatus', $userbio?->maritalstatus ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                                            <option value="widowed" {{ old('maritalstatus', $userbio?->maritalstatus ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                                        </select>
                                                    </div>
                                                    @endif
                                                    <div class="col-md-6">
                                                        <label>Nationality</label>
                                                        <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $userbio?->nationality ?? $studentData?->nationality ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Date of Birth</label>
                                                        <input type="date" name="dob" class="form-control"
                                                               value="{{ old('dob', $userbio?->dob ?? ($studentData?->dateofbirth ? \Carbon\Carbon::parse($studentData?->dateofbirth)->format('Y-m-d') : '')) }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label>Address</label>
                                                        <textarea name="address" class="form-control" rows="3">{{ old('address', $userbio?->address ?? $studentData?->home_address ?? '') }}</textarea>
                                                    </div>
                                                    <div class="col-12 text-end mt-4">
                                                        <button type="reset" class="btn btn-light me-2">Reset</button>
                                                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save Changes</button>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <!-- Student View-Only Personal Info -->
                                            <div class="row g-3">
                                                <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Personal Information</h5></div>
                                                <div class="col-md-6">
                                                    <label>First Name</label>
                                                    <input type="text" class="form-control" value="{{ $userbio?->firstname ?? $studentData?->firstname ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" value="{{ $userbio?->lastname ?? $studentData?->lastname ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Other Names</label>
                                                    <input type="text" class="form-control" value="{{ $userbio?->othernames ?? $studentData?->othername ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Phone Number</label>
                                                    <input type="text" class="form-control" value="{{ $userbio?->phone ?? $studentData?->phone_number ?? $user->phone_number ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Gender</label>
                                                    <input type="text" class="form-control" value="{{ ucfirst($userbio?->gender ?? $studentData?->gender ?? 'N/A') }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Nationality</label>
                                                    <input type="text" class="form-control" value="{{ $userbio?->nationality ?? $studentData?->nationality ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Date of Birth</label>
                                                    <input type="text" class="form-control" value="{{ $userbio?->dob ?? ($studentData?->dateofbirth ? \Carbon\Carbon::parse($studentData->dateofbirth)->format('d M Y') : 'N/A') }}" readonly>
                                                </div>
                                                <div class="col-12">
                                                    <label>Address</label>
                                                    <textarea class="form-control" rows="3" readonly>{{ $userbio?->address ?? $studentData?->home_address ?? 'N/A' }}</textarea>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                <!-- Employment Info (Staff Only - Editable) -->
                                @if($isStaff && !auth()->user()->hasRole('student'))
                                <div class="tab-pane" id="employmentInfo" role="tabpanel">
                                    <form action="{{ route('profile.update-employment-info') }}" method="POST">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Employment Information</h5></div>
                                            <div class="col-md-6"><label>Employment ID *</label><input type="text" name="employmentid" class="form-control" value="{{ old('employmentid', $staffInfo?->employmentid ?? '') }}" required></div>
                                            <div class="col-md-6"><label>Job Title *</label><input type="text" name="title" class="form-control" value="{{ old('title', $staffInfo?->title ?? '') }}" required></div>
                                            <div class="col-md-6"><label>Work Phone *</label><input type="text" name="phonenumber" class="form-control" value="{{ old('phonenumber', $staffInfo?->phonenumber ?? '') }}" required></div>
                                            <div class="col-md-6">
                                                <label>Marital Status *</label>
                                                <select name="maritalstatus" class="form-control" required>
                                                    <option value="">Select</option>
                                                    <option value="single" {{ old('maritalstatus', $staffInfo?->maritalstatus ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                                                    <option value="married" {{ old('maritalstatus', $staffInfo?->maritalstatus ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6"><label>Number of Children</label><input type="number" name="numberofchildren" class="form-control" value="{{ old('numberofchildren', $staffInfo?->numberofchildren ?? 0) }}"></div>
                                            <div class="col-md-6"><label>Spouse Phone</label><input type="text" name="spousenumber" class="form-control" value="{{ old('spousenumber', $staffInfo?->spousenumber ?? '') }}"></div>
                                            <div class="col-12"><label>Residential Address *</label><textarea name="address" class="form-control" rows="3" required>{{ old('address', $staffInfo?->address ?? '') }}</textarea></div>
                                            <div class="col-md-6"><label>State *</label><input type="text" name="state" class="form-control" value="{{ old('state', $staffInfo?->state ?? '') }}" required></div>
                                            <div class="col-md-6"><label>Local Government *</label><input type="text" name="local" class="form-control" value="{{ old('local', $staffInfo?->local ?? '') }}" required></div>
                                            <div class="col-12"><label>Religion *</label><input type="text" name="religion" class="form-control" value="{{ old('religion', $staffInfo?->religion ?? '') }}" required></div>
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-light me-2">Reset</button>
                                                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Update Employment Info</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @elseif($isStaff)
                                <!-- Staff View-Only Employment Info -->
                                <div class="tab-pane" id="employmentInfo" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Employment Information</h5></div>
                                        <div class="col-md-6"><label>Employment ID</label><input type="text" class="form-control" value="{{ $staffInfo?->employmentid ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Job Title</label><input type="text" class="form-control" value="{{ $staffInfo?->title ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Work Phone</label><input type="text" class="form-control" value="{{ $staffInfo?->phonenumber ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Marital Status</label><input type="text" class="form-control" value="{{ ucfirst($staffInfo?->maritalstatus ?? 'N/A') }}" readonly></div>
                                        <div class="col-md-6"><label>Number of Children</label><input type="text" class="form-control" value="{{ $staffInfo?->numberofchildren ?? '0' }}" readonly></div>
                                        <div class="col-md-6"><label>Spouse Phone</label><input type="text" class="form-control" value="{{ $staffInfo?->spousenumber ?? 'N/A' }}" readonly></div>
                                        <div class="col-12"><label>Residential Address</label><textarea class="form-control" rows="3" readonly>{{ $staffInfo?->address ?? 'N/A' }}</textarea></div>
                                        <div class="col-md-6"><label>State</label><input type="text" class="form-control" value="{{ $staffInfo?->state ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Local Government</label><input type="text" class="form-control" value="{{ $staffInfo?->local ?? 'N/A' }}" readonly></div>
                                        <div class="col-12"><label>Religion</label><input type="text" class="form-control" value="{{ $staffInfo?->religion ?? 'N/A' }}" readonly></div>
                                    </div>
                                </div>
                                @endif

                                <!-- Qualifications (Staff Only - Editable) -->
                                @if($isStaff && !auth()->user()->hasRole('student'))
                                <div class="tab-pane" id="qualifications" role="tabpanel">
                                    <div class="card mb-4 border">
                                        <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-add-circle-line me-2"></i>Add New Qualification</h5></div>
                                        <div class="card-body">
                                            <form action="{{ route('profile.add-qualification') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-md-6"><label>Institution *</label><input type="text" name="institution" class="form-control" required></div>
                                                    <div class="col-md-6"><label>Qualification *</label><input type="text" name="qualification" class="form-control" required></div>
                                                    <div class="col-md-6"><label>Field of Study *</label><input type="text" name="field_of_study" class="form-control" required></div>
                                                    <div class="col-md-6"><label>Year Obtained *</label><input type="number" name="year_obtained" class="form-control" min="1900" max="{{ date('Y')+1 }}" required></div>
                                                    <div class="col-md-6"><label>Certificate</label><input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                                                    <div class="col-md-6"><label>Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
                                                    <div class="col-12 text-end">
                                                        <button type="reset" class="btn btn-light me-2">Clear</button>
                                                        <button type="submit" class="btn btn-success"><i class="ri-add-line me-1"></i>Add</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="card border">
                                        <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-graduation-cap-line me-2"></i>Qualifications <span class="badge bg-primary ms-2">{{ $qualifications->count() }}</span></h5></div>
                                        <div class="card-body">
                                            @if($qualifications->count() > 0)
                                                <table class="table table-hover">
                                                    <thead><tr><th>#</th><th>Institution</th><th>Qualification</th><th>Field</th><th>Year</th><th>Certificate</th><th>Remarks</th><th>Action</th></tr></thead>
                                                    <tbody>
                                                        @foreach($qualifications as $i => $q)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td>{{ $q->institution }}</td>
                                                            <td>{{ $q->qualification }}</td>
                                                            <td>{{ $q->field_of_study }}</td>
                                                            <td>{{ $q->year_obtained }}</td>
                                                            <td>@if($q->certificate_file)<a href="{{ asset('storage/' . $q->certificate_file) }}" target="_blank">View</a>@else - @endif</td>
                                                            <td>{{ $q->remarks ?? '-' }}</td>
                                                            <td>
                                                                <form action="{{ route('profile.delete-qualification', $q->id) }}" method="POST" class="d-inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="ri-delete-bin-line"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p class="text-center text-muted py-4">No qualifications added.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @elseif($isStaff)
                                <!-- Staff View-Only Qualifications -->
                                <div class="tab-pane" id="qualifications" role="tabpanel">
                                    <div class="card border">
                                        <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-graduation-cap-line me-2"></i>Qualifications <span class="badge bg-primary ms-2">{{ $qualifications->count() }}</span></h5></div>
                                        <div class="card-body">
                                            @if($qualifications->count() > 0)
                                                <table class="table table-hover">
                                                    <thead><tr><th>#</th><th>Institution</th><th>Qualification</th><th>Field</th><th>Year</th><th>Certificate</th><th>Remarks</th></tr></thead>
                                                    <tbody>
                                                        @foreach($qualifications as $i => $q)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td>
                                                            <td>{{ $q->institution }}</td>
                                                            <td>{{ $q->qualification }}</td>
                                                            <td>{{ $q->field_of_study }}</td>
                                                            <td>{{ $q->year_obtained }}</td>
                                                            <td>@if($q->certificate_file)<a href="{{ asset('storage/' . $q->certificate_file) }}" target="_blank">View</a>@else - @endif</td>
                                                            <td>{{ $q->remarks ?? '-' }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p class="text-center text-muted py-4">No qualifications added.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Student Info (View-Only) -->
                                @if($isStudent && $studentData)
                                <div class="tab-pane" id="studentInfo" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Student Information</h5></div>
                                        <div class="col-md-6"><label>Admission Number</label><input type="text" class="form-control" value="{{ $studentData?->admissionNo ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Admission Date</label><input type="text" class="form-control" value="{{ $studentData?->admission_date ? \Carbon\Carbon::parse($studentData->admission_date)->format('d M Y') : 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Phone Number</label><input type="text" class="form-control" value="{{ $studentData?->phone_number ?? $user->phone_number ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Email</label><input type="text" class="form-control" value="{{ $user->email }}" readonly></div>
                                        <div class="col-md-6"><label>State</label><input type="text" class="form-control" value="{{ $studentData?->state ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Local Government</label><input type="text" class="form-control" value="{{ $studentData?->local ?? 'N/A' }}" readonly></div>
                                        <div class="col-12"><label>Home Address</label><textarea class="form-control" rows="3" readonly>{{ $studentData?->home_address ?? 'N/A' }}</textarea></div>
                                    </div>
                                </div>
                                @endif

                                <!-- Parent Info (View-Only) -->
                                @if($isStudent && $parentData)
                                <div class="tab-pane" id="parentInfo" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-12"><h5 class="mb-3 border-bottom pb-2">Parent/Guardian Information</h5></div>
                                        <div class="col-md-6"><label>Father's Name</label><input type="text" class="form-control" value="{{ $parentData?->father ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Mother's Name</label><input type="text" class="form-control" value="{{ $parentData?->mother ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Father's Phone</label><input type="text" class="form-control" value="{{ $parentData?->father_phone ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Mother's Phone</label><input type="text" class="form-control" value="{{ $parentData?->mother_phone ?? 'N/A' }}" readonly></div>
                                        <div class="col-md-6"><label>Father's Occupation</label><input type="text" class="form-control" value="{{ $parentData?->father_occupation ?? 'N/A' }}" readonly></div>
                                        <div class="col-12"><label>Home Address</label><textarea class="form-control" rows="3" readonly>{{ $parentData?->parent_address ?? 'N/A' }}</textarea></div>
                                    </div>
                                </div>
                                @endif

                                <!-- Academic Info (View-Only) -->
                                @if($isStudent)
                                <div class="tab-pane" id="academicInfo" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-book-open-line me-2"></i>Current Class</h5></div>
                                                <div class="card-body">
                                                    @if($currentClass?->schoolclass)
                                                        <div class="d-flex align-items-center mb-3">
                                                            <div class="avatar-sm"><div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16"><i class="ri-building-line"></i></div></div>
                                                            <div class="ms-3">
                                                                <h6>{{ $currentClass->schoolclass->schoolclass ?? 'Not Assigned' }}</h6>
                                                                <p class="text-muted mb-0">{{ $currentClass->schoolclass->armRelation?->schoolarm ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                        <ul class="list-unstyled">
                                                            <li><strong>Session:</strong> {{ $currentClass->session?->session ?? 'N/A' }}</li>
                                                            <li><strong>Term:</strong> {{ $currentClass->term?->term ?? 'N/A' }}</li>
                                                            <li><strong>Class Teacher:</strong> {{ $currentClass->schoolclass?->classteacher ?? 'Not Assigned' }}</li>
                                                        </ul>
                                                    @else
                                                        <p class="text-center text-muted py-4">No class assigned.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-history-line me-2"></i>Class History</h5></div>
                                                <div class="card-body">
                                                    @if($classHistory?->count() > 0)
                                                        <table class="table table-sm table-borderless">
                                                            <thead><tr><th>Class</th><th>Session</th><th>Term</th></tr></thead>
                                                            <tbody>
                                                                @foreach($classHistory as $h)
                                                                <tr>
                                                                    <td>{{ $h->schoolclass?->schoolclass ?? 'N/A' }}</td>
                                                                    <td>{{ $h->session?->session ?? 'N/A' }}</td>
                                                                    <td>{{ $h->term?->term ?? 'N/A' }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <p class="text-center text-muted py-4">No history available.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Security (View-Only for Students) -->
                                <div class="tab-pane" id="security" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-mail-line me-2"></i>Email Address</h5></div>
                                                <div class="card-body">
                                                    <p class="mb-0"><strong>Current Email:</strong> {{ $user->email }}</p>
                                                    @if(auth()->user()->hasRole('student'))
                                                        <small class="text-muted mt-2 d-block">Email changes must be requested through admin/staff.</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-lock-line me-2"></i>Password</h5></div>
                                                <div class="card-body">
                                                    <p class="mb-0"><strong>Password Status:</strong> Set</p>
                                                    @if(auth()->user()->hasRole('student'))
                                                        <small class="text-muted mt-2 d-block">Password changes must be requested through admin/staff.</small>
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
// Session messages
@if(session('success'))
    Swal.fire({icon:'success', title:'Success', text:'{{ session('success') }}', toast:true, position:'top-end', timer:3000, showConfirmButton: false});
@endif
@if(session('error'))
    Swal.fire({icon:'error', title:'Error', text:'{{ session('error') }}', toast:true, position:'top-end', timer:3000, showConfirmButton: false});
@endif

// No JS for avatar/email/password forms when student - already hidden
// Tab persistence (optional)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            localStorage.setItem('activeProfileTab', e.target.getAttribute('href'));
        });
    });

    const saved = localStorage.getItem('activeProfileTab');
    if (saved) {
        const tab = document.querySelector(`a[href="${saved}"]`);
        if (tab) {
            const bsTab = new bootstrap.Tab(tab);
            bsTab.show();
        }
    }
});
</script>
@endsection
