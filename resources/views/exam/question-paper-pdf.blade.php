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

        /* Header with School and Student side by side - like old UI */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            gap: 20px;
        }

        .school-section {
            flex: 2;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .school-logo {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }

        .school-info h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #0d6efd;
        }

        .school-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }

        .student-section {
            flex: 1;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
        }

        .student-photo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0d6efd;
        }

        .student-info-text {
            text-align: right;
        }

        .student-info-text h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #333;
        }

        .student-info-text p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }

        /* Exam Title - like new UI */
        .exam-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            color: #0d6efd;
        }

        /* Student Info Table - like new UI but enhanced */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .info-table td {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }

        .info-table .label {
            font-weight: bold;
            background-color: #e9ecef;
            width: 120px;
        }

        /* Stats Grid - like new UI */
        .stats-grid {
            display: flex;
            margin: 20px 0;
            border: 1px solid #0d6efd;
            border-radius: 5px;
            overflow: hidden;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 12px 5px;
            background: #e7f3ff;
            border-right: 1px solid #0d6efd;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }

        .stat-label {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }

        /* Questions - like new UI */
        .questions-title {
            font-size: 16px;
            font-weight: bold;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #0d6efd;
        }

        .question {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            page-break-inside: avoid;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

        .question-image {
            max-width: 300px;
            max-height: 200px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .options {
            margin-left: 15px;
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
            border-color: #28a745;
            border-left-width: 4px;
        }

        .option.selected {
            background-color: #fff3cd;
            border-color: #ffc107;
            border-left-width: 4px;
        }

        .option.selected.correct {
            background-color: #d4edda;
            border-color: #28a745;
            border-left-width: 4px;
        }

        .option.selected.incorrect {
            background-color: #f8d7da;
            border-color: #dc3545;
            border-left-width: 4px;
        }

        .option-label {
            font-weight: bold;
            margin-right: 8px;
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
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
            font-size: 12px;
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

        .footer p {
            margin: 2px 0;
        }

        /* Print styles */
        @media print {
            body { padding: 0.5in; }
            .header { border-bottom-color: #000; }
            .stat-item { background: #f0f0f0; }
        }
    </style>
</head>
<body>
    <!-- Header with School and Student side by side (old UI style) -->
    <div class="header">
        <div class="school-section">
            @if($school && $school->school_logo)
                <img src="{{ $school->logo_url }}" alt="School Logo" class="school-logo">
            @endif
            <div class="school-info">
                <h1>{{ $school->school_name ?? 'School Name' }}</h1>
                <p>{{ $school->school_motto ?? '' }}</p>
                <p>{{ $school->school_address ?? '' }}</p>
                <p>Phone: {{ $school->school_phone ?? '' }} | Email: {{ $school->school_email ?? '' }}</p>
                <p>Date: {{ now()->format('F d, Y') }}</p>
            </div>
        </div>
        <div class="student-section">
            <img src="{{ $student->picture_path }}" alt="Student Photo" class="student-photo">
            <div class="student-info-text">
                <h3>{{ $student->lastname }} {{ $student->firstname }}</h3>
                <p><strong>Admission:</strong> {{ $student->admissionNo }}</p>
                <p><strong>Class:</strong> {{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }}</p>
                <p><strong>Term/Session:</strong> {{ $exam->termRelation->term ?? 'N/A' }} | {{ $exam->sessionRelation->session ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Exam Title -->
    <div class="exam-title">{{ strtoupper($exam->title) }} - {{ $exam->subject->subject ?? 'Subject' }} ({{ $exam->subject->subject_code ?? 'Code' }})</div>

    <!-- Additional Info Table (new UI style) -->
    <table class="info-table">
        <tr>
            <td class="label">Exam Duration:</td>
            <td>{{ $exam->duration }} minutes</td>
            <td class="label">Total Marks:</td>
            <td>{{ $exam->questions->sum('marks') }}</td>
        </tr>
        <tr>
            <td class="label">Attempt Date:</td>
            <td>{{ $attempt->created_at->format('F d, Y H:i') ?? 'N/A' }}</td>
            <td class="label">Status:</td>
            <td>{{ ucfirst($attempt->status ?? 'completed') }}</td>
        </tr>
    </table>

    <!-- Statistics Grid (new UI style) -->
    <div class="stats-grid">
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
                <span class="question-number">Question {{ $index + 1 }}</span>
                <span class="question-marks">{{ number_format($question->marks, 1) }} marks</span>
            </div>

            <div class="question-text">
                <strong>{!! $question->question_text !!}</strong>
            </div>

            @if($question->image)
                <div>
                    <img src="{{ $question->image }}" alt="Question image" class="question-image">
                </div>
            @endif

            @if($question->type === 'short_answer')
                <div class="options">
                    <div class="option {{ isset($question->is_correct) ? ($question->is_correct ? 'correct' : 'selected incorrect') : '' }}">
                        <strong>Student's Answer:</strong> {{ $question->student_answer ?? 'Not answered' }}
                        @if(isset($question->is_correct))
                            @if($question->is_correct)
                                <span class="badge badge-success">✓ Correct</span>
                            @else
                                <span class="badge badge-danger">✗ Incorrect</span>
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
        <p>Generated on {{ now()->format('F j, Y \a\t h:i A') }}</p>
        <p>{{ $school->school_name ?? 'School Name' }} | This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
