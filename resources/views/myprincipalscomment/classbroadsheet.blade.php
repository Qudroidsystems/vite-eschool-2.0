@extends('layouts.master')

@section('content')
<style>
    .highlight-red { color: red !important; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
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
    .comment-cell {
        position: relative;
    }
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
    .comment-info-icon:hover,
    .comment-info-icon:focus {
        color: #0056b3;
    }

    /* Toast Notification */
    .auto-save-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99999;
        min-width: 250px;
    }

    /* Intelligent Comment Styles */
    .intelligent-comment-section {
        border-left: 4px solid #28a745;
        background-color: #f8fff8 !important;
        margin-bottom: 15px;
        border-radius: 8px;
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
    
    .intelligent-comment-text {
        color: #155724;
        font-weight: 500;
    }
    
    .intelligent-comment-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        margin-left: 8px;
    }
    
    .intelligent-option {
        background-color: #e8f5e8 !important;
        border-top: 2px solid #28a745 !important;
        font-weight: 600 !important;
        color: #155724 !important;
        margin-top: 5px;
    }

    /* Floating Tooltip for Desktop */
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
        from {
            opacity: 0;
            transform: translate(-50%, -48%) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    .grades-tooltip.position-top {
        top: 15%;
        left: 50%;
        transform: translateX(-50%);
    }
    .grades-tooltip.position-bottom {
        bottom: 15%;
        left: 50%;
        transform: translateX(-50%);
    }

    .grades-tooltip .tooltip-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 60px 20px 24px;
        font-weight: 700;
        font-size: 1.2rem;
        text-align: left;
        border-radius: 18px 18px 0 0;
        margin: -2px -2px 20px -2px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        position: relative;
        min-height: 70px;
        display: flex;
        align-items: center;
    }

    .grades-tooltip .tooltip-header .tooltip-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
        padding: 0;
        margin: 0;
    }

    .grades-tooltip .tooltip-body {
        padding: 0 20px 20px 20px;
        max-height: 380px;
        overflow-y: auto;
    }

    .grades-tooltip table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .grades-tooltip th {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px 8px;
        border-bottom: 2px solid #e9ecef;
    }
    .grades-tooltip td {
        padding: 14px 12px;
        background: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .grades-tooltip .grade-badge {
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        min-width: 50px;
        display: inline-block;
        text-align: center;
    }

    /* Grade badge colors */
    .grade-a { background-color: #28a745; color: white; }
    .grade-b { background-color: #17a2b8; color: white; }
    .grade-c { background-color: #6c757d; color: white; }
    .grade-d { background-color: #ffc107; color: black; }
    .grade-e { background-color: #fd7e14; color: white; }
    .grade-f { background-color: #dc3545; color: white; }

    /* Mobile Card Styles */
    .student-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .student-header {
        background: #f8f9fa;
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
    }
    .student-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .student-details h6 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }
    .student-meta {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .student-body {
        padding: 15px;
    }
    
    /* Updated subjects grid for mobile with grades */
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }
    .subject-item {
        text-align: center;
        padding: 12px 8px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .subject-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        line-height: 1.2;
        height: 2.4em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .subject-score {
        font-size: 1.3rem;
        font-weight: bold;
        margin: 5px 0;
        color: #212529;
    }
    .subject-grade {
        font-size: 0.8rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-block;
        min-width: 35px;
    }
    
    /* Performance summary for mobile */
    .performance-summary {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
    }
    .summary-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #495057;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .summary-title i {
        color: #667eea;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        text-align: center;
    }
    .summary-item {
        padding: 8px 5px;
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .summary-label {
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 4px;
    }
    .summary-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
    }
    
    .comment-section-mobile {
        margin-top: 15px;
    }
    .comment-label-mobile {
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .comment-label-mobile i {
        color: #667eea;
    }

    /* Remove comment info icon on mobile */
    @media (max-width: 991px) {
        .desktop-table { display: none; }
        .mobile-cards { display: block; }
        .comment-info-icon { display: none; }
    }
    
    @media (min-width: 992px) {
        .mobile-cards { display: none; }
        .desktop-table { display: block; }
        
        .grades-tooltip {
            max-height: 550px;
        }
        
        .grades-tooltip .tooltip-body {
            max-height: 430px;
        }
    }
</style>

<div class="main-content class-broadsheet">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
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

            <!-- Flash Messages -->
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
                                        Broadsheet: {{ $schoolclass->schoolclass }} {{ $schoolclass->arm_name }} 
                                        - {{ $schoolterm }} {{ $schoolsession }}
                                    </h5>
                                    <div class="alert alert-info mt-2 mb-0">
                                        <i class="ri-lightbulb-line me-2"></i>
                                        <strong>Intelligent Comment Feature:</strong> The system now analyzes student grades and suggests personalized comments with student names and subject names. Look for the "💡 Use Intelligent Comment" option in the dropdown.
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Search Box -->
                                    <div class="search-box mb-4">
                                        <input type="text" class="form-control" placeholder="Search students by name or admission no..." id="searchInput">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>

                                    <!-- Desktop Table (with tooltips) -->
                                    <div class="desktop-table">
                                        <div class="table-responsive">
                                            <table class="table table-centered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>SN</th>
                                                        <th>Admission No</th>
                                                        <th>Student</th>
                                                        <th>Gender</th>
                                                        @foreach ($subjects as $subject)
                                                            <th>{{ $subject }}</th>
                                                        @endforeach
                                                        <th>Principal's Comment</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($students as $index => $student)
                                                        @php
                                                            $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                            $imagePath = asset('storage/student_avatars/' . $picture);
                                                            $studentGradesList = $studentGrades[$student->id] ?? [];
                                                            $intelligentComment = $intelligentComments[$student->id] ?? '';
                                                            $hasWeakSubjects = !empty($studentGradeAnalysis[$student->id]['weak_subjects'] ?? []);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $student->admissionNo }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ $imagePath }}" class="rounded-circle avatar-sm me-3" alt="">
                                                                    {{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}
                                                                </div>
                                                            </td>
                                                            <td>{{ $student->gender ?? 'N/A' }}</td>
                                                            @foreach ($subjects as $subject)
                                                                @php
                                                                    $score = $scores->where('student_id', $student->id)
                                                                                   ->where('subject_name', $subject)
                                                                                   ->first();
                                                                    $grade = '';
                                                                    foreach ($studentGradesList as $g) {
                                                                        if ($g['subject'] == $subject) {
                                                                            $grade = $g['grade'];
                                                                            break;
                                                                        }
                                                                    }
                                                                @endphp
                                                                <td class="{{ ($score && $score->total < 50) ? 'highlight-red' : '' }}">
                                                                    {{ $score?->total ?? '-' }}
                                                                </td>
                                                            @endforeach
                                                            <td class="comment-cell">
                                                                <!-- Intelligent Comment Preview -->
                                                                @if($intelligentComment)
                                                                <div class="intelligent-comment-section">
                                                                    <small class="text-muted d-block mb-1">
                                                                        <i class="ri-lightbulb-line"></i> Suggested personalized comment:
                                                                        @if($hasWeakSubjects)
                                                                        <span class="badge bg-warning intelligent-comment-badge">Includes weak subjects</span>
                                                                        @endif
                                                                    </small>
                                                                    <div class="intelligent-comment-preview mb-2">
                                                                        <div class="intelligent-comment-text">{{ $intelligentComment }}</div>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                        name="teacher_comments[{{ $student->id }}]"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-original-value="{{ $profiles[$student->id] ?? '' }}"
                                                                        data-intelligent-comment="{{ $intelligentComment }}">
                                                                    <option value="">-- Select Comment --</option>
                                                                    <option value="Excellent result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Excellent result, keep it up!' ? 'selected' : '' }}>
                                                                        Excellent result, keep it up!
                                                                    </option>
                                                                    <option value="A very good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'A very good result, keep it up!' ? 'selected' : '' }}>
                                                                        A very good result, keep it up!
                                                                    </option>
                                                                    <option value="Good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Good result, keep it up!' ? 'selected' : '' }}>
                                                                        Good result, keep it up!
                                                                    </option>
                                                                    <option value="Average result, there's still room for improvement next term." {{ ($profiles[$student->id] ?? '') == "Average result, there's still room for improvement next term." ? 'selected' : '' }}>
                                                                        Average result, there's still room for improvement next term.
                                                                    </option>
                                                                    <option value="You can do better next term." {{ ($profiles[$student->id] ?? '') == 'You can do better next term.' ? 'selected' : '' }}>
                                                                        You can do better next term.
                                                                    </option>
                                                                    <option value="You need to sit up and be serious." {{ ($profiles[$student->id] ?? '') == 'You need to sit up and be serious.' ? 'selected' : '' }}>
                                                                        You need to sit up and be serious.
                                                                    </option>
                                                                    <option value="Wake up and be serious." {{ ($profiles[$student->id] ?? '') == 'Wake up and be serious.' ? 'selected' : '' }}>
                                                                        Wake up and be serious.
                                                                    </option>
                                                                    @if($intelligentComment)
                                                                    <option value="{{ $intelligentComment }}" class="intelligent-option">
                                                                        💡 Use Personalized Intelligent Comment
                                                                        @if($hasWeakSubjects)
                                                                        <span class="badge bg-warning ms-2">Includes weak subjects</span>
                                                                        @endif
                                                                    </option>
                                                                    @endif
                                                                </select>

                                                                <button type="button"
                                                                        class="comment-info-icon grades-trigger btn btn-link p-0"
                                                                        data-student-id="{{ $student->id }}"
                                                                        data-student-name="{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}">
                                                                    <i class="ri-eye-line" aria-hidden="true"></i>
                                                                </button>

                                                                <!-- Floating Tooltip for Desktop -->
                                                                <div class="grades-tooltip position-bottom" id="tooltip-{{ $student->id }}">
                                                                    <div class="tooltip-header" id="header-{{ $student->id }}">
                                                                        <span id="tooltip-title-{{ $student->id }}">Grades</span>
                                                                        <button type="button" class="tooltip-close" aria-label="Close">
                                                                            <i class="ri-close-line"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div class="tooltip-body">
                                                                        <table>
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Subject</th>
                                                                                    <th>Score</th>
                                                                                    <th>Grade</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="grades-body-{{ $student->id }}"></tbody>
                                                                        </table>
                                                                        <div class="text-center py-4 text-muted d-none" id="no-grades-{{ $student->id }}">
                                                                            No grades available
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Mobile Card View (with grades displayed) -->
                                    <div class="mobile-cards">
                                        @foreach ($students as $index => $student)
                                            @php
                                                $picture = $student->picture ? basename($student->picture) : 'unnamed.jpg';
                                                $imagePath = asset('storage/student_avatars/' . $picture);
                                                $studentGradesList = $studentGrades[$student->id] ?? [];
                                                $intelligentComment = $intelligentComments[$student->id] ?? '';
                                                $hasWeakSubjects = !empty($studentGradeAnalysis[$student->id]['weak_subjects'] ?? []);
                                                
                                                // Calculate performance summary
                                                $totalSubjects = count($subjects);
                                                $subjectsWithScores = 0;
                                                $totalScore = 0;
                                                $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
                                                
                                                foreach ($subjects as $subject) {
                                                    $score = $scores->where('student_id', $student->id)
                                                                   ->where('subject_name', $subject)
                                                                   ->first();
                                                    if ($score) {
                                                        $subjectsWithScores++;
                                                        $totalScore += $score->total;
                                                    }
                                                }
                                                
                                                foreach ($studentGradesList as $grade) {
                                                    $firstLetter = strtoupper(substr($grade['grade_letter'] ?? $grade['grade'], 0, 1));
                                                    if (in_array($firstLetter, ['A', 'B', 'C', 'D', 'E', 'F'])) {
                                                        $gradeCounts[$firstLetter]++;
                                                    }
                                                }
                                                
                                                $averageScore = $subjectsWithScores > 0 ? round($totalScore / $subjectsWithScores, 1) : 0;
                                            @endphp
                                            <div class="student-card">
                                                <div class="student-header">
                                                    <div class="student-info">
                                                        <img src="{{ $imagePath }}" class="rounded-circle avatar-sm" alt="">
                                                        <div class="student-details">
                                                            <h6>{{ $student->lastname }} {{ $student->firstname }} {{ $student->othername }}</h6>
                                                            <div class="student-meta">
                                                                <strong>SN:</strong> {{ $index + 1 }} | 
                                                                <strong>Admission:</strong> {{ $student->admissionNo }} | 
                                                                <strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="student-body">
                                                    <!-- Performance Summary -->
                                                    <div class="performance-summary">
                                                        <div class="summary-title">
                                                            <i class="ri-bar-chart-line"></i>
                                                            Performance Summary
                                                            @if($hasWeakSubjects)
                                                            <span class="badge bg-warning ms-auto">Has weak subjects</span>
                                                            @endif
                                                        </div>
                                                        <div class="summary-grid">
                                                            <div class="summary-item">
                                                                <div class="summary-label">Avg. Score</div>
                                                                <div class="summary-value {{ $averageScore < 50 ? 'text-danger' : 'text-success' }}">
                                                                    {{ $averageScore }}
                                                                </div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Subjects</div>
                                                                <div class="summary-value">{{ $subjectsWithScores }}/{{ $totalSubjects }}</div>
                                                            </div>
                                                            <div class="summary-item">
                                                                <div class="summary-label">Best Grade</div>
                                                                <div class="summary-value">
                                                                    @php
                                                                        $bestGrade = 'N/A';
                                                                        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade) {
                                                                            if ($gradeCounts[$grade] > 0) {
                                                                                $bestGrade = $grade;
                                                                                break;
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <span class="subject-grade grade-{{ strtolower($bestGrade) }}">{{ $bestGrade }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Subjects with Scores and Grades -->
                                                    <div class="subjects-grid">
                                                        @foreach ($subjects as $subject)
                                                            @php
                                                                $score = $scores->where('student_id', $student->id)
                                                                               ->where('subject_name', $subject)
                                                                               ->first();
                                                                $grade = '';
                                                                $gradeClass = '';
                                                                
                                                                foreach ($studentGradesList as $g) {
                                                                    if ($g['subject'] == $subject) {
                                                                        $grade = $g['grade'];
                                                                        $firstLetter = strtoupper(substr($g['grade_letter'] ?? $g['grade'], 0, 1));
                                                                        $gradeClass = 'grade-' . strtolower($firstLetter);
                                                                        break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="subject-item">
                                                                <div class="subject-name">{{ $subject }}</div>
                                                                <div class="subject-score {{ ($score && $score->total < 50) ? 'highlight-red' : '' }}">
                                                                    {{ $score?->total ?? '-' }}
                                                                </div>
                                                                @if($grade)
                                                                    <div class="subject-grade {{ $gradeClass }}">{{ $grade }}</div>
                                                                @else
                                                                    <div class="subject-grade" style="visibility: hidden;">-</div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <!-- Intelligent Comment Preview for Mobile -->
                                                    @if($intelligentComment)
                                                    <div class="intelligent-comment-section">
                                                        <div class="comment-label-mobile">
                                                            <i class="ri-lightbulb-line"></i>
                                                            Personalized Comment Suggestion
                                                        </div>
                                                        <div class="intelligent-comment-preview mb-3">
                                                            <div class="intelligent-comment-text">{{ $intelligentComment }}</div>
                                                            @if($hasWeakSubjects)
                                                            <small class="text-muted d-block mt-2">
                                                                <i class="ri-alert-line"></i> Includes specific advice for weak subjects
                                                            </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <!-- Principal's Comment Section -->
                                                    <div class="comment-section-mobile">
                                                        <div class="comment-label-mobile">
                                                            <i class="ri-chat-3-line"></i>
                                                            Principal's Comment
                                                        </div>
                                                        <div class="position-relative">
                                                            <select class="form-select teacher-comment-dropdown auto-save-comment"
                                                                    name="teacher_comments[{{ $student->id }}]"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-original-value="{{ $profiles[$student->id] ?? '' }}"
                                                                    data-intelligent-comment="{{ $intelligentComment }}">
                                                                <option value="">-- Select Comment --</option>
                                                                <option value="Excellent result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Excellent result, keep it up!' ? 'selected' : '' }}>
                                                                    Excellent result, keep it up!
                                                                </option>
                                                                <option value="A very good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'A very good result, keep it up!' ? 'selected' : '' }}>
                                                                    A very good result, keep it up!
                                                                </option>
                                                                <option value="Good result, keep it up!" {{ ($profiles[$student->id] ?? '') == 'Good result, keep it up!' ? 'selected' : '' }}>
                                                                    Good result, keep it up!
                                                                </option>
                                                                <option value="Average result, there's still room for improvement next term." {{ ($profiles[$student->id] ?? '') == "Average result, there's still room for improvement next term." ? 'selected' : '' }}>
                                                                    Average result, there's still room for improvement next term.
                                                                </option>
                                                                <option value="You can do better next term." {{ ($profiles[$student->id] ?? '') == 'You can do better next term.' ? 'selected' : '' }}>
                                                                    You can do better next term.
                                                                </option>
                                                                <option value="You need to sit up and be serious." {{ ($profiles[$student->id] ?? '') == 'You need to sit up and be serious.' ? 'selected' : '' }}>
                                                                    You need to sit up and be serious.
                                                                </option>
                                                                <option value="Wake up and be serious." {{ ($profiles[$student->id] ?? '') == 'Wake up and be serious.' ? 'selected' : '' }}>
                                                                    Wake up and be serious.
                                                                </option>
                                                                @if($intelligentComment)
                                                                <option value="{{ $intelligentComment }}" class="intelligent-option">
                                                                    💡 Use Personalized Intelligent Comment
                                                                    @if($hasWeakSubjects)
                                                                    <span class="badge bg-warning ms-2">Includes weak subjects</span>
                                                                    @endif
                                                                </option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">Save All Comments</button>
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
// Grades data
window.studentGrades = @json($studentGrades);
let activeTooltip = null;

// Toast notification function
function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.auto-save-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `auto-save-toast alert alert-${type} alert-dismissible fade show`;
    toast.style.zIndex = '99999';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-fill me-2 fs-5"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Function to close all tooltips (desktop only)
function closeAllTooltips() {
    document.querySelectorAll('.grades-tooltip.show').forEach(tooltip => {
        tooltip.classList.remove('show');
    });
    activeTooltip = null;
    
    // Remove any active states
    document.querySelectorAll('.grades-trigger.active').forEach(trigger => {
        trigger.classList.remove('active');
    });
}

// Function to show a specific tooltip (desktop only)
function showTooltip(tooltipId, studentId, studentName) {
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;
    
    // Close any open tooltip first
    closeAllTooltips();
    
    // Mark the trigger as active
    const trigger = document.querySelector(`.grades-trigger[data-student-id="${studentId}"]`);
    if (trigger) {
        trigger.classList.add('active');
    }
    
    // Set content
    const title = document.getElementById(`tooltip-title-${studentId}`);
    if (title) {
        title.textContent = studentName + "'s Grades";
    }
    
    const grades = window.studentGrades[studentId] || [];
    const tbody = document.getElementById(`grades-body-${studentId}`);
    const noGrades = document.getElementById(`no-grades-${studentId}`);
    
    tbody.innerHTML = '';
    
    if (grades.length === 0) {
        noGrades.classList.remove('d-none');
    } else {
        noGrades.classList.add('d-none');
        grades.forEach(g => {
            const row = document.createElement('tr');
            const gradeColor = g.grade[0] === 'A' ? 'success' :
                               g.grade[0] === 'B' || g.grade === 'C' ? 'info' :
                               g.grade[0] === 'D' || g.grade[0] === 'E' ? 'warning' : 'danger';

            row.innerHTML = `
                <td><strong>${g.subject}</strong></td>
                <td class="text-center fw-bold ${g.score < 50 ? 'text-danger' : 'text-success'}">${g.score}</td>
                <td class="text-center">
                    <span class="badge bg-${gradeColor} grade-badge">${g.grade}</span>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Show the tooltip
    tooltip.classList.add('show');
    activeTooltip = tooltipId;
}

// Auto-save on dropdown change with intelligent comment support
document.querySelectorAll('.auto-save-comment').forEach(select => {
    select.addEventListener('change', function () {
        const studentId = this.getAttribute('data-student-id');
        const comment = this.value;
        const originalValue = this.getAttribute('data-original-value');
        const intelligentComment = this.getAttribute('data-intelligent-comment');
        
        // If user selected the intelligent comment option, use the actual comment
        let finalComment = comment;
        if (comment === intelligentComment) {
            // This is the intelligent comment
        }
        
        if (finalComment === originalValue) {
            return;
        }

        const originalBorderColor = this.style.borderColor;
        const originalBackgroundColor = this.style.backgroundColor;
        this.style.borderColor = '#ffc107';
        this.style.backgroundColor = '#fff3cd';
        this.disabled = true;
        
        const selectedOption = this.options[this.selectedIndex];
        const originalText = selectedOption ? selectedOption.text : '';
        
        if (selectedOption) {
            selectedOption.text = 'Saving...';
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('teacher_comments[' + studentId + ']', finalComment);

        const saveUrl = '{{ route("myprincipalscomment.updateComments", [$schoolclassid, $sessionid, $termid]) }}';
        
        fetch(saveUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text().then(text => {
                    if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                        throw new Error('Server returned HTML instead of JSON');
                    }
                    return { success: true, message: 'Comment saved' };
                });
            }
        })
        .then(data => {
            if (data.success) {
                this.setAttribute('data-original-value', finalComment);
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#d1e7dd';
                showToast(data.message || 'Comment saved successfully!', 'success');
                
                if (selectedOption) {
                    selectedOption.text = originalText;
                }
                
                setTimeout(() => {
                    this.style.borderColor = originalBorderColor;
                    this.style.backgroundColor = originalBackgroundColor;
                    this.disabled = false;
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to save comment');
            }
        })
        .catch(err => {
            console.error('Auto-save error:', err);
            
            this.value = originalValue;
            this.style.borderColor = '#dc3545';
            this.style.backgroundColor = '#f8d7da';
            this.disabled = false;
            
            if (selectedOption) {
                selectedOption.text = originalText;
            }
            
            let errorMsg = 'Error saving comment';
            if (err.message.includes('403')) {
                errorMsg = 'Unauthorized: You are not assigned to this class';
            } else if (err.message.includes('419')) {
                errorMsg = 'Session expired. Please refresh the page.';
            } else if (err.message.includes('500')) {
                errorMsg = 'Server error. Please try again.';
            } else {
                errorMsg = err.message;
            }
            
            showToast(errorMsg, 'error');
            
            setTimeout(() => {
                this.style.borderColor = originalBorderColor;
                this.style.backgroundColor = originalBackgroundColor;
            }, 3000);
        });
    });
});

// Desktop tooltip functionality
if (window.innerWidth > 991) {
    // Tooltip click for desktop
    document.querySelectorAll('.grades-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const studentId = this.getAttribute('data-student-id');
            const studentName = this.getAttribute('data-student-name');
            const tooltipId = `tooltip-${studentId}`;
            
            // If clicking on the same tooltip that's already open, close it
            if (activeTooltip === tooltipId) {
                closeAllTooltips();
            } else {
                showTooltip(tooltipId, studentId, studentName);
            }
        });
    });
    
    // Close tooltips when clicking close button
    document.querySelectorAll('.tooltip-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeAllTooltips();
        });
    });
    
    // Close tooltips with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllTooltips();
        }
    });
    
    // Close tooltips when clicking outside
    document.addEventListener('click', function(e) {
        if (activeTooltip) {
            const tooltip = document.getElementById(activeTooltip);
            const trigger = document.querySelector(`.grades-trigger[data-student-id="${activeTooltip.replace('tooltip-', '')}"]`);
            
            if (tooltip && !tooltip.contains(e.target) && (!trigger || !trigger.contains(e.target))) {
                closeAllTooltips();
            }
        }
    });
    
    // Desktop hover behavior
    document.querySelectorAll('.grades-trigger').forEach(trigger => {
        let hoverTimeout;
        
        trigger.addEventListener('mouseenter', function() {
            if (!activeTooltip) {
                hoverTimeout = setTimeout(() => {
                    const studentId = this.getAttribute('data-student-id');
                    const studentName = this.getAttribute('data-student-name');
                    const tooltipId = `tooltip-${studentId}`;
                    
                    if (!activeTooltip) {
                        showTooltip(tooltipId, studentId, studentName);
                    }
                }, 300);
            }
        });
        
        trigger.addEventListener('mouseleave', function() {
            clearTimeout(hoverTimeout);
            
            if (activeTooltip && !this.classList.contains('active')) {
                setTimeout(() => {
                    const tooltip = document.getElementById(activeTooltip);
                    if (tooltip && !tooltip.matches(':hover')) {
                        closeAllTooltips();
                    }
                }, 100);
            }
        });
    });
}

// Search functionality
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchInput');
    if (input) {
        input.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();

            // Desktop
            document.querySelectorAll('.desktop-table tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = term === '' || text.includes(term) ? '' : 'none';
            });

            // Mobile
            document.querySelectorAll('.mobile-cards .student-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = term === '' || text.includes(term) ? '' : 'none';
            });
        });
    }
});

// Form submission handler
document.getElementById('commentsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                    return { success: true, message: 'All comments saved successfully!' };
                }
                return { success: true, message: 'Saved' };
            });
        }
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'All comments saved successfully!', 'success');
            
            document.querySelectorAll('.auto-save-comment').forEach(select => {
                select.setAttribute('data-original-value', select.value);
                select.style.borderColor = '#28a745';
                select.style.backgroundColor = '#d1e7dd';
                setTimeout(() => {
                    select.style.borderColor = '';
                    select.style.backgroundColor = '';
                }, 2000);
            });
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to save comments');
        }
    })
    .catch(err => {
        console.error('Form save error:', err);
        
        let errorMsg = 'Error saving comments';
        if (err.message.includes('403')) {
            errorMsg = 'Unauthorized: You are not assigned to this class';
        } else if (err.message.includes('419')) {
            errorMsg = 'Session expired. Please refresh the page and try again.';
        } else if (err.message.includes('500')) {
            errorMsg = 'Server error. Please try again.';
        } else {
            errorMsg = err.message;
        }
        
        showToast(errorMsg, 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('commentsForm')?.dispatchEvent(new Event('submit'));
    }
});

// Initialize original values on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.auto-save-comment').forEach(select => {
        select.setAttribute('data-original-value', select.value);
    });
});
</script>
@endsection