<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: {{ $orientation == 'landscape' ? '9px' : '10px' }};
            margin: 0;
            padding: 0;
        }

        /* School Header Styles */
        .school-header {
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header-with-logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .school-info {
            flex-grow: 1;
            text-align: center;
            padding: 0 15px;
        }

        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
            line-height: 1.2;
        }

        .school-motto {
            font-size: 12px;
            color: #666;
            font-style: italic;
            margin: 5px 0;
            line-height: 1.2;
        }

        .school-contact {
            font-size: 10px;
            color: #666;
            margin: 3px 0;
            line-height: 1.2;
        }

        /* Report Header */
        .report-header {
            text-align: center;
            margin: 10px 0 15px 0;
            padding-bottom: 8px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #1E40AF;
            margin: 0;
            line-height: 1.2;
        }

        .report-subtitle {
            font-size: 11px;
            color: #666;
            margin: 5px 0;
            line-height: 1.2;
        }

        /* Summary Section */
        .summary {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 10px;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            padding: 4px;
            border: 1px solid #dee2e6;
        }

        .summary .label {
            font-weight: bold;
            background-color: #e9ecef;
        }

        /* Main Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: {{ $orientation == 'landscape' ? '8px' : '9px' }};
        }

        .data-table th {
            background-color: #405189;
            color: white;
            padding: 6px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
            white-space: nowrap;
        }

        .data-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            line-height: 1.3;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .wrap {
            word-wrap: break-word;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: white;
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .footer-generated {
            text-align: left;
        }

        .footer-user {
            text-align: center;
        }

        .footer-pages {
            text-align: right;
        }

        /* Column Width Classes */
        .col-photo { width: 5%; }
        .col-admission { width: 10%; }
        .col-lastname { width: 15%; }
        .col-firstname { width: 15%; }
        .col-othername { width: 15%; }
        .col-gender { width: 5%; }
        .col-dob { width: 8%; }
        .col-age { width: 4%; }
        .col-class { width: 12%; }
        .col-status { width: 8%; }
        .col-admission-date { width: 8%; }
        .col-phone { width: 10%; }
        .col-state { width: 10%; }
        .col-local { width: 10%; }
        .col-religion { width: 8%; }
        .col-blood { width: 6%; }
        .col-father { width: 15%; }
        .col-mother { width: 15%; }
        .col-guardian { width: 10%; }
        .col-term { width: 10%; }
        .col-session { width: 10%; }
    </style>
