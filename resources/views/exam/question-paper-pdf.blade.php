<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $exam->title }} - {{ $student->firstname }} {{ $student->lastname }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 15px;
        }

        /* School Header */
        .school-header {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .school-logo {
            max-height: 60px;
            margin-bottom: 5px;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .school-details {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }

        /* Student Info - Clean table style */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border: 1px solid #ddd;
        }

        .info-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .info-table .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 120px;
        }

        /* Stats Row */
        .stats-row {
            display: flex;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-bottom: none;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 10px 5px;
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        /* Questions */
        .questions-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
        }

        .question {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .question-number {
            color: #000;
        }

        .question-marks {
            color: #555;
        }

        .question-text {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .question-image {
            max-width: 300px;
            max-height: 200px;
            margin: 10px 0;
        }

        .options {
            margin-left: 15px;
        }

        .option {
            margin-bottom: 5px;
            font-size: 11px;
        }

        .option-label {
            font-weight: bold;
            margin-right: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 8px;
        }

        .badge-correct {
            color: #28a745;
        }

        .badge-incorrect {
            color: #dc3545;
        }

        .badge-student {
            color: #ffc107;
        }

        .correct-answer {
            margin-top: 8px;
            padding: 5px 8px;
            background-color: #f0f7ff;
            border-left: 3px solid #17a2b8;
            font-size: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- School Header -->
    <div class="school-header">
        @if($school && $school->school_logo)
            <img src="{{ $school->logo_url }}" class="school-logo" alt="School Logo">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        @if($school && $school->school_address)
            <div class="school-details">{{ $school->school_address }}</div>
        @endif
        @if($school && ($school->school_phone || $school->school_email))
            <div class="school-details">
                {{ $school->school_phone ?? '' }} {{ $school->school_phone && $school->school_email ? '|' : '' }} {{ $school->school_email ?? '' }}
            </div>
        @endif
    </div>

    <!-- Student Information Table -->
    <table class="info-table">
        <tr>
            <td class="label">Student Name:</td>
            <td><strong>{{ $student->lastname }} {{ $student->firstname }}</strong></td>
            <td class="label">Admission No:</td>
            <td><strong>{{ $student->admissionNo }}</strong></td>
        </tr>
        <tr>
            <td class="label">Class:</td>
            <td>{{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }}</td>
            <td class="label">Term/Session:</td>
            <td>{{ $exam->termRelation->term ?? 'N/A' }} | {{ $exam->sessionRelation->session ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Subject:</td>
            <td>{{ $exam->subject->subject ?? 'N/A' }} ({{ $exam->subject->subject_code ?? 'N/A' }})</td>
            <td class="label">Exam Title:</td>
            <td>{{ $exam->title }}</td>
        </tr>
    </table>

    <!-- Statistics Row -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-value">{{ $totalQuestions ?? 0 }}</div>
            <div class="stat-label">Total Questions</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $attemptedQuestions ?? 0 }}</div>
            <div class="stat-label">Attempted</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $correctAnswers ?? 0 }}</div>
            <div class="stat-label">Correct</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $score ?? 0 }}</div>
            <div class="stat-label">Score</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $percentage ?? 0 }}%</div>
            <div class="stat-label">Percentage</div>
        </div>
    </div>

    <!-- Questions and Answers -->
    <div class="questions-title">Questions and Answers</div>

    @foreach($questions as $index => $question)
        <div class="question">
            <div class="question-header">
                <span class="question-number">Question {{ $index + 1 }} ({{ number_format($question->marks, 1) }} marks)</span>
                @if(isset($question->is_correct))
                    @if($question->is_correct)
                        <span class="status-badge badge-correct">Correct</span>
                    @elseif($question->student_answer !== 'Not Attempted' && $question->student_answer !== 'Not answered')
                        <span class="status-badge badge-incorrect">Incorrect</span>
                    @else
                        <span class="status-badge" style="color: #ffc107;">Not Attempted</span>
                    @endif
                @endif
            </div>

            <div class="question-text">
                {!! $question->question_text !!}
            </div>

            @if($question->image)
                <div>
                    <img src="{{ $question->image }}" class="question-image" alt="Question image">
                </div>
            @endif

            @if($question->type === 'short_answer')
                <div class="options">
                    <div class="option">
                        <span class="option-label">Student's Answer:</span>
                        {{ $question->student_answer ?? 'Not answered' }}
                    </div>
                    @if(isset($question->correct_answer_text) && $question->student_answer !== $question->correct_answer_text)
                        <div class="correct-answer">
                            <span class="option-label">Correct Answer:</span> {{ $question->correct_answer_text }}
                        </div>
                    @endif
                </div>
            @else
                <div class="options">
                    @foreach($question->options as $option)
                        <div class="option">
                            <span class="option-label">{{ $option->label }}.</span>
                            {{ $option->option_text }}
                            @if($option->is_correct)
                                <span class="status-badge badge-correct">Correct Answer</span>
                            @endif
                            @if(isset($question->selected_option_id) && $question->selected_option_id == $option->id)
                                <span class="status-badge badge-student">Student's Choice</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t h:i A') }} | {{ $school->school_name ?? 'School Name' }}
    </div>
</body>
</html>
