<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} - {{ $student->lastname }} {{ $student->firstname }}</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            background: #ffffff;
            color: #2d3748;
            line-height: 1.5;
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }

        /* Professional Header with School Info */
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 0 0 20px 20px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: rotate(30deg);
        }

        .school-header {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .school-logo img {
            max-width: 60px;
            max-height: 60px;
            border-radius: 50%;
        }

        .school-info {
            flex: 1;
        }

        .school-name {
            font-size: 24pt;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .school-details {
            font-size: 10pt;
            opacity: 0.95;
            line-height: 1.6;
        }

        .school-details i {
            margin-right: 8px;
            opacity: 0.8;
        }

        .school-motto {
            margin-top: 8px;
            font-style: italic;
            font-size: 11pt;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 8px;
            color: #ffd700;
        }

        /* Student Info Card */
        .student-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 25px;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .student-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .student-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            background: white;
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-details {
            flex: 1;
        }

        .student-name {
            font-size: 22pt;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .student-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            font-size: 11pt;
        }

        .student-meta-item {
            background: rgba(255,255,255,0.2);
            padding: 8px 12px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }

        .student-meta-item strong {
            display: block;
            font-size: 9pt;
            opacity: 0.9;
            margin-bottom: 3px;
        }

        /* Exam Info Bar */
        .exam-info-bar {
            background: #f7fafc;
            border-left: 5px solid #4299e1;
            padding: 15px 25px;
            margin: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exam-title-section h3 {
            color: #2d3748;
            font-size: 16pt;
            margin-bottom: 5px;
        }

        .exam-title-section p {
            color: #718096;
            font-size: 10pt;
        }

        .exam-badge {
            background: #4299e1;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 11pt;
            box-shadow: 0 2px 5px rgba(66, 153, 225, 0.3);
        }

        /* Performance Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin: 20px 25px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4299e1 0%, #667eea 100%);
            border-radius: 10px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .stat-value {
            font-size: 20pt;
            font-weight: 700;
            color: #2d3748;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 9pt;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card.success .stat-value { color: #48bb78; }
        .stat-card.warning .stat-value { color: #ecc94b; }
        .stat-card.info .stat-value { color: #4299e1; }

        /* Questions Section */
        .questions-section {
            margin: 25px;
        }

        .section-title {
            font-size: 16pt;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4299e1;
            display: inline-block;
        }

        .question-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            page-break-inside: avoid;
        }

        .question-header {
            background: #f8fafc;
            padding: 15px 20px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .question-number {
            font-weight: 700;
            color: #4299e1;
            font-size: 12pt;
        }

        .question-marks {
            background: #edf2f7;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10pt;
            color: #4a5568;
        }

        .question-body {
            padding: 20px;
        }

        .question-text {
            font-size: 12pt;
            color: #2d3748;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .question-image {
            margin: 15px 0;
            text-align: center;
        }

        .question-image img {
            max-width: 400px;
            max-height: 250px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .option-item {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            position: relative;
            font-size: 11pt;
        }

        .option-item.correct {
            background: #f0fff4;
            border-color: #48bb78;
            border-width: 2px;
        }

        .option-item.selected {
            background: #ebf8ff;
            border-color: #4299e1;
            border-width: 2px;
        }

        .option-item.selected.correct {
            background: #c6f6d5;
            border-color: #48bb78;
            border-width: 2px;
            position: relative;
        }

        .option-item.selected.correct::after {
            content: '✓';
            position: absolute;
            top: 5px;
            right: 5px;
            color: #48bb78;
            font-size: 14px;
            font-weight: bold;
        }

        .option-item.selected.incorrect {
            background: #fff5f5;
            border-color: #fc8181;
            border-width: 2px;
            position: relative;
        }

        .option-item.selected.incorrect::after {
            content: '✗';
            position: absolute;
            top: 5px;
            right: 5px;
            color: #fc8181;
            font-size: 14px;
            font-weight: bold;
        }

        .option-label {
            font-weight: 600;
            color: #4a5568;
            margin-right: 8px;
        }

        .student-answer-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9pt;
            margin-left: 8px;
            background: #edf2f7;
        }

        .correct-answer-box {
            margin-top: 15px;
            padding: 12px 15px;
            background: #f0fff4;
            border-left: 4px solid #48bb78;
            border-radius: 6px;
            font-size: 11pt;
        }

        .short-answer-box {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .short-answer-student {
            background: #ebf8ff;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .short-answer-correct {
            background: #f0fff4;
            padding: 10px;
            border-radius: 6px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding: 20px 25px;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
            color: #718096;
            font-size: 9pt;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo img {
            max-height: 30px;
            opacity: 0.7;
        }

        .generated-date {
            color: #a0aec0;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Print Styles */
        @media print {
            body { background: white; }
            .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .student-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stat-card { break-inside: avoid; }
            .question-card { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Header with School Info -->
    <div class="header">
        <div class="school-header">
            @if($school && $school->school_logo)
                <div class="school-logo">
                    <img src="{{ $school->logo_url }}" alt="School Logo">
                </div>
            @endif
            <div class="school-info">
                <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
                <div class="school-details">
                    @if($school && $school->school_address)
                        <div><i>📍</i> {{ $school->school_address }}</div>
                    @endif
                    @if($school && ($school->school_phone || $school->school_email))
                        <div>
                            @if($school->school_phone)<i>📞</i> {{ $school->school_phone }}@endif
                            @if($school->school_email) | <i>✉️</i> {{ $school->school_email }}@endif
                        </div>
                    @endif
                    @if($school && $school->school_motto)
                        <div class="school-motto"><i>"</i>{{ $school->school_motto }}<i>"</i></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Student Info Card with Photo -->
    <div class="student-card">
        <div class="student-header">
            <div class="student-photo">
                <img src="{{ $student->picture_path }}" alt="Student Photo">
            </div>
            <div class="student-details">
                <div class="student-name">{{ strtoupper($student->lastname) }}, {{ $student->firstname }}</div>
                <div class="student-meta">
                    <div class="student-meta-item">
                        <strong>Admission No</strong>
                        {{ $student->admissionNo }}
                    </div>
                    <div class="student-meta-item">
                        <strong>Class</strong>
                        {{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }}
                    </div>
                    <div class="student-meta-item">
                        <strong>Term</strong>
                        {{ $exam->termRelation->term ?? 'N/A' }}
                    </div>
                    <div class="student-meta-item">
                        <strong>Session</strong>
                        {{ $exam->sessionRelation->session ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Info Bar -->
    <div class="exam-info-bar">
        <div class="exam-title-section">
            <h3>{{ $exam->title }}</h3>
            <p>{{ $exam->subject->subject ?? 'Subject' }} ({{ $exam->subject->subject_code ?? 'Code' }})</p>
        </div>
        <div class="exam-badge">
            {{ $exam->duration }} Minutes
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="stats-grid">
        <div class="stat-card info">
            <div class="stat-icon">📋</div>
            <div class="stat-value">{{ $totalQuestions }}</div>
            <div class="stat-label">Total Questions</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon">✍️</div>
            <div class="stat-value">{{ $attemptedQuestions }}</div>
            <div class="stat-label">Attempted</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $correctAnswers }}</div>
            <div class="stat-label">Correct</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value">{{ $score }}</div>
            <div class="stat-label">Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-value">{{ $percentage }}%</div>
            <div class="stat-label">Percentage</div>
        </div>
    </div>

    <!-- Questions Section -->
    <div class="questions-section">
        <h2 class="section-title">Question Paper with Answers</h2>

        @foreach($questions as $index => $question)
            <div class="question-card">
                <div class="question-header">
                    <span class="question-number">Question {{ $index + 1 }}</span>
                    <span class="question-marks">{{ number_format($question->marks, 1) }} marks</span>
                </div>
                <div class="question-body">
                    <div class="question-text">
                        {!! $question->question_text !!}
                        @if(isset($question->is_correct))
                            @if($question->is_correct)
                                <span style="color: #48bb78; margin-left: 10px;">✓ Correct</span>
                            @elseif($question->student_answer !== 'Not Attempted' && $question->student_answer !== 'Not answered')
                                <span style="color: #fc8181; margin-left: 10px;">✗ Incorrect</span>
                            @else
                                <span style="color: #ecc94b; margin-left: 10px;">⚠ Not Attempted</span>
                            @endif
                        @endif
                    </div>

                    @if($question->image)
                        <div class="question-image">
                            <img src="{{ $question->image }}" alt="Question image">
                        </div>
                    @endif

                    @if($question->type === 'short_answer')
                        <div class="short-answer-box">
                            <div class="short-answer-student">
                                <strong>Student's Answer:</strong>
                                {{ $question->student_answer ?? 'Not answered' }}
                            </div>
                            @if(isset($question->correct_answer_text) && $question->student_answer !== $question->correct_answer_text)
                                <div class="short-answer-correct">
                                    <strong>Correct Answer:</strong> {{ $question->correct_answer_text }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="options-grid">
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
                                <div class="option-item {{ $optionClass }}">
                                    <span class="option-label">{{ $option->label }}.</span>
                                    {{ $option->option_text }}
                                    @if(isset($question->selected_option_id) && $question->selected_option_id == $option->id)
                                        <span class="student-answer-badge">Your Answer</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if(isset($question->correct_answer_text) && isset($question->selected_option_id) && !$question->is_correct)
                            <div class="correct-answer-box">
                                <strong>Correct Answer:</strong> {{ $question->correct_answer_text }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                @if($school && $school->app_logo)
                    <img src="{{ $school->app_logo_url }}" alt="App Logo">
                @else
                    <span>{{ config('app.name') }}</span>
                @endif
            </div>
            <div class="generated-date">
                Generated on {{ now()->format('F j, Y \a\t g:i A') }}
            </div>
            <div>
                Page {PAGE_NUM} of {PAGE_COUNT}
            </div>
        </div>
    </div>
</body>
</html>
