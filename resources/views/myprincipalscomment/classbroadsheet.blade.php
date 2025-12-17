@extends('layouts.master')

@section('content')
<style>
    .highlight-red { color: red !important; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; border-radius: 50%; }
    .table-centered th, .table-centered td { text-align: center; vertical-align: middle; }
    .form-select.teacher-comment-dropdown {
        width: 100%;
        min-width: 150px;
        padding-right: 40px;
        cursor: pointer;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        transition: all 0.2s ease;
    }
    .form-select.teacher-comment-dropdown:focus {
        background-color: #fff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .comment-cell { position: relative; }
    .comment-info-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: #0d6efd;
        z-index: 2;
        transition: color 0.2s ease;
    }
    .comment-info-icon:hover { color: #0056b3; }

    .auto-save-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99999;
        min-width: 250px;
    }

    .intelligent-comment-section {
        border-left: 4px solid #28a745;
        background-color: #f8fff8 !important;
        margin-bottom: 15px;
        border-radius: 8px;
        padding: 10px;
    }
    .intelligent-comment-preview {
        font-size: 0.9rem;
        line-height: 1.4;
        white-space: pre-line;
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin-top: 8px;
    }
    .intelligent-comment-text { color: #155724; font-weight: 500; }
    .intelligent-comment-badge { font-size: 0.75rem; padding: 2px 8px; margin-left: 8px; }
    .intelligent-option {
        background-color: #e8f5e8 !important;
        border-top: 2px solid #28a745 !important;
        font-weight: 600 !important;
        color: #155724 !important;
        margin-top: 5px;
    }

    .saved-comment-preview {
        background-color: #f8fff8 !important;
        border-left: 4px solid #28a745;
        border-radius: 6px;
        padding: 10px;
        font-size: 0.875rem;
        line-height: 1.5;
        white-space: pre-line;
        max-height: 120px;
        overflow-y: auto;
    }

    .grades-tooltip {
        position: fixed;
        background: white;
        border: 2px solid #667eea;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        width: 380px;
        max-height: 500px;
        overflow: hidden;
        z-index: 10050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }
    .grades-tooltip.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        animation: tooltipFadeIn 0.3s ease-out;
    }
    @keyframes tooltipFadeIn {
        from { opacity: 0; transform: translate(-50%, -48%) scale(0.95); }
        to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    .grades-tooltip.position-bottom { bottom: 15%; left: 50%; transform: translateX(-50%); }
    .grades-tooltip .tooltip-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 60px 20px 24px;
        font-weight: 700;
        font-size: 1.2rem;
        border-radius: 18px 18px 0 0;
        margin: -2px -2px 20px -2px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        position: relative;
        display: flex;
        align-items: center;
    }
    .grades-tooltip .tooltip-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        color: white;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .grades-tooltip .tooltip-body { padding: 0 20px 20px 20px; max-height: 380px; overflow-y: auto; }
    .grades-tooltip table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .grades-tooltip th { color: #6c757d; font-weight: 600; font-size: 0.9rem; padding: 12px 8px; border-bottom: 2px solid #e9ecef; }
    .grades-tooltip td { padding: 14px 12px; background: #f8f9fa; border-radius: 12px; font-size: 0.95rem; }
    .grade-badge { font-weight: 800; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; min-width: 50px; text-align: center; }
    
    /* Grade color classes */
    .grade-a { background-color: #28a745; color: white; }
    .grade-b { background-color: #17a2b8; color: white; }
    .grade-c { background-color: #6c757d; color: white; }
    .grade-d { background-color: #ffc107; color: black; }
    .grade-e { background-color: #fd7e14; color: white; }
    .grade-f { background-color: #dc3545; color: white; }
    
    /* Senior grade specific colors */
    .grade-a1, .grade-a { background-color: #28a745; color: white; }
    .grade-b2, .grade-b3, .grade-b { background-color: #17a2b8; color: white; }
    .grade-c4, .grade-c5, .grade-c6, .grade-c { background-color: #6c757d; color: white; }
    .grade-d7, .grade-d { background-color: #ffc107; color: black; }
    .grade-e8, .grade-e { background-color: #fd7e14; color: white; }
    .grade-f9, .grade-f { background-color: #dc3545; color: white; }

    .student-card { background: #fff; border: 1px solid #dee2e6; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
    .student-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #dee2e6; }
    .student-info { display: flex; align-items: center; gap: 12px; }
    .student-details h6 { margin: 0; font-size: 1rem; font-weight: 600; }
    .student-meta { font-size: 0.875rem; color: #6c757d; }
    .student-body { padding: 15px; }
    .subjects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-bottom: 20px; }
    .subject-item { text-align: center; padding: 12px 8px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef; }
    .subject-name { font-size: 0.75rem; font-weight: 600; color: #495057; margin-bottom: 5px; line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .subject-score { font-size: 1.3rem; font-weight: bold; margin: 5px 0; color: #212529; }
    .subject-grade { font-size: 0.8rem; font-weight: 700; padding: 3px 8px; border-radius: 12px; display: inline-block; min-width: 35px; }
    .performance-summary { background: #f8f9fa; border-radius: 10px; padding: 12px; margin-bottom: 15px; border: 1px solid #e9ecef; }
    .summary-title { font-weight: 600; font-size: 0.9rem; color: #495057; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
    .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; text-align: center; }
    .summary-item { padding: 8px 5px; background: white; border-radius: 8px; border: 1px solid #dee2e6; }
    .summary-label { font-size: 0.75rem; color: #6c757d; margin-bottom: 4px; }
    .summary-value { font-size: 1.1rem; font-weight: 700; color: #212529; }
    .comment-label-mobile { font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; color: #495057; display: flex; align-items: center; gap: 8px; }

    @media (min-width: 992px) {
        .desktop-table { display: block !important; }
        .mobile-cards { display: none !important; }
        .comment-info-icon { display: block !important; }
    }
    @media (max-width: 991.98px) {
        .desktop-table { display: none !important; }
        .mobile-cards { display: block !important; }
        .comment-info-icon { display: none !important; }
        .summary-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('myprincipalscomment.index') }}">My Assignments</a></li>
                                <li class="breadcrumb-item active">Broadsheet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($students->isNotEmpty())
                <form id="commentsForm" action="{{ route('myprincipalscomment.updateComments', [$schoolclassid, $sessionid, $termid]) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        Broadsheet: {{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }} - {{ $schoolterm }} {{ $schoolsession }}
                                        @if($isSenior)
                                            <span class="badge bg-warning ms-2">Senior Class</span>
                                        @else
                                            <span class="badge bg-info ms-2">Junior Class</span>
                                        @endif
                                    </h5>
                                    <div class="alert alert-info mt-2 mb-0">
                                        <i class="ri-bar-chart-line me-2"></i>
                                        <strong>Class Average:</strong> {{ $classAnalytics['average'] }} | 
                                        <strong>Students:</strong> {{ $classAnalytics['total_students'] }}
                                        @if($isSenior)
                                            <span class="ms-3"><strong>Grading:</strong> Senior (A1-F9)</span>
                                        @else
                                            <span class="ms-3"><strong>Grading:</strong> Junior (A-F)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="search-box mb-4">
                                        <input type="text" class="form-control" placeholder="Search students by name or admission number..." id="searchInput">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>

                                    <!-- Desktop Table -->
                                    <div class="desktop-table">
                                        <div class="table-responsive">
                                            <table class="table table-centered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>SN</th>
                                                        <th>Admission No</th>
                                                        <th>Student</th>
                                                        <th>Gender</th>
                                                        @foreach ($subjects as $subject)<th>{{ $subject }}</th>@endforeach
                                                        <th>Principal's Comment</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($students as $index => $student)
                                                        @php
                                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                            $imagePath = asset('storage/student_avatars/' . $picture);
                                                            $currentComment = $profiles[$student->id] ?? '';
                                                            $intelligentComment = $intelligentComments[$student->id] ?? '';
                                                            $hasWeakAdvice = !empty($studentGradeAnalysis[$student->id]['weak_subjects'] ?? []);
                                                            $analytics = $studentAnalytics[$student->id] ?? [];
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $student->admissionNo }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ $imagePath }}" class="avatar-sm me-3" alt="">
                                                                    <div>
                                                                        {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                        @if($currentComment)
                                                                            <small class="d-block text-success mt-1"><i class="ri-check-double-line"></i> Comment saved</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ $student->gender ?? 'N/A' }}</td>
                                                            @foreach ($subjects as $subject)
                                                                @php $score = $scores->where('student_id', $student->id)->where('subject_name', $subject)->first(); @endphp
                                                                <td class="{{ ($score && $score->total < 50) ? 'highlight-red' : '' }}">{{ $score?->total ?? '-' }}</td>
                                                            @endforeach
                                                            <td class="comment-cell">
                                                                @if($intelligentComment)
                                                                <div class="intelligent-comment-section mb-3">
                                                                    <small class="text-muted d-block mb-1">
                                                                        <i class="ri-lightbulb-line"></i> Grade summary comment
                                                                        @if($hasWeakAdvice)<span class="badge bg-warning intelligent-comment-badge">Includes improvement advice</span>@endif
                                                                    </small>
                                                                    <div class="intelligent-comment-preview">
                                                                        <div class="intelligent-comment-text">{{ $intelligentComment }}</div>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                @if($currentComment)
                                                                <div class="mb-3">
                                                                    <small class="text-success d-block mb-1"><i class="ri-chat-check-line"></i> Previously saved comment</small>
                                                                    <div class="saved-comment-preview">
                                                                        <small class="text-secondary">{{ $currentComment }}</small>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                        name="teacher_comments[{{ $student->id }}]"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-original-value="{{ $currentComment }}">
                                                                    <option value="">-- Select Comment --</option>

                                                                    @foreach ($standardPersonalizedComments[$student->id] ?? [] as $comment)
                                                                        <option value="{{ $comment }}" {{ $currentComment == $comment ? 'selected' : '' }}>
                                                                            {{ Str::limit($comment, 80, '...') }}
                                                                            @if(str_contains($comment, 'should work harder'))
                                                                                <span class="badge bg-warning ms-2">+ Advice</span>
                                                                            @endif
                                                                        </option>
                                                                    @endforeach

                                                                    @if(isset($intelligentComments[$student->id]) && !in_array($intelligentComments[$student->id], $standardPersonalizedComments[$student->id] ?? []))
                                                                        <option value="{{ $intelligentComments[$student->id] }}" class="intelligent-option" {{ $currentComment == $intelligentComments[$student->id] ? 'selected' : '' }}>
                                                                            💡 Use Grade Summary Comment
                                                                            @if($hasWeakAdvice)<span class="badge bg-warning ms-2">Improvement advice</span>@endif
                                                                        </option>
                                                                    @endif
                                                                </select>

                                                                <button type="button" class="comment-info-icon grades-trigger btn btn-link p-0"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-student-name="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}">
                                                                    <i class="ri-eye-line"></i>
                                                                </button>

                                                                <div class="grades-tooltip position-bottom" id="tooltip-{{ $student->id }}">
                                                                    <div class="tooltip-header">
                                                                        <span id="tooltip-title-{{ $student->id }}">Grades</span>
                                                                        <button type="button" class="tooltip-close"><i class="ri-close-line"></i></button>
                                                                    </div>
                                                                    <div class="tooltip-body">
                                                                        <div class="text-center mb-3 p-3 bg-light rounded">
                                                                            <div class="row">
                                                                                <div class="col-6"><strong>Total Score:</strong> {{ $analytics['total_score'] ?? 0 }}</div>
                                                                                <div class="col-6"><strong>Average:</strong> <span class="{{ ($analytics['average'] ?? 0) < 50 ? 'text-danger' : 'text-success' }}">{{ $analytics['average'] ?? 0 }}</span></div>
                                                                            </div>
                                                                            <div class="row mt-2">
                                                                                <div class="col-6"><strong>Subjects:</strong> {{ $analytics['subjects'] ?? 0 }}</div>
                                                                                <div class="col-6"><strong>Position:</strong> <strong class="text-primary">{{ $analytics['position_text'] ?? '-' }}</strong></div>
                                                                            </div>
                                                                            <div class="mt-3">
                                                                                <strong>Grades:</strong>
                                                                                A: {{ $analytics['grade_counts']['A'] ?? 0 }} |
                                                                                B: {{ $analytics['grade_counts']['B'] ?? 0 }} |
                                                                                C: {{ $analytics['grade_counts']['C'] ?? 0 }} |
                                                                                D/F: {{ ($analytics['grade_counts']['D'] ?? 0) + ($analytics['grade_counts']['F'] ?? 0) }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-center mt-3 pt-3 border-top">
                                                                            <small class="text-muted d-block mb-2">Class Average: <strong>{{ $classAnalytics['average'] }}</strong></small>
                                                                            @php
                                                                                $diff = ($analytics['average'] ?? 0) - $classAnalytics['average'];
                                                                                $above = $diff > 0.5;
                                                                                $below = $diff < -0.5;
                                                                            @endphp
                                                                            @if($above)
                                                                                <span class="text-success fw-bold"><i class="ri-arrow-up-line"></i> +{{ round(abs($diff), 1) }} above class average</span>
                                                                            @elseif($below)
                                                                                <span class="text-danger fw-bold"><i class="ri-arrow-down-line"></i> {{ round(abs($diff), 1) }} below class average</span>
                                                                            @else
                                                                                <span class="text-info fw-bold"><i class="ri-subtract-line"></i> At class average</span>
                                                                            @endif
                                                                        </div>
                                                                        <table>
                                                                            <thead><tr><th>Subject</th><th>Score</th><th>Grade</th></tr></thead>
                                                                            <tbody id="grades-body-{{ $student->id }}"></tbody>
                                                                        </table>
                                                                        <div class="text-center py-4 text-muted d-none" id="no-grades-{{ $student->id }}">No grades available</div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Mobile Cards -->
                                    <div class="mobile-cards">
                                        @foreach ($students as $index => $student)
                                            @php
                                                $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                $imagePath = asset('storage/student_avatars/' . $picture);
                                                $currentComment = $profiles[$student->id] ?? '';
                                                $intelligentComment = $intelligentComments[$student->id] ?? '';
                                                $hasWeakAdvice = !empty($studentGradeAnalysis[$student->id]['weak_subjects'] ?? []);
                                                $analytics = $studentAnalytics[$student->id] ?? [];
                                                $myAvg = $analytics['average'] ?? 0;
                                                $diff = $myAvg - $classAnalytics['average'];
                                                $above = $diff > 0.5;
                                                $below = $diff < -0.5;
                                            @endphp
                                            <div class="student-card">
                                                <div class="student-header">
                                                    <div class="student-info">
                                                        <img src="{{ $imagePath }}" class="avatar-sm" alt="">
                                                        <div class="student-details">
                                                            <h6>
                                                                {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                @if($currentComment)<span class="badge bg-success ms-2" style="font-size:0.7rem;">Commented</span>@endif
                                                            </h6>
                                                            <div class="student-meta">
                                                                SN: {{ $index + 1 }} | Admission: {{ $student->admissionNo }} | Gender: {{ $student->gender ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="student-body">
                                                    <div class="performance-summary">
                                                        <div class="summary-title">
                                                            <i class="ri-bar-chart-line"></i> Performance Analytics
                                                            @if($hasWeakAdvice)<span class="badge bg-warning ms-auto">Needs Improvement</span>@endif
                                                        </div>
                                                        <div class="summary-grid">
                                                            <div class="summary-item">
                                                                <div class="summary-label">My Average</div>
                                                                <div class="summary-value {{ $myAvg < 50 ? 'text-danger' : 'text-success' }}">{{ $myAvg }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Class Average</div>
                                                                <div class="summary-value fw-bold">{{ $classAnalytics['average'] }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Position</div>
                                                                <div class="summary-value text-primary fw-bold">{{ $analytics['position_text'] ?? '-' }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Total Score</div>
                                                                <div class="summary-value">{{ $analytics['total_score'] ?? 0 }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="text-center mt-3">
                                                            @if($above)
                                                                <div class="alert alert-success py-2 mb-0">
                                                                    <i class="ri-arrow-up-line"></i> <strong>Above class average</strong> (+{{ round(abs($diff), 1) }})
                                                                </div>
                                                            @elseif($below)
                                                                <div class="alert alert-danger py-2 mb-0">
                                                                    <i class="ri-arrow-down-line"></i> <strong>Below class average</strong> ({{ round(abs($diff), 1) }} behind)
                                                                </div>
                                                            @else
                                                                <div class="alert alert-info py-2 mb-0">
                                                                    <i class="ri-subtract-line"></i> <strong>At class average</strong>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div class="text-center mt-2">
                                                            <small class="text-muted">
                                                                Grades: A({{ $analytics['grade_counts']['A'] ?? 0 }}) B({{ $analytics['grade_counts']['B'] ?? 0 }}) C({{ $analytics['grade_counts']['C'] ?? 0 }}) D/F({{ ($analytics['grade_counts']['D'] ?? 0) + ($analytics['grade_counts']['F'] ?? 0) }})
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="subjects-grid">
                                                        @foreach ($subjects as $subject)
                                                            @php
                                                                $score = $scores->where('student_id', $student->id)->where('subject_name', $subject)->first();
                                                                $g = collect($studentGrades[$student->id] ?? [])->firstWhere('subject', $subject);
                                                            @endphp
                                                            <div class="subject-item">
                                                                <div class="subject-name">{{ $subject }}</div>
                                                                <div class="subject-score {{ ($score && $score->total < 50) ? 'highlight-red' : '' }}">{{ $score?->total ?? '-' }}</div>
                                                                @if($g)
                                                                    <div class="subject-grade grade-{{ strtolower($g['grade']) }}">
                                                                        {{ $g['grade'] }}
                                                                        @if($isSenior)
                                                                            <br><small style="font-size:0.7em;">({{ $g['grade_letter'] }})</small>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if($intelligentComment)
                                                    <div class="intelligent-comment-section mb-3">
                                                        <div class="comment-label-mobile"><i class="ri-lightbulb-line"></i> Grade Summary Comment</div>
                                                        <div class="intelligent-comment-preview">
                                                            <div class="intelligent-comment-text">{{ $intelligentComment }}</div>
                                                            @if($hasWeakAdvice)<small class="text-muted d-block mt-2"><i class="ri-alert-line"></i> Includes improvement advice</small>@endif
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($currentComment)
                                                    <div class="mb-3">
                                                        <div class="comment-label-mobile"><i class="ri-chat-check-line text-success"></i> Previously Saved Comment</div>
                                                        <div class="saved-comment-preview">
                                                            <small class="text-secondary">{{ $currentComment }}</small>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="comment-section-mobile">
                                                        <div class="comment-label-mobile"><i class="ri-chat-3-line"></i> Principal's Comment</div>
                                                        <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                name="teacher_comments[{{ $student->id }}]"
                                                                data-student-id="{{ $student->id }}"
                                                                data-original-value="{{ $currentComment }}">
                                                            <option value="">-- Select Comment --</option>

                                                            @foreach ($standardPersonalizedComments[$student->id] ?? [] as $comment)
                                                                <option value="{{ $comment }}" {{ $currentComment == $comment ? 'selected' : '' }}>
                                                                    {{ Str::limit($comment, 80, '...') }}
                                                                    @if(str_contains($comment, 'should work harder'))
                                                                        <span class="badge bg-warning ms-2">+ Advice</span>
                                                                    @endif
                                                                </option>
                                                            @endforeach

                                                            @if(isset($intelligentComments[$student->id]) && !in_array($intelligentComments[$student->id], $standardPersonalizedComments[$student->id] ?? []))
                                                                <option value="{{ $intelligentComments[$student->id] }}" class="intelligent-option" {{ $currentComment == $intelligentComments[$student->id] ? 'selected' : '' }}>
                                                                    💡 Use Grade Summary Comment
                                                                    @if($hasWeakAdvice)<span class="badge bg-warning ms-2">Improvement advice</span>@endif
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="alert alert-info text-center">No students enrolled in this class for the selected session and term.</div>
            @endif
        </div>
    </div>
</div>

<script>
window.studentGrades = @json($studentGrades);
let activeTooltip = null;

function showToast(message, type = 'info') {
    const existing = document.querySelector('.auto-save-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show`;
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-fill me-2 fs-5"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function closeAllTooltips() {
    document.querySelectorAll('.grades-tooltip.show').forEach(t => t.classList.remove('show'));
    activeTooltip = null;
    document.querySelectorAll('.grades-trigger.active').forEach(t => t.classList.remove('active'));
}

function showTooltip(tooltipId, studentId, studentName) {
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    closeAllTooltips();
    document.querySelector(`.grades-trigger[data-student-id="${studentId}"]`)?.classList.add('active');
    document.getElementById(`tooltip-title-${studentId}`).textContent = studentName + "'s Grades";

    const grades = window.studentGrades[studentId] || [];
    const tbody = document.getElementById(`grades-body-${studentId}`);
    const noGrades = document.getElementById(`no-grades-${studentId}`);
    tbody.innerHTML = '';

    if (grades.length === 0) {
        noGrades.classList.remove('d-none');
    } else {
        noGrades.classList.add('d-none');
        grades.forEach(g => {
            // Determine color based on grade letter
            let color = 'secondary';
            const gradeLetter = g.grade_letter || g.grade.charAt(0);
            
            if (gradeLetter === 'A') color = 'success';
            else if (gradeLetter === 'B') color = 'info';
            else if (gradeLetter === 'C') color = 'secondary';
            else if (gradeLetter === 'D') color = 'warning';
            else if (gradeLetter === 'E') color = 'warning';
            else if (gradeLetter === 'F') color = 'danger';
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${g.subject}</strong></td>
                <td class="text-center fw-bold ${g.score < 50 ? 'text-danger' : 'text-success'}">${g.score}</td>
                <td class="text-center">
                    <span class="badge bg-${color} grade-badge grade-${g.grade.toLowerCase()}">
                        ${g.grade}
                    </span>
                </td>`;
            tbody.appendChild(row);
        });
    }
    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function () {
        const studentId = this.dataset.studentId;
        const comment = this.value.trim();
        const original = this.dataset.originalValue || '';

        // Skip if no change
        if (comment === original) return;

        // Store original state for restoration
        const originalBorder = this.style.borderColor;
        const originalBg = this.style.backgroundColor;
        
        // Visual feedback
        this.style.borderColor = '#ffc107';
        this.style.backgroundColor = '#fff3cd';
        this.disabled = true;

        // Store original text and show saving state
        const option = this.selectedOptions[0];
        const originalText = option ? option.textContent : '';
        if (option) {
            option.textContent = 'Saving...';
            option.disabled = true;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append(`teacher_comments[${studentId}]`, comment);

        console.log('Attempting to save comment for student:', studentId, 'Comment:', comment);
        console.log('CSRF Token present:', !!document.querySelector('meta[name="csrf-token"]')?.content);
        
        // Make the request
        fetch('{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}', {
            method: 'POST',
            body: formData,
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Add this header explicitly
            }
        })
        .then(async response => {
            console.log('Response status:', response.status, response.statusText);
            
            if (!response.ok) {
                // Try to get error message from response
                let errorMsg = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    errorMsg = errorData.message || errorMsg;
                } catch (e) {
                    // If response is not JSON, try to get text
                    try {
                        const text = await response.text();
                        if (text) errorMsg += ` - ${text.substring(0, 100)}`;
                    } catch (e2) {
                        // Ignore if can't read text
                    }
                }
                throw new Error(errorMsg);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Save successful:', data);
            
            if (data.success) {
                // Update original value marker
                this.dataset.originalValue = comment;
                
                // Show success feedback
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#d1e7dd';
                
                // Show success toast
                showToast(data.message || 'Comment saved successfully!', 'success');
                
                // Restore normal state after delay
                setTimeout(() => {
                    this.style.borderColor = originalBorder;
                    this.style.backgroundColor = originalBg;
                    this.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Server returned error: ' + JSON.stringify(data));
            }
        })
        .catch(error => {
            console.error('Auto-save error details:', error);
            console.error('Full error object:', error);
            
            // Revert to original value on error
            this.value = original;
            
            // Show error feedback
            this.style.borderColor = '#dc3545';
            this.style.backgroundColor = '#f8d7da';
            
            // Show error toast with more specific message
            const errorMessage = error.message || 'Unknown error occurred';
            showToast('Error saving comment: ' + errorMessage, 'danger');
            
            // Restore normal state after delay
            setTimeout(() => {
                this.style.borderColor = originalBorder;
                this.style.backgroundColor = originalBg;
                this.disabled = false;
            }, 3000);
        })
        .finally(() => {
            // Always restore option text
            if (option) {
                option.textContent = originalText;
                option.disabled = false;
            }
        });
    });
});

if (window.innerWidth > 991) {
    document.querySelectorAll('.grades-trigger').forEach(trigger => {
        let hoverTimeout;
        trigger.addEventListener('mouseenter', function() {
            hoverTimeout = setTimeout(() => {
                showTooltip(`tooltip-${this.dataset.studentId}`, this.dataset.studentId, this.dataset.studentName);
            }, 300);
        });
        trigger.addEventListener('mouseleave', () => clearTimeout(hoverTimeout));
        trigger.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            const tid = `tooltip-${this.dataset.studentId}`;
            activeTooltip === tid ? closeAllTooltips() : showTooltip(tid, this.dataset.studentId, this.dataset.studentName);
        });
    });

    document.querySelectorAll('.tooltip-close').forEach(btn => btn.addEventListener('click', closeAllTooltips));
    document.addEventListener('keydown', e => e.key === 'Escape' && closeAllTooltips());
    document.addEventListener('click', e => {
        if (activeTooltip && !document.getElementById(activeTooltip)?.contains(e.target)) {
            const trigger = document.querySelector(`.grades-trigger[data-student-id="${activeTooltip.replace('tooltip-', '')}"]`);
            if (!trigger || !trigger.contains(e.target)) closeAllTooltips();
        }
    });
}

document.getElementById('searchInput')?.addEventListener('input', function() {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.desktop-table tbody tr, .mobile-cards .student-card').forEach(el => {
        el.style.display = term === '' || el.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});

document.getElementById('commentsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    btn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 
            'Accept': 'application/json', 
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(async response => {
        if (!response.ok) {
            let errorMsg = `HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMsg = errorData.message || errorMsg;
            } catch (e) {
                // Ignore
            }
            throw new Error(errorMsg);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('All comments saved successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Save failed');
        }
    })
    .catch(error => {
        console.error('Bulk save error:', error);
        showToast('Error saving comments: ' + error.message, 'danger');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.auto-save-comment').forEach(s => {
        s.dataset.originalValue = s.value;
    });
    
    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.warn('CSRF token not found in meta tag');
    }
});
</script>
@endsection