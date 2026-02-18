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
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0d6efd;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .exam-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .student-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .student-info table {
            width: 100%;
        }
        .student-info td {
            padding: 5px;
        }
        .student-info .label {
            font-weight: bold;
            width: 120px;
        }
        .stats {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #0d6efd;
        }
        .stats table {
            width: 100%;
        }
        .stats td {
            padding: 5px;
            text-align: center;
        }
        .stats .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
        }
        .question {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        .question-header {
            background: #f8f9fa;
            padding: 8px;
            margin: -10px -10px 10px -10px;
            border-radius: 5px 5px 0 0;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
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
            padding: 5px;
            border-radius: 3px;
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
            background-image: linear-gradient(45deg, rgba(40,167,69,0.1) 0%, rgba(40,167,69,0.1) 100%);
        }
        .option.selected.incorrect {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .mark {
            font-size: 11px;
            color: #6c757d;
            margin-left: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 3px;
            margin-left: 10px;
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
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .correct-answer {
            margin-top: 10px;
            padding: 8px;
            background-color: #e8f4fd;
            border-left: 4px solid #17a2b8;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->schoolname ?? 'School Name' }}</div>
        <div class="exam-title">{{ $exam->title }}</div>
        <div>{{ $exam->subject->subject ?? 'Subject' }} ({{ $exam->subject->subject_code ?? 'Code' }})</div>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name:</td>
                <td><strong>{{ $student->lastname }} {{ $student->firstname }}</strong></td>
                <td class="label">Admission No:</td>
                <td><strong>{{ $student->admissionNo }}</strong></td>
            </tr>
            <tr>
                <td class="label">Class:</td>
                <td>{{ $exam->schoolclass->schoolclass ?? 'N/A' }} {{ $exam->schoolclass->arm ?? '' }}</td>
                <td class="label">Date:</td>
                <td>{{ now()->format('F j, Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <table>
            <tr>
                <td>
                    <div class="stat-value">{{ $totalQuestions ?? 0 }}</div>
                    <div>Total Questions</div>
                </td>
                <td>
                    <div class="stat-value">{{ $attemptedQuestions ?? 0 }}</div>
                    <div>Attempted</div>
                </td>
                <td>
                    <div class="stat-value">{{ $correctAnswers ?? 0 }}</div>
                    <div>Correct</div>
                </td>
                <td>
                    <div class="stat-value">{{ $score ?? 0 }}</div>
                    <div>Score</div>
                </td>
                <td>
                    <div class="stat-value">{{ $percentage ?? 0 }}%</div>
                    <div>Percentage</div>
                </td>
            </tr>
        </table>
    </div>

    <h3>Questions and Answers</h3>

    @foreach($questions as $index => $question)
        <div class="question">
            <div class="question-header">
                Question {{ $index + 1 }}
                <span class="mark">({{ number_format($question->marks, 1) }} marks)</span>
                @if(isset($question->is_correct))
                    @if($question->is_correct)
                        <span class="badge badge-success">Correct</span>
                    @elseif($question->student_answer !== 'Not Attempted')
                        <span class="badge badge-danger">Incorrect</span>
                    @else
                        <span class="badge badge-warning">Not Attempted</span>
                    @endif
                @endif
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
                    </div>
                    @if(isset($question->correct_answer_text) && $question->student_answer !== $question->correct_answer_text)
                        <div class="correct-answer">
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
                            @if($question->type === 'true_false')
                                <strong>{{ ucfirst($option->label) }}:</strong>
                            @else
                                <strong>{{ $option->label }}.</strong>
                            @endif
                            {{ $option->option_text }}
                            @if($option->is_correct)
                                <span style="color: #28a745; margin-left: 10px;">✓ Correct Answer</span>
                            @endif
                            @if(isset($question->selected_option_id) && $question->selected_option_id == $option->id)
                                <span style="color: #ffc107; margin-left: 10px;">← Student's Choice</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y \a\t h:i A') }} | {{ $school->schoolname ?? 'School Name' }}</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
