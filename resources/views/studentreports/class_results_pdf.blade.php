<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] }} - {{ $metadata['session'] }} - {{ $metadata['term'] }}</title>
    <style>
        /* Basic reset */
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
            text-align: left;
        }

        .student-section:last-child {
            page-break-after: avoid;
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

        .text-space-on-dots,
        .text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-height: 14px;
            font-weight: bold;
            font-size: 12px;
        }

        .text-space-on-dots {
            width: 250px;
        }

        .text-dot-space2 {
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
            text-align: center;
            overflow: hidden;
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
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-img {
            width: 100px;
            height: 70px;
            display: block;
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
            background: white;
            padding: 2px;
            width: 80px;
            height: 100px;
            margin: 0 auto;
            text-align: center;
            overflow: hidden;
        }

        .photo-frame img {
            width: 80px;
            height: 100px;
            display: block;
        }

        .result-table {
            margin-bottom: 8px;
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
            font-size: 7px;
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
            text-align: left;
            font-weight: 600;
        }

        .highlight-red {
            color: #dc2626;
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

        .footer-section {
            background: #f1f5f9;
            padding: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 6px;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 4px;
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
        }

        .powered-by {
            font-size: 12px;
            color: #000000;
            font-weight: 700;
            margin-top: 6px;
        }

        /* Promotion status styles */
        .promotion-status {
            font-weight: 900;
            margin-left: 5px;
            font-size: 10px;
        }

        .promotion-promoted {
            color: #1e40af;
            font-weight: 900;
        }

        .promotion-repeat {
            color: #dc2626;
            font-weight: 900;
        }

        .promotion-parents {
            color: #dc2626;
            font-weight: 900;
        }

        .promotion-default {
            color: #6b7280;
            font-weight: 900;
        }

        /* Column width classes - simple widths */
        .col-sn { width: 30px; }
        .col-admissionno { width: 80px; }
        .col-name { width: 150px; }
        .col-assessment { width: 40px; }
        .col-total { width: 50px; }
        .col-bf { width: 40px; }
        .col-cum { width: 50px; }
        .col-grade { width: 40px; }
        .col-position { width: 60px; }
        .col-class-average { width: 60px; }
        .col-num-subjects { width: 60px; }
        .col-total-grade-points { width: 60px; }
        .col-gpa { width: 50px; }
        .col-calculated-gpa { width: 60px; }
        .col-gpa-grade { width: 60px; }
        .col-cgpa { width: 50px; }
        .col-compulsory { width: 60px; }
        .col-vetted { width: 80px; }
        
        /* For printing */
        @media print {
            .student-section {
                width: 190mm;
                max-height: 287mm;
                margin: 0 auto;
                padding: 10mm;
                page-break-after: always;
            }
            
            .student-section:last-child {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        $selectedColumns = $metadata['selected_columns'] ?? [];
        $defaultColumns = ['sn', 'admission_no', 'name', 'total', 'bf', 'cum', 'grade', 'position', 'class_average', 'gpa', 'cgpa', 'vetted_status'];
        $columnsToShow = !empty($selectedColumns) ? $selectedColumns : $defaultColumns;
        
        // Count visible columns
        $visibleColumnCount = 0;
        if (in_array('sn', $columnsToShow)) $visibleColumnCount++;
        if (in_array('admission_no', $columnsToShow)) $visibleColumnCount++;
        if (in_array('name', $columnsToShow)) $visibleColumnCount++;
        
        // Count assessment columns
        $assessmentColumnsCount = 0;
    @endphp

    @foreach ($allStudentData as $index => $studentData)
        @php
            $schoolInfo = $studentData['schoolInfo'] ?? null;
            $student = $studentData['students'] && $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
            $assessments = $studentData['assessments'] ?? collect();
            $gpaData = $studentData['gpa_data'] ?? [];
            
            // Recalculate assessment columns for this student
            $assessmentColumnsCount = 0;
            foreach ($assessments as $assessment) {
                if (in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow)) {
                    $assessmentColumnsCount++;
                }
            }
            $currentVisibleColumnCount = $visibleColumnCount + $assessmentColumnsCount;
            
            // Add other columns
            $otherColumns = ['total', 'bf', 'cum', 'grade', 'position', 'class_average', 
                            'num_subjects', 'total_grade_points', 'gpa', 'calculated_gpa', 
                            'gpa_grade', 'cgpa', 'compulsory_flag', 'vetted_status'];
            foreach ($otherColumns as $col) {
                if (in_array($col, $columnsToShow)) $currentVisibleColumnCount++;
            }
        @endphp
        
        <div class="student-section">
            <div class="student-section-inner">
                <!-- Header Section -->
                <div class="header">
                    <table class="header-table">
                        <tr>
                            <td width="25%">
                                <div class="school-logo">
                                    @if(!empty($studentData['school_logo_base64']))
                                        <img class="header-img" src="{{ $studentData['school_logo_base64'] }}" alt="School Logo">
                                    @else
                                        <img class="header-img" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" alt="No Logo">
                                    @endif
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
                            </td>
                            <td width="25%">
                                @if(in_array('picture', $columnsToShow))
                                <div class="photo-frame">
                                    @if(!empty($studentData['student_image_base64']))
                                        <img src="{{ $studentData['student_image_base64'] }}" alt="{{ $student->fname ?? 'Student' }}'s picture">
                                    @else
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" alt="Default Photo">
                                    @endif
                                </div>
                                @endif
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
                                        $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
                                    @endphp
                                    <table style="width: 100%;">
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
                                                @if(in_array('dob', $columnsToShow))
                                                <div class="info-row">
                                                    <span class="result-details">DOB:</span>
                                                    <span class="info-value font-bold">
                                                        @php
                                                            $dob = $student->dateofbirth ?? null;
                                                            $formattedDob = 'NO INFO';
                                                            if ($dob) {
                                                                try {
                                                                    $formattedDob = \Carbon\Carbon::parse($dob)->format('jS F, Y');
                                                                } catch (\Exception $e) {
                                                                    $formattedDob = $dob;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $formattedDob }}
                                                    </span>
                                                </div>
                                                @endif
                                                <div class="info-row">
                                                    <span class="result-details">Adm No:</span>
                                                    <span class="info-value font-bold">{{ $student->admissionNo ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                            <td width="30%">
                                                @if(in_array('gender', $columnsToShow))
                                                <div class="info-row">
                                                    <span class="result-details">Sex:</span>
                                                    <span class="info-value font-bold">{{ $student->gender ?? 'NO INFO' }}</span>
                                                </div>
                                                @endif
                                                <div class="info-row">
                                                    <span class="result-details">Date School Opened:</span>
                                                    <span class="info-value font-bold">
                                                        @php
                                                            $dateSchoolOpened = $schoolInfo->date_school_opened ?? null;
                                                            $formattedDateSchoolOpened = $dateSchoolOpened ? \Carbon\Carbon::parse($dateSchoolOpened)->format('jS F, Y') : 'NO INFO';
                                                        @endphp
                                                        {{ $formattedDateSchoolOpened }}
                                                    </span>
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

                <!-- Results Table with Dynamic Columns -->
                <div class="result-table">
                    <table>
                        <thead>
                            <tr>
                                <!-- SN Column -->
                                @if(in_array('sn', $columnsToShow))
                                <th class="col-sn">S/N</th>
                                @endif
                                
                                <!-- Admission No Column -->
                                @if(in_array('admission_no', $columnsToShow))
                                <th class="col-admissionno">Adm No</th>
                                @endif
                                
                                <!-- Name Column -->
                                @if(in_array('name', $columnsToShow))
                                <th class="col-name">Subject</th>
                                @endif
                                
                                <!-- Dynamic Assessment Columns -->
                                @foreach ($assessments as $assessment)
                                    @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                    <th class="col-assessment assessment-header">
                                        {{ substr($assessment->name, 0, 3) }}
                                    </th>
                                    @endif
                                @endforeach
                                
                                <!-- Total Column -->
                                @if(in_array('total', $columnsToShow))
                                <th class="col-total">Total</th>
                                @endif
                                
                                <!-- BF Column -->
                                @if(in_array('bf', $columnsToShow))
                                <th class="col-bf">BF</th>
                                @endif
                                
                                <!-- Cum Column -->
                                @if(in_array('cum', $columnsToShow))
                                <th class="col-cum">Cum</th>
                                @endif
                                
                                <!-- Grade Column -->
                                @if(in_array('grade', $columnsToShow))
                                <th class="col-grade">Grade</th>
                                @endif
                                
                                <!-- Position Column -->
                                @if(in_array('position', $columnsToShow))
                                <th class="col-position">Pos</th>
                                @endif
                                
                                <!-- Class Average Column -->
                                @if(in_array('class_average', $columnsToShow))
                                <th class="col-class-average">Avg</th>
                                @endif
                                
                                <!-- GPA Metrics Columns -->
                                @if(in_array('num_subjects', $columnsToShow))
                                <th class="col-num-subjects">Subj</th>
                                @endif
                                
                                @if(in_array('total_grade_points', $columnsToShow))
                                <th class="col-total-grade-points">TGP</th>
                                @endif
                                
                                @if(in_array('gpa', $columnsToShow))
                                <th class="col-gpa">GPA</th>
                                @endif
                                
                                @if(in_array('calculated_gpa', $columnsToShow))
                                <th class="col-calculated-gpa">Calc</th>
                                @endif
                                
                                @if(in_array('gpa_grade', $columnsToShow))
                                <th class="col-gpa-grade">Grd</th>
                                @endif
                                
                                @if(in_array('cgpa', $columnsToShow))
                                <th class="col-cgpa">CGPA</th>
                                @endif
                                
                                <!-- Compulsory Flag Column -->
                                @if(in_array('compulsory_flag', $columnsToShow))
                                <th class="col-compulsory">Comp</th>
                                @endif
                                
                                <!-- Vetted Status Column -->
                                @if(in_array('vetted_status', $columnsToShow))
                                <th class="col-vetted">Vetted</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['scores'] as $scoreIndex => $score)
                                <tr>
                                    <!-- SN Column -->
                                    @if(in_array('sn', $columnsToShow))
                                    <td class="col-sn">{{ $scoreIndex + 1 }}</td>
                                    @endif
                                    
                                    <!-- Admission No Column -->
                                    @if(in_array('admission_no', $columnsToShow))
                                    <td class="col-admissionno">{{ $student->admissionNo ?? '-' }}</td>
                                    @endif
                                    
                                    <!-- Name Column -->
                                    @if(in_array('name', $columnsToShow))
                                    <td class="col-name subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td>
                                    @endif
                                    
                                    <!-- Dynamic Assessment Columns -->
                                    @foreach ($assessments as $assessment)
                                        @if(in_array($assessment->id, $columnsToShow) || in_array('all_assessments', $columnsToShow))
                                        @php
                                            $assessmentScore = 0;
                                            if (isset($score->assessment_scores)) {
                                                $found = $score->assessment_scores->firstWhere('assessment_id', $assessment->id);
                                                $assessmentScore = $found ? $found->score : 0;
                                            }
                                            $isLow = $assessmentScore < ($assessment->max_score * 0.5);
                                        @endphp
                                        <td class="col-assessment @if ($isLow && is_numeric($assessmentScore)) highlight-red @endif">
                                            {{ $assessmentScore ? number_format($assessmentScore, 0) : '-' }}
                                        </td>
                                        @endif
                                    @endforeach
                                    
                                    <!-- Total Column -->
                                    @if(in_array('total', $columnsToShow))
                                    <td class="col-total @if ($score->total < 50 && is_numeric($score->total)) highlight-red @endif">
                                        {{ $score->total ? number_format($score->total, 1) : '-' }}
                                    </td>
                                    @endif
                                    
                                    <!-- BF Column -->
                                    @if(in_array('bf', $columnsToShow))
                                    <td class="col-bf @if ($score->bf < 50 && is_numeric($score->bf)) highlight-red @endif">
                                        {{ $score->bf ? number_format($score->bf, 1) : '-' }}
                                    </td>
                                    @endif
                                    
                                    <!-- Cum Column -->
                                    @if(in_array('cum', $columnsToShow))
                                    <td class="col-cum @if ($score->cum < 50 && is_numeric($score->cum)) highlight-red @endif">
                                        {{ $score->cum ? number_format($score->cum, 1) : '-' }}
                                    </td>
                                    @endif
                                    
                                    <!-- Grade Column -->
                                    @if(in_array('grade', $columnsToShow))
                                    <td class="col-grade @if (in_array($score->grade ?? '', ['F', 'F9', 'E', 'E8'])) highlight-red @endif">
                                        {{ $score->grade ?? '-' }}
                                    </td>
                                    @endif
                                    
                                    <!-- Position Column -->
                                    @if(in_array('position', $columnsToShow))
                                    <td class="col-position">{{ $score->position ?? '-' }}</td>
                                    @endif
                                    
                                    <!-- Class Average Column -->
                                    @if(in_array('class_average', $columnsToShow))
                                    <td class="col-class-average">{{ $score->class_average ? number_format($score->class_average, 1) : '-' }}</td>
                                    @endif
                                    
                                    <!-- GPA Metrics Columns -->
                                    @if(in_array('num_subjects', $columnsToShow))
                                    <td class="col-num-subjects">
                                        {{ $gpaData['num_subjects'] ?? '-' }}
                                    </td>
                                    @endif
                                    
                                    @if(in_array('total_grade_points', $columnsToShow))
                                    <td class="col-total-grade-points">
                                        {{ $gpaData['total_grade_points'] ? number_format($gpaData['total_grade_points'], 1) : '-' }}
                                    </td>
                                    @endif
                                    
                                    @if(in_array('gpa', $columnsToShow))
                                    <td class="col-gpa">
                                        {{ $gpaData['gpa'] ? number_format($gpaData['gpa'], 2) : '-' }}
                                    </td>
                                    @endif
                                    
                                    @if(in_array('calculated_gpa', $columnsToShow))
                                    <td class="col-calculated-gpa">
                                        {{ $gpaData['calculated_gpa'] ? number_format($gpaData['calculated_gpa'], 2) : '-' }}
                                    </td>
                                    @endif
                                    
                                    @if(in_array('gpa_grade', $columnsToShow))
                                    <td class="col-gpa-grade">
                                        {{ $gpaData['gpa_grade'] ?? '-' }}
                                    </td>
                                    @endif
                                    
                                    @if(in_array('cgpa', $columnsToShow))
                                    <td class="col-cgpa">
                                        {{ $gpaData['cgpa'] ? number_format($gpaData['cgpa'], 2) : '-' }}
                                    </td>
                                    @endif
                                    
                                    <!-- Compulsory Flag Column -->
                                    @if(in_array('compulsory_flag', $columnsToShow))
                                    <td class="col-compulsory">
                                        @if($score->is_compulsory ?? false)
                                            ✓
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @endif
                                    
                                    <!-- Vetted Status Column -->
                                    @if(in_array('vetted_status', $columnsToShow))
                                    <td class="col-vetted">
                                        @php
                                            $vettedStatus = $score->vettedstatus ?? '2';
                                            if ($vettedStatus === '1') {
                                                echo '✓';
                                            } elseif ($vettedStatus === '0') {
                                                echo '✗';
                                            } else {
                                                echo '...';
                                            }
                                        @endphp
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $visibleColumnCount }}" style="text-align: center;">No scores available.</td>
                                </tr>
                            @endforelse
                            
                            <!-- GPA Summary Row -->
                            @if($studentData['scores']->isNotEmpty() && (
                                in_array('num_subjects', $columnsToShow) || 
                                in_array('total_grade_points', $columnsToShow) || 
                                in_array('gpa', $columnsToShow) || 
                                in_array('calculated_gpa', $columnsToShow) || 
                                in_array('gpa_grade', $columnsToShow) || 
                                in_array('cgpa', $columnsToShow)
                            ))
                            <tr style="background-color: #f3f4f6; font-weight: bold;">
                                @php
                                    $summaryColspan = 0;
                                    if (in_array('sn', $columnsToShow)) $summaryColspan++;
                                    if (in_array('admission_no', $columnsToShow)) $summaryColspan++;
                                    if (in_array('name', $columnsToShow)) $summaryColspan++;
                                    $summaryColspan += $assessmentColumnsCount;
                                    if (in_array('total', $columnsToShow)) $summaryColspan++;
                                    if (in_array('bf', $columnsToShow)) $summaryColspan++;
                                    if (in_array('cum', $columnsToShow)) $summaryColspan++;
                                    if (in_array('grade', $columnsToShow)) $summaryColspan++;
                                    if (in_array('position', $columnsToShow)) $summaryColspan++;
                                    if (in_array('class_average', $columnsToShow)) $summaryColspan++;
                                @endphp
                                
                                <td colspan="{{ $summaryColspan }}" style="text-align: right; padding-right: 10px;">
                                    GPA Summary:
                                </td>
                                
                                @if(in_array('num_subjects', $columnsToShow))
                                <td class="col-num-subjects">
                                    {{ $gpaData['num_subjects'] ?? '-' }}
                                </td>
                                @endif
                                
                                @if(in_array('total_grade_points', $columnsToShow))
                                <td class="col-total-grade-points">
                                    {{ $gpaData['total_grade_points'] ? number_format($gpaData['total_grade_points'], 1) : '-' }}
                                </td>
                                @endif
                                
                                @if(in_array('gpa', $columnsToShow))
                                <td class="col-gpa">
                                    {{ $gpaData['gpa'] ? number_format($gpaData['gpa'], 2) : '-' }}
                                </td>
                                @endif
                                
                                @if(in_array('calculated_gpa', $columnsToShow))
                                <td class="col-calculated-gpa">
                                    {{ $gpaData['calculated_gpa'] ? number_format($gpaData['calculated_gpa'], 2) : '-' }}
                                </td>
                                @endif
                                
                                @if(in_array('gpa_grade', $columnsToShow))
                                <td class="col-gpa-grade">
                                    {{ $gpaData['gpa_grade'] ?? '-' }}
                                </td>
                                @endif
                                
                                @if(in_array('cgpa', $columnsToShow))
                                <td class="col-cgpa">
                                    {{ $gpaData['cgpa'] ? number_format($gpaData['cgpa'], 2) : '-' }}
                                </td>
                                @endif
                                
                                <!-- Fill remaining columns -->
                                @if(in_array('compulsory_flag', $columnsToShow))
                                <td class="col-compulsory"></td>
                                @endif
                                
                                @if(in_array('vetted_status', $columnsToShow))
                                <td class="col-vetted"></td>
                                @endif
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Remarks Section -->
                <table class="remarks-table">
                    <tbody>
                        <tr>
                            <td width="50%">
                                <div class="h6">Class Teacher's Remark</div>
                                <div>
                                    <span class="text-space-on-dots">{{ $profile ? ($profile->classteachercomment ?? 'NO INFO') : 'NO INFO' }}</span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Overall Performance</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if(!empty($gpaData))
                                        <strong>GPA:</strong> {{ number_format($gpaData['gpa'] ?? 0, 2) }} 
                                        ({{ $gpaData['gpa_grade'] ?? '-' }}) |
                                        <strong>CGPA:</strong> {{ number_format($gpaData['cgpa'] ?? 0, 2) }}
                                        @else
                                        GPA/CGPA data not available
                                        @endif
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="h6">Guidance Counselor's Remark</div>
                                <div>
                                    <span class="text-space-on-dots">{{ $profile ? ($profile->guidancescomment ?? 'NO INFO') : 'NO INFO' }}</span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Principal's Remark & Promotion</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        {{ $profile ? ($profile->principalscomment ?? 'NO INFO') : 'NO INFO' }}
                                        @php
                                            $status = $studentData['promotionStatusValue'] ?? null;
                                            $statusUpper = strtoupper(trim($status ?? ''));
                                            $statusClass = 'promotion-default';
                                            
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
                                            PROMOTION: {{ $statusText }}
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
                                <span class="font-bold">Issued: </span>
                                <span class="text-dot-space2"> {{ now()->format('jS F, Y') }}</span>
                                <span class="font-bold">Collected by:</span>
                                <span>.......................................</span>
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
                    <div class="powered-by">Powered by Qudroid Systems</div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>