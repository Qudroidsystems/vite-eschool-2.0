<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] }} - {{ $metadata['session'] }} - {{ $metadata['term'] }}</title>
    <style>
        /* Basic reset and font setup */
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 10mm 0 0 0;
            padding: 0;
            text-align: center;
        }

        .student-section {
            width: 190mm;
            max-height: 287mm;
            page-break-after: always;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto;
            padding: 12px;
            position: relative;
            overflow: hidden;
            text-align: left;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                text-align: center;
            }
            
            .student-section {
                width: 190mm;
                max-height: 287mm;
                margin: 0 auto;
                padding: 10mm;
                page-break-after: always;
                text-align: left;
            }
            
            .student-section:last-child {
                page-break-after: avoid;
            }
        }

        .fraction {
            display: inline-block;
            font-family: Arial, sans-serif;
            font-size: 8px;
            text-align: center;
            font-weight: bold;
        }

        .fraction .numerator {
            border-bottom: 2px solid #333;
            padding: 0 3px;
            display: block;
        }

        .fraction .denominator {
            padding-top: 3px;
            display: block;
        }

        span.text-space-on-dots,
        span.text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-height: 14px;
            font-weight: bold;
            font-size: 12px;
        }

        span.text-space-on-dots {
            width: 250px;
        }

        span.text-dot-space2 {
            width: 150px;
        }

        .school-name2 {
            font-size: 22px;
            font-weight: 900;
            color: #000000;
            text-align: left;
            margin: 1px 0;
            line-height: 1.2;
        }

        .school-logo {
            width: 100px;
            height: 70px;
            border: 0px solid #1e40af;
            border-radius: 1px;
            overflow: hidden;
            text-align: center;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background: #1e40af;
            margin: 4px 0;
        }

        .header-divider2 {
            width: 100%;
            height: 1px;
            background: #64748b;
            margin: 2px 0;
        }

        .report-title {
            background: #111827;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            margin: 8px 0;
        }

        .header {
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
            table-layout: fixed;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-img {
            width: 100%;
            height: 100%;
            border-radius: 1px;
        }

        .student-info-section {
            margin-bottom: 4px;
        }

        .result-details {
            font-size: 10px;
            font-weight: 800;
            color: #000000;
        }

        .info-value {
            font-size: 11px;
            font-weight: 900;
            color: #000000;
        }

        .photo-frame {
            border: 3px solid #090909;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            padding: 2px;
            width: 80px;
            height: 100px;
            margin: 0 auto;
            text-align: center;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
        }

        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 6px 3px;
            text-align: center;
            font-size: 8px;
        }

        .result-table thead th.assessment-header {
            width: 25px;
            min-width: 25px;
            max-width: 30px;
            font-size: 7px;
            overflow: hidden;
            white-space: nowrap;
        }

        .result-table tbody tr {
            font-weight: 800;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 4px 3px;
            text-align: center;
            font-size: 10px;
            background: white;
            font-weight: 900;
        }

        .result-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .result-table tbody td.subject-name {
            text-align: left !important;
            font-weight: 600;
        }

        .highlight-red {
            color: #dc2626 !important;
            font-weight: 900;
        }

        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 6px;
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #050505;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 12px;
        }

        .remarks-table .text-space-on-dots {
            color: #000000;
            font-weight: bold;
        }

        .footer-section {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 6px;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        .student-info-table td {
            padding: 1px;
            vertical-align: top;
        }

        .footer-layout-table {
            width: 100%;
        }

        .footer-layout-table td {
            padding: 3px;
            text-align: center;
        }

        .info-row {
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .info-row .result-details {
            margin-right: 4px;
            display: inline-block;
            width: 120px;
        }

        .info-row.students-count {
            margin-top: 2px;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 900;
        }

        .text-primary {
            color: #02175e;
        }

        .student-section-inner {
            width: 100%;
            height: auto;
        }

        .powered-by {
            font-size: 12px;
            color: #000000;
            font-weight: 700;
            margin-top: 6px;
        }

        /* Promotion status styles */
        .promotion-status {
            font-weight: 900 !important;
            margin-left: 5px;
            font-size: 10px !important;
        }

        .remarks-table .promotion-status.promotion-promoted,
        .promotion-status.promotion-promoted {
            color: #1e40af !important;
            font-weight: 900 !important;
        }

        .remarks-table .promotion-status.promotion-repeat,
        .promotion-status.promotion-repeat {
            color: #dc2626 !important;
            font-weight: 900 !important;
        }

        .remarks-table .promotion-status.promotion-parents,
        .promotion-status.promotion-parents {
            color: #dc2626 !important;
            font-weight: 900 !important;
        }

        .remarks-table .promotion-status.promotion-default,
        .promotion-status.promotion-default {
            color: #6b7280 !important;
            font-weight: 900 !important;
        }
    </style>
</head>
<body>
    @foreach ($allStudentData as $index => $studentData)
        <div class="student-section">
            <div class="student-section-inner">
                <!-- Header Section -->
                <div class="header">
                    @php
                        $schoolInfo = $studentData['schoolInfo'] ?? null;
                        $student = $studentData['students'] && $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
                        // Get assessments from studentData (added in controller)
                        $assessments = $studentData['assessments'] ?? collect();
                        // Count assessments to determine colspan
                        $assessmentCount = $assessments->count();
                        $totalColspan = 11 + $assessmentCount; // Original 11 columns + dynamic assessments
                        
                        // Fix image paths for DomPDF - use file:// protocol for local files
                        $schoolLogoPath = $studentData['school_logo_path'] ?? null;
                        $studentImagePath = $studentData['student_image_path'] ?? null;
                        
                        // Convert to absolute file paths for DomPDF
                        if ($schoolLogoPath && file_exists($schoolLogoPath)) {
                            $schoolLogoUrl = 'file://' . str_replace('\\', '/', realpath($schoolLogoPath));
                        } else {
                            $schoolLogoUrl = 'file://' . str_replace('\\', '/', realpath(public_path('storage/school_logos/default.jpg')));
                        }
                        
                        if ($studentImagePath && file_exists($studentImagePath)) {
                            $studentImageUrl = 'file://' . str_replace('\\', '/', realpath($studentImagePath));
                        } else {
                            $studentImageUrl = 'file://' . str_replace('\\', '/', realpath(public_path('storage/student_avatars/unnamed.jpg')));
                        }
                    @endphp
                    <table class="header-table">
                        <tr>
                            <td width="25%">
                                <div class="school-logo">
                                    <img class="header-img" src="{{ $schoolLogoUrl }}" alt="School Logo">
                                </div>
                            </td>
                            <td width="50%">
                                <div class="info-row">
                                     <p class="school-name2">{{ $schoolInfo->school_name ?? 'QUDROID SYSTEMS' }}</p>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Motto:</span>
                                    <span class="info-value font-bold">{{ $schoolInfo->school_motto ?? 'NO INFO' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Address:</span>
                                    <span class="info-value font-bold">{{ $schoolInfo->school_address ?? 'NO INFO' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Phone:</span>
                                    <span class="info-value font-bold">{{ $schoolInfo->school_phone ?? 'NO INFO' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Email:</span>
                                    <span class="info-value font-bold">{{ $schoolInfo->school_email ?? 'NO INFO' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Website:</span>
                                    <span class="info-value font-bold">{{ $schoolInfo->school_website ?? 'NO INFO' }}</span>
                                </div>
                            </td>
                            <td width="25%">
                                <div class="photo-frame">
                                    @if ($studentData['students'] && $studentData['students']->isNotEmpty() && $student->picture)
                                        <img src="{{ $studentImageUrl }}" alt="{{ $student->fname ?? 'Student' }}'s picture">
                                    @else
                                        <img src="{{ 'file://' . str_replace('\\', '/', realpath(public_path('storage/student_avatars/unnamed.jpg'))) }}" alt="Default Photo">
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="header-divider"></div>
                    <div class="header-divider2"></div>
                    <div class="report-title">{{ strtoupper($metadata['term']) }} {{ strtoupper($metadata['session']) }} ACADEMIC SESSION TERMINAL PROGRESS REPORT</div>
                </div>

                <!-- Student Information Section -->
                <div class="student-info-section">
                    <table class="student-info-table">
                        <tr>
                            <td width="100%">
                                @if ($studentData['students'] && $studentData['students']->isNotEmpty())
                                    @php 
                                        $student = $studentData['students']->first();
                                        $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
                                    @endphp
                                    <table style="width: 100%; table-layout: fixed;">
                                        <tr>
                                            <td width="41%">
                                                <div class="info-row">
                                                    <span class="result-details">Name:</span>
                                                    <span class="info-value font-bold">{{ strtoupper($student->lastname ?? '') }} {{ $student->fname ?? '' }} {{ $student->othername ?? '' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Session:</span>
                                                    <span class="info-value font-bold">{{ $studentData['schoolsession']->session ?? 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Term:</span>
                                                    <span class="info-value font-bold">{{ $studentData['schoolterm']->term ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                            <td width="29%">
                                                <div class="info-row">
                                                    <span class="result-details">Class:</span>
                                                    <span class="info-value font-bold">{{ $studentData['schoolclass']->schoolclass ?? 'NO INFO' }} {{ $studentData['schoolclass']->arms->arm ?? '' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">DOB:</span>
                                                    <span class="info-value font-bold">
                                                        @php
                                                            $dob = $student->dateofbirth ?? null;
                                                            $formattedDob = 'NO INFO';
                                                            if ($dob) {
                                                                try {
                                                                    if (is_numeric($dob)) {
                                                                        $unixTimestamp = ($dob - 25569) * 86400;
                                                                        $formattedDob = date('jS F, Y', $unixTimestamp);
                                                                    } else {
                                                                        $formattedDob = \Carbon\Carbon::parse($dob)->format('jS F, Y');
                                                                    }
                                                                } catch (\Exception $e) {
                                                                    $formattedDob = $dob;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $formattedDob }}
                                                    </span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Adm No:</span>
                                                    <span class="info-value font-bold">{{ $student->admissionNo ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                            <td width="30%">
                                                <div class="info-row">
                                                    <span class="result-details">Sex:</span>
                                                    <span class="info-value font-bold">{{ $student->gender ?? 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Date School Opened:</span>
                                                    <span class="info-value font-bold">
                                                        @php
                                                            $dateSchoolOpened = $schoolInfo->date_school_opened ?? null;
                                                            $formattedDateSchoolOpened = 'NO INFO';
                                                            if ($dateSchoolOpened) {
                                                                try {
                                                                    $formattedDateSchoolOpened = \Carbon\Carbon::parse($dateSchoolOpened)->format('jS F, Y');
                                                                } catch (\Exception $e) {
                                                                    $formattedDateSchoolOpened = $dateSchoolOpened;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $formattedDateSchoolOpened }}
                                                    </span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Times School Opened:</span>
                                                    <span class="info-value font-bold">{{ $schoolInfo->no_of_times_school_opened ?? 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row students-count">
                                                    <span class="result-details">Students in Class:</span>
                                                    <span class="info-value font-bold">{{ $studentData['numberOfStudents'] ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div class="info-row">
                                        <span class="result-details">No student data available.</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Results Table - UPDATED FOR DYNAMIC ASSESSMENTS -->
                <div class="result-table">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Subjects</th>
                                @foreach ($assessments as $assessment)
                                    <th class="assessment-header" title="{{ $assessment->name }}">{{ substr($assessment->name, 0, 3) }}</th>
                                @endforeach
                                <th>d</th>
                                <th>e</th>
                                <th>f</th>
                                <th>g</th>
                                <th>h</th>
                                <th>i</th>
                                <th>j</th>
                                <th>k</th>
                            </tr>
                            <tr>
                                <th>S/N</th>
                                <th>Subjects</th>
                                @foreach ($assessments as $assessment)
                                    <th class="assessment-header" title="{{ $assessment->name }} (Max: {{ $assessment->max_score }})">
                                        {{ substr($assessment->name, 0, 3) }}
                                    </th>
                                @endforeach
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">Assess Total</div>
                                        <div class="denominator">{{ $assessmentCount }}</div>
                                    </div>
                                </th>
                                <th>Term Exams</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">d + e</div>
                                        <div class="denominator">2</div>
                                    </div>
                                </th>
                                <th>B/F</th>
                                <th>Cum (f/g)/2</th>
                                <th>Grade</th>
                                <th>PSN</th>
                                <th>Class Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['scores'] as $index => $score)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td>
                                    
                                    <!-- Dynamic Assessment Columns -->
                                    @foreach ($assessments as $assessment)
                                        @php
                                            // Get the score for this assessment
                                            $assessmentScore = 0;
                                            if (isset($score->assessment_scores)) {
                                                $found = $score->assessment_scores->firstWhere('assessment_id', $assessment->id);
                                                $assessmentScore = $found ? $found->score : 0;
                                            }
                                            // Check if score is low (less than 50%)
                                            $isLow = $assessmentScore < ($assessment->max_score * 0.5);
                                        @endphp
                                        <td class="@if ($isLow && is_numeric($assessmentScore)) highlight-red @endif">
                                            {{ $assessmentScore ?? '-' }}
                                        </td>
                                    @endforeach
                                    
                                    <!-- Total of Assessments (Column d) -->
                                    <td class="@if ($score->total < 50 && is_numeric($score->total)) highlight-red @endif">
                                        {{ $score->total ?? '-' }}
                                    </td>
                                    
                                    <!-- Exam Column (now using the last assessment if it's an exam) -->
                                    @php
                                        $examScore = 0;
                                        // Try to find an assessment named "Exam" or use the last one
                                        $examAssessment = $assessments->firstWhere('name', 'like', '%exam%') ?? $assessments->last();
                                        if ($examAssessment && isset($score->assessment_scores)) {
                                            $found = $score->assessment_scores->firstWhere('assessment_id', $examAssessment->id);
                                            $examScore = $found ? $found->score : 0;
                                        }
                                        $isLowExam = $examScore < ($examAssessment->max_score * 0.5);
                                    @endphp
                                    <td class="@if ($isLowExam && is_numeric($examScore)) highlight-red @endif">
                                        {{ $examScore ?? '-' }}
                                    </td>
                                    
                                    <!-- Total + Exam / 2 (Column f) -->
                                    <td class="@if ($score->total < 50 && is_numeric($score->total)) highlight-red @endif">
                                        {{ $score->total ?? '-' }}
                                    </td>
                                    
                                    <!-- Brought Forward (Column g) -->
                                    <td class="@if ($score->bf < 50 && is_numeric($score->bf)) highlight-red @endif">
                                        {{ $score->bf ?? '-' }}
                                    </td>
                                    
                                    <!-- Cumulative (Column h) -->
                                    <td class="@if ($score->cum < 50 && is_numeric($score->cum)) highlight-red @endif">
                                        {{ $score->cum ?? '-' }}
                                    </td>
                                    
                                    <!-- Grade (Column i) -->
                                    <td class="@if (in_array($score->grade ?? '', ['F', 'F9', 'E', 'E8'])) highlight-red @endif">
                                        {{ $score->grade ?? '-' }}
                                    </td>
                                    
                                    <!-- Position (Column j) -->
                                    <td>{{ $score->position ?? '-' }}</td>
                                    
                                    <!-- Class Average (Column k) -->
                                    <td>{{ $score->class_average ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $totalColspan }}" style="text-align: center;">No scores available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Remarks Section -->
                <table class="remarks-table">
                    <tbody>
                        <tr>
                            <td width="50%">
                                <div class="h6">Class Teacher's Remark Signature/Date</div>
                                <div>
                                    <span class="text-space-on-dots">{{ $profile ? ($profile->classteachercomment ?? 'NO INFO') : 'NO INFO' }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                                <div>
                                    <span class="text-space-on-dots">{{ $profile ? ($profile->guidancescomment ?? 'NO INFO') : 'NO INFO' }}</span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Principal's Remark & Promotion Status</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        {{ $profile ? ($profile->principalscomment ?? 'NO INFO') : 'NO INFO' }}
                                        @php
                                            $status = $studentData['promotionStatusValue'] ?? null;
                                            
                                            // Enhanced status classification with better string matching
                                            $statusUpper = strtoupper(trim($status ?? ''));
                                            $statusClass = 'promotion-default';
                                            
                                            // More comprehensive matching
                                            if (str_contains($statusUpper, 'PROMOTED') && !str_contains($statusUpper, 'TRIAL')) {
                                                $statusClass = 'promotion-promoted';
                                            } elseif (str_contains($statusUpper, 'TRIAL') || str_contains($statusUpper, 'PROMOTED ON TRIAL')) {
                                                $statusClass = 'promotion-repeat';
                                            } elseif (str_contains($statusUpper, 'REPEAT')) {
                                                $statusClass = 'promotion-repeat';
                                            } elseif (str_contains($statusUpper, 'PRINCIPAL') || str_contains($statusUpper, 'PARENTS')) {
                                                $statusClass = 'promotion-parents';
                                            }
                                            
                                            $statusText = $status ?? 'Not applicable for this term';
                                        @endphp
                                        <br>
                                        <span class="promotion-status {{ $statusClass }}">
                                            PROMOTION STATUS: {{ $statusText }}
                                        </span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer Section -->
                <div class="footer-section">
                    <table class="footer-layout-table">
                        <tr>
                            <td>
                                <span class="font-bold">This Result was issued on  </span>
                                <span class="text-dot-space2"> {{ now()->format('jS F, Y') }}</span>
                        
                                <span class="font-bold">and collected by</span>
                                <span class="">.......................................</span>
                            </td>
                            
                        </tr>
                        <tr>
                            <td>
                                <span class="font-bold text-primary">Next Term Begins:</span>
                                <span class="text-dot-space2">
                                    @php
                                        $nextTermBegins = $schoolInfo->date_next_term_begins ?? null;
                                        $formattedNextTermBegins = $nextTermBegins ? \Carbon\Carbon::parse($nextTermBegins)->format('jS F, Y') : '........................';
                                    @endphp
                                    {{ $formattedNextTermBegins }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="powered-by">Powered by Qudroid Systems | www.qudroid.co | +2349057522004</div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>