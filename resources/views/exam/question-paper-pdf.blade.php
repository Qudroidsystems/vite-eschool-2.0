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
            margin: 20px;
        }

        /* School Header */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0d6efd;
        }

        .school-logo {
            max-height: 80px;
            margin-bottom: 10px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .school-address {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .school-motto {
            font-size: 11px;
            font-style: italic;
            color: #555;
            margin-top: 5px;
        }

        .school-contact {
            font-size: 9px;
            color: #777;
            margin-top: 5px;
        }

        /* Title */
        .exam-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #0d6efd;
        }

        /* Student Info Card - Original Style */
        .student-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
        }

        .student-photo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0d6efd;
            margin-right: 15px;
        }

        .student-details {
            flex: 1;
        }

        .student-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-details td {
            padding: 4px 8px;
            font-size: 11px;
        }

        .student-details .label {
            font-weight: bold;
            color: #555;
            width: 100px;
        }

        /* Exam Info Card */
        .exam-info {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #0d6efd;
            display: flex;
            justify-content: space-between;
        }

        .exam-info-item {
            text-align: center;
            flex: 1;
        }

        .exam-info-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }

        .exam-info-value {
            font-size: 13px;
            font-weight: bold;
            color: #0d6efd;
        }

        /* Stats Grid - Original Style */
        .stats-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .stat-box {
            flex: 1;
            min-width: 80px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 5px;
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Questions */
        .question {
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            page-break-inside: avoid;
        }

        .question-header {
            background: #f8f9fa;
            padding: 8px 12px;
            margin: -12px -12px 10px -12px;
            border-radius: 6px 6px 0 0;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .question-text {
            margin-bottom: 10px;
            font-weight: 500;
        }

        .options {
            margin-left: 20px;
        }

        .option {
            margin-bottom: 5px;
            padding: 5px 8px;
            border-radius: 4px;
        }

        .option.correct {
            background-color: #d4edda;
            border-left: 3px solid #28a745;
        }

        .option.selected {
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }

        .option.selected.correct {
            background-color: #d4edda;
            border-left: 3px solid #28a745;
        }

        .option.selected.incorrect {
            background-color: #f8d7da;
            border-left: 3px solid #dc3545;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-left: 5px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .correct-answer-box {
            margin-top: 10px;
            padding: 8px;
            background-color: #e8f4fd;
            border-left: 3px solid #17a2b8;
            font-size: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
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
            <div class="school-address">{{ $school->school_address }}</div>
        @endif
        @if($school && $school->school_motto)
            <div class="school-motto">"{{ $school->school_motto }}"</div>
        @endif
        @if($school && ($school->school_phone || $school->school_email))
            <div class="school-contact">
                {{ $school->school_phone ?? '' }} {{ $school->school_phone && $school->school_email ? '|' : '' }} {{ $school->school_email ?? '' }}
            </div>
        @endif
    </div>

    <!-- Exam Title -->
    <div class="exam-title">{{ strtoupper($exam->title) }}</div>

    <!-- Student Info Card -->
    <div class="student-info">
        <img src="{{ $student->picture_path }}" class="student-photo" alt="Student Photo">
        <div class="student-details">
            <table>
                <tr>
                    <td class="label">Name:</td>
                    <td><strong>{{ $student->lastname }} {{ $student->firstname }}</strong></td>
                    <td class="label">Admission:</td>
                    <td><strong>{{ $student->admissionNo }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Class:</td>
                    <td>{{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }}</td>
                    <td class="label">Term/Session:</td>
                    <td>{{ $exam->termRelation->term ?? 'N/A' }} {{ $exam->sessionRelation->session ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Subject:</td>
                    <td>{{ $exam->subject->subject ?? 'N/A' }} ({{ $exam->subject->subject_code ?? 'N/A' }})</td>
                    <td class="label">Duration:</td>
                    <td>{{ $exam->duration }} mins</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-value">{{ $totalQuestions ?? 0 }}</div>
            <div class="stat-label">Total Ques</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $attemptedQuestions ?? 0 }}</div>
            <div class="stat-label">Attempted</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $correctAnswers ?? 0 }}</div>
            <div class="stat-label">Correct</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $score ?? 0 }}</div>
            <div class="stat-label">Score</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $percentage ?? 0 }}%</div>
            <div class="stat-label">Percentage</div>
        </div>
    </div>

    <!-- Questions -->
    <h3 style="margin-bottom: 15px; font-size: 14px;">Questions and Answers</h3>

    @foreach($questions as $index => $question)
        <div class="question">
            <div class="question-header">
                <span>Question {{ $index + 1 }}</span>
                <span>{{ number_format($question->marks, 1) }} marks</span>
            </div>

            <div class="question-text">
                <strong>{!! $question->question_text !!}</strong>
                @if($question->image)
                    <div style="margin-top: 10px;">
                        <img src="{{ $question->image }}" alt="Question image" style="max-width: 300px; max-height: 200px;">
                    </div>
                @endif
            </div>

            @if($question->type === 'short_answer')
                <div class="options">
                    <div class="option {{ isset($question->is_correct) ? ($question->is_correct ? 'correct' : 'selected incorrect') : '' }}">
                        <strong>Student's Answer:</strong> {{ $question->student_answer ?? 'Not answered' }}
                        @if(isset($question->is_correct))
                            @if($question->is_correct)
                                <span class="badge badge-success">Correct</span>
                            @else
                                <span class="badge badge-danger">Incorrect</span>
                            @endif
                        @endif
                    </div>
                    @if(isset($question->correct_answer_text) && $question->student_answer !== $question->correct_answer_text)
                        <div class="correct-answer-box">
                            <strong>Correct Answer:</strong> {{ $question->correct_answer_text }}
                        </div>
                    @endif
                </div>
            @else
                <div class="options">
                    @foreach($question->options as $option)
                        @php
                            $optionClass = '';
                            if ($option->is_correct) {
                                $optionClass = 'correct';
                            }
                            if (isset($question->selected_option_id) && $question->selected_option_id == $option->id) {
                                $optionClass .= ' selected';
                            }
                        @endphp
                        <div class="option {{ $optionClass }}">
                            <strong>{{ $option->label }}.</strong> {{ $option->option_text }}
                            @if($option->is_correct)
                                <span class="badge badge-success">✓ Correct</span>
                            @endif
                            @if(isset($question->selected_option_id) && $question->selected_option_id == $option->id)
                                <span class="badge badge-warning">Your Answer</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y \a\t h:i A') }} | {{ $school->school_name ?? 'School Name' }}</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
