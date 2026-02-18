<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $exam->title }} - {{ $student->firstname }} {{ $student->lastname }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Centered School Header */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0d6efd;
        }

        .school-logo {
            max-width: 90px;
            max-height: 90px;
            margin-bottom: 10px;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .school-motto {
            font-size: 12px;
            font-style: italic;
            color: #555;
            margin-bottom: 5px;
        }

        .school-address, .school-contact {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }

        /* Student Info Card - Side by Side */
        .student-card {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            gap: 20px;
        }

        .student-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0d6efd;
        }

        .student-details {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .detail-item {
            min-width: 200px;
        }

        .detail-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        /* Exam Info Bar */
        .exam-info-bar {
            display: flex;
            justify-content: space-between;
            background: #e7f3ff;
            border: 1px solid #0d6efd;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .exam-info-item {
            text-align: center;
            flex: 1;
        }

        .exam-info-label {
            font-size: 10px;
            color: #555;
            margin-bottom: 3px;
        }

        .exam-info-value {
            font-size: 14px;
            font-weight: bold;
            color: #0d6efd;
        }

        /* Horizontal Analysis Strip */
        .analysis-strip {
            display: flex;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .analysis-item {
            flex: 1;
            text-align: center;
            padding: 12px 5px;
            background: white;
            border-right: 1px solid #dee2e6;
        }

        .analysis-item:last-child {
            border-right: none;
        }

        .analysis-value {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            line-height: 1.2;
        }

        .analysis-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Questions */
        .questions-title {
            font-size: 16px;
            font-weight: bold;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .question {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
        }

        .question-number {
            font-weight: bold;
            font-size: 14px;
            color: #0d6efd;
        }

        .question-marks {
            font-size: 12px;
            color: #666;
        }

        .question-text {
            margin-bottom: 15px;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Question Image - Like Old UI */
        .question-image-container {
            margin: 15px 0;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background: #fafafa;
        }

        .question-image {
            max-width: 400px;
            max-height: 250px;
            object-fit: contain;
        }

        .options {
            margin-left: 10px;
        }

        .option {
            margin-bottom: 8px;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 12px;
        }

        .option.correct {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }

        .option.selected {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .option.selected.correct {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }

        .option.selected.incorrect {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }

        .option-label {
            font-weight: bold;
            margin-right: 8px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 12px;
            margin-left: 8px;
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

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .correct-answer-box {
            margin-top: 12px;
            padding: 10px 15px;
            background-color: #e8f4fd;
            border-left: 4px solid #17a2b8;
            border-radius: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        @media print {
            body { padding: 0.25in; }
        }
    </style>
</head>
<body>
    <!-- Centered School Header -->
    <div class="school-header">
        @if($school && $school->school_logo)
            <img src="{{ $school->logo_url }}" alt="School Logo" class="school-logo">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div class="school-motto">{{ $school->school_motto ?? '' }}</div>
        <div class="school-address">{{ $school->school_address ?? '' }}</div>
        <div class="school-contact">
            Phone: {{ $school->school_phone ?? '' }} | Email: {{ $school->school_email ?? '' }}
        </div>
    </div>

    <!-- Student Info Card - Side by Side -->
    <div class="student-card">
        <img src="{{ $student->picture_path }}" alt="Student Photo" class="student-photo">
        <div class="student-details">
            <div class="detail-item">
                <div class="detail-label">Student Name</div>
                <div class="detail-value">{{ $student->lastname }} {{ $student->firstname }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Admission No</div>
                <div class="detail-value">{{ $student->admissionNo }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Class</div>
                <div class="detail-value">{{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Term/Session</div>
                <div class="detail-value">{{ $exam->termRelation->term ?? 'N/A' }} | {{ $exam->sessionRelation->session ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Exam Info Bar -->
    <div class="exam-info-bar">
        <div class="exam-info-item">
            <div class="exam-info-label">Subject</div>
            <div class="exam-info-value">{{ $exam->subject->subject ?? 'N/A' }}</div>
        </div>
        <div class="exam-info-item">
            <div class="exam-info-label">Subject Code</div>
            <div class="exam-info-value">{{ $exam->subject->subject_code ?? 'N/A' }}</div>
        </div>
        <div class="exam-info-item">
            <div class="exam-info-label">Exam Title</div>
            <div class="exam-info-value">{{ $exam->title }}</div>
        </div>
        <div class="exam-info-item">
            <div class="exam-info-label">Duration</div>
            <div class="exam-info-value">{{ $exam->duration }} mins</div>
        </div>
        <div class="exam-info-item">
            <div class="exam-info-label">Attempt Date</div>
            <div class="exam-info-value">{{ $attempt->created_at->format('d/m/Y') ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Horizontal Analysis Strip -->
    <div class="analysis-strip">
        <div class="analysis-item">
            <div class="analysis-value">{{ $totalQuestions ?? 0 }}</div>
            <div class="analysis-label">Total Ques</div>
        </div>
        <div class="analysis-item">
            <div class="analysis-value">{{ $attemptedQuestions ?? 0 }}</div>
            <div class="analysis-label">Attempted</div>
        </div>
        <div class="analysis-item">
            <div class="analysis-value">{{ $correctAnswers ?? 0 }}</div>
            <div class="analysis-label">Correct</div>
        </div>
        <div class="analysis-item">
            <div class="analysis-value">{{ $score ?? 0 }}</div>
            <div class="analysis-label">Score</div>
        </div>
        <div class="analysis-item">
            <div class="analysis-value">{{ $percentage ?? 0 }}%</div>
            <div class="analysis-label">Percentage</div>
        </div>
    </div>

    <!-- Questions and Answers -->
    <div class="questions-title">Questions and Answers</div>

    @foreach($questions as $index => $question)
        <div class="question">
            <div class="question-header">
                <span class="question-number">Question {{ $index + 1 }}</span>
                <span class="question-marks">{{ number_format($question->marks, 1) }} marks</span>
            </div>

            <div class="question-text">
                {!! $question->question_text !!}
            </div>

            <!-- Question Image - Like Old UI -->
            @if($question->image)
                <div class="question-image-container">
                    <img src="{{ $question->image }}" alt="Question image" class="question-image">
                </div>
            @endif

            @if($question->type === 'short_answer')
                <div class="options">
                    <div class="option {{ isset($question->is_correct) ? ($question->is_correct ? 'correct' : 'selected incorrect') : '' }}">
                        <span class="option-label">Student's Answer:</span>
                        {{ $question->student_answer ?? 'Not answered' }}
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
                                if (!$option->is_correct) {
                                    $optionClass .= ' incorrect';
                                }
                            }
                        @endphp
                        <div class="option {{ $optionClass }}">
                            <span class="option-label">{{ $option->label }}.</span>
                            {{ $option->option_text }}
                            @if($option->is_correct)
                                <span class="badge badge-success">✓ Correct</span>
                            @endif
                            @if(isset($question->selected_option_id) && $question->selected_option_id == $option->id)
                                <span class="badge badge-warning">Your Choice</span>
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