</head>
<body>
    @if($include_header)
    <div class="school-header">
        @if($include_logo && $school_info)
        <div class="header-with-logo">
            <div class="logo-container">
                @if($school_logo_base64)
                    <!-- Use base64 for PDF -->
                    <img src="{{ $school_logo_base64 }}" alt="School Logo">
                @elseif($school_info->getLogoUrlAttribute())
                    <!-- Use regular URL if base64 conversion failed -->
                    <img src="{{ $school_info->getLogoUrlAttribute() }}" alt="School Logo">
                @else
                    <!-- Fallback placeholder -->
                    <div style="width: 100%; height: 100%; background-color: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 10px;">
                        No Logo
                    </div>
                @endif
            </div>
            <div class="school-info">
                <h1 class="school-name">{{ $school_info->school_name ?? 'School Name' }}</h1>
                @if($school_info && $school_info->school_motto)
                <p class="school-motto">{{ $school_info->school_motto }}</p>
                @endif
                @if($school_info && $school_info->school_address)
                <p class="school-contact">{{ $school_info->school_address }}</p>
                @endif
                @if($school_info && $school_info->school_phone)
                <p class="school-contact">Tel: {{ $school_info->school_phone }}</p>
                @endif
                @if($school_info && $school_info->school_email)
                <p class="school-contact">Email: {{ $school_info->school_email }}</p>
                @endif
            </div>
            <div class="logo-container"></div> <!-- Empty for balance -->
        </div>
        @else
        <div class="school-info">
            <h1 class="school-name">{{ $school_info->school_name ?? 'School Name' }}</h1>
            @if($school_info && $school_info->school_address)
            <p class="school-contact">{{ $school_info->school_address }}</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    <div class="report-header">
        <h2 class="report-title">{{ $title }}</h2>
        <div class="report-subtitle">
            Class: {{ $className }} | Term: {{ $termName }} | Session: {{ $sessionName }} | Generated: {{ $generated }}
        </div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label" width="20%">Total Students:</td>
                <td width="30%">{{ $total }}</td>
                <td class="label" width="20%">Males:</td>
                <td width="30%">{{ $males }}</td>
            </tr>
            <tr>
                <td class="label">Females:</td>
                <td>{{ $females }}</td>
                <td class="label">Generated By:</td>
                <td>{{ $generated_by ?? 'System' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                @foreach($columns as $col)
                    @php
                        // Determine column width class based on column type
                        $widthClass = 'col-' . str_replace('_', '-', $col);
                        if (!in_array($col, ['photo', 'admissionNo', 'lastname', 'firstname', 'othername', 'gender', 'dateofbirth', 'age', 'class', 'status', 'admission_date', 'phone_number', 'state', 'local', 'religion', 'blood_group', 'father_name', 'mother_name', 'guardian_phone', 'term', 'session'])) {
                            $widthClass = 'col-' . substr($col, 0, 10);
                        }
                    @endphp

                    @if($col == 'photo')
                    <th class="text-center {{ $widthClass }}">Photo</th>
                    @elseif($col == 'admissionNo')
                    <th class="text-left {{ $widthClass }}">Admission No</th>
                    @elseif($col == 'lastname')
                    <th class="text-left {{ $widthClass }}">Last Name</th>
                    @elseif($col == 'firstname')
                    <th class="text-left {{ $widthClass }}">First Name</th>
                    @elseif($col == 'othername')
                    <th class="text-left {{ $widthClass }}">Other Name</th>
                    @elseif($col == 'gender')
                    <th class="text-center {{ $widthClass }}">Gender</th>
                    @elseif($col == 'dateofbirth')
                    <th class="text-center {{ $widthClass }}">Date of Birth</th>
                    @elseif($col == 'age')
                    <th class="text-center {{ $widthClass }}">Age</th>
                    @elseif($col == 'class')
                    <th class="text-left {{ $widthClass }}">Class</th>
                    @elseif($col == 'status')
                    <th class="text-center {{ $widthClass }}">Status</th>
                    @elseif($col == 'admission_date')
                    <th class="text-center {{ $widthClass }}">Admission Date</th>
                    @elseif($col == 'phone_number')
                    <th class="text-left {{ $widthClass }}">Phone Number</th>
                    @elseif($col == 'state')
                    <th class="text-left {{ $widthClass }}">State</th>
                    @elseif($col == 'local')
                    <th class="text-left {{ $widthClass }}">LGA</th>
                    @elseif($col == 'religion')
                    <th class="text-center {{ $widthClass }}">Religion</th>
                    @elseif($col == 'blood_group')
                    <th class="text-center {{ $widthClass }}">Blood Group</th>
                    @elseif($col == 'father_name')
                    <th class="text-left {{ $widthClass }}">Father's Name</th>
                    @elseif($col == 'mother_name')
                    <th class="text-left {{ $widthClass }}">Mother's Name</th>
                    @elseif($col == 'guardian_phone')
                    <th class="text-left {{ $widthClass }}">Guardian Phone</th>
                    @elseif($col == 'term')
                    <th class="text-center {{ $widthClass }}">Term</th>
                    @elseif($col == 'session')
                    <th class="text-center {{ $widthClass }}">Session</th>
                    @else
                    <th class="text-left {{ $widthClass }}">{{ ucwords(str_replace('_', ' ', $col)) }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                @foreach($columns as $col)
                    @php
                        $widthClass = 'col-' . str_replace('_', '-', $col);
                        if (!in_array($col, ['photo', 'admissionNo', 'lastname', 'firstname', 'othername', 'gender', 'dateofbirth', 'age', 'class', 'status', 'admission_date', 'phone_number', 'state', 'local', 'religion', 'blood_group', 'father_name', 'mother_name', 'guardian_phone', 'term', 'session'])) {
                            $widthClass = 'col-' . substr($col, 0, 10);
                        }
                    @endphp

                    @if($col == 'photo')
                    <td class="text-center {{ $widthClass }}">
                        <div style="width: 30px; height: 30px; background-color: #ddd; border-radius: 50%; margin: 0 auto;"></div>
                    </td>
                    @elseif($col == 'admissionNo')
                    <td class="text-left {{ $widthClass }}">{{ $student->admissionNo ?? 'N/A' }}</td>
                    @elseif($col == 'lastname')
                    <td class="text-left {{ $widthClass }}">{{ $student->lastname ?? 'N/A' }}</td>
                    @elseif($col == 'firstname')
                    <td class="text-left {{ $widthClass }}">{{ $student->firstname ?? 'N/A' }}</td>
                    @elseif($col == 'othername')
                    <td class="text-left {{ $widthClass }}">{{ $student->othername ?? 'N/A' }}</td>
                    @elseif($col == 'gender')
                    <td class="text-center {{ $widthClass }}">{{ $student->gender ?? 'N/A' }}</td>
                    @elseif($col == 'dateofbirth')
                    <td class="text-center {{ $widthClass }} nowrap">
                        @if($student->dateofbirth)
                            {{ \Carbon\Carbon::parse($student->dateofbirth)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    @elseif($col == 'age')
                    <td class="text-center {{ $widthClass }}">{{ $student->age ?? 'N/A' }}</td>
                    @elseif($col == 'class')
                    <td class="text-left {{ $widthClass }}">
                        {{ $student->schoolclass ?? 'N/A' }}{{ $student->arm_name ? ' - ' . $student->arm_name : '' }}
                    </td>
                    @elseif($col == 'status')
                    <td class="text-center {{ $widthClass }}">
                        @if($student->statusId == 1)
                            Old
                        @elseif($student->statusId == 2)
                            New
                        @else
                            N/A
                        @endif
                        @if($student->student_status)
                            <br><small>({{ $student->student_status }})</small>
                        @endif
                    </td>
                    @elseif($col == 'admission_date')
                    <td class="text-center {{ $widthClass }} nowrap">
                        @if($student->admission_date)
                            {{ \Carbon\Carbon::parse($student->admission_date)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    @elseif($col == 'phone_number')
                    <td class="text-left {{ $widthClass }}">{{ $student->phone_number ?? 'N/A' }}</td>
                    @elseif($col == 'state')
                    <td class="text-left {{ $widthClass }}">{{ $student->state ?? 'N/A' }}</td>
                    @elseif($col == 'local')
                    <td class="text-left {{ $widthClass }}">{{ $student->local ?? 'N/A' }}</td>
                    @elseif($col == 'religion')
                    <td class="text-center {{ $widthClass }}">{{ $student->religion ?? 'N/A' }}</td>
                    @elseif($col == 'blood_group')
                    <td class="text-center {{ $widthClass }}">{{ $student->blood_group ?? 'N/A' }}</td>
                    @elseif($col == 'father_name')
                    <td class="text-left {{ $widthClass }}">{{ $student->father_name ?? 'N/A' }}</td>
                    @elseif($col == 'mother_name')
                    <td class="text-left {{ $widthClass }}">{{ $student->mother_name ?? 'N/A' }}</td>
                    @elseif($col == 'guardian_phone')
                    <td class="text-left {{ $widthClass }}">
                        {{ $student->father_phone ?? $student->mother_phone ?? 'N/A' }}
                    </td>
                    @elseif($col == 'term')
                    <td class="text-center {{ $widthClass }}">
                        @php
                            $term = $student->termid ? \App\Models\Schoolterm::find($student->termid) : null;
                        @endphp
                        {{ $term ? $term->term : 'N/A' }}
                    </td>
                    @elseif($col == 'session')
                    <td class="text-center {{ $widthClass }}">
                        @php
                            $session = $student->sessionid ? \App\Models\Schoolsession::find($student->sessionid) : null;
                        @endphp
                        {{ $session ? $session->session : 'N/A' }}
                    </td>
                    @else
                    <td class="text-left {{ $widthClass }}">
                        {{ $student->$col ?? 'N/A' }}
                    </td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-info">
            <div class="footer-generated">
                Generated on: {{ date('d/m/Y H:i:s') }}
            </div>
            <div class="footer-user">
                Generated by: {{ $generated_by ?? 'System' }}
            </div>
            <div class="footer-pages">
                Page <span class="page-number">1</span> of <span class="page-count">1</span>
            </div>
        </div>
        <div class="footer-note">
            {{ $school_info->school_name ?? 'School Name' }} - Student Report
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Generated by: {{ $generated_by ?? 'System' }} | Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->page_text($x, $y, $text, $font, $size);

            // Add generated date at bottom left
            $date_text = "Generated on: {{ date('d/m/Y H:i:s') }}";
            $pdf->page_text(25, $pdf->get_height() - 25, $date_text, $font, $size);
        }
    </script>
</body>
</html>
