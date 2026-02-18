<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Question Paper - {{ $student->firstname }} {{ $student->lastname }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; margin: 0; padding: 20px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            gap: 20px; /* Space between sections */
        }
        .school-section {
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .school-logo { max-width: 100px; max-height: 100px; margin-bottom: 10px; }
        .school-info h1 { margin: 0 0 5px 0; font-size: 18px; text-align: center; }
        .school-info p { margin: 2px 0; font-size: 11px; text-align: center; }
        .student-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .student-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
            margin-bottom: 5px;
        }
        .student-text h3 { margin: 0 0 5px 0; font-size: 14px; }
        .student-text p { margin: 0; font-size: 11px; }
        .exam-details { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px; }
        .question { margin-bottom: 25px; page-break-inside: avoid; }
        .question-header { font-weight: bold; font-size: 14px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .question-text { margin-bottom: 15px; }
        .question-image { max-width: 300px; max-height: 200px; margin: 10px 0; }
        .options { list-style: none; padding: 0; }
        .option { margin-bottom: 8px; padding: 5px; border: 1px solid #ddd; border-radius: 3px; }
        .option.selected { background: #e8f5e8; border-color: #28a745; }
        .option.correct { background: #d4edda; border-color: #28a745; }
        .option.incorrect { background: #f8d7da; border-color: #dc3545; }
        .option-label { font-weight: bold; margin-right: 10px; }
        .score-section { text-align: center; margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px; }
        .total-score { font-size: 24px; font-weight: bold; color: #28a745; }
        @page { margin: 1in; }
        @media print { body { font-size: 11px; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-section">
            @if($school && $school->school_logo)
                <img src="{{ asset('storage/' . $school->school_logo) }}" alt="School Logo" class="school-logo">
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
            <div class="student-text">
                <h3>{{ $student->firstname }} {{ $student->lastname }}</h3>
                <p>Admission No: {{ $student->admissionNo }}</p>
            </div>
        </div>
    </div>

    <div class="exam-details">
        <h2>{{ $exam->title }}</h2>
        <p>{{ $exam->description ?? '' }}</p>
        @if($attempt)
            <p>Attempt Date: {{ $attempt->created_at->format('F d, Y H:i') }}</p>
        @endif
    </div>

    @foreach($questions as $index => $question)
        <div class="question">
            <div class="question-header">
                Question {{ $index + 1 }} ({{ ucfirst(str_replace('_', ' ', $question->type)) }})
            </div>
            <div class="question-text">{!! $question->question_text !!}</div>
            @if($question->image)
                <img src="{{ asset('storage/' . $question->image) }}" alt="Question Image" class="question-image">
            @endif
            <ul class="options">
                @foreach($question->options as $option)
                    @php
                        $isSelected = $question->student_option_id == $option->id;
                        $isCorrect = $option->is_correct;
                        $statusClass = '';
                        if ($isSelected) {
                            $statusClass = $isCorrect ? 'correct' : 'incorrect';
                        } elseif ($isCorrect) {
                            $statusClass = 'correct';
                        }
                    @endphp
                    <li class="option {{ $statusClass }}">
                        <span class="option-label">{{ $option->label ? strtoupper($option->label) . '.' : '' }}</span>
                        {!! $option->option_text !!}
                        @if($isSelected)
                            <span style="float: right; font-weight: bold;">(Selected)</span>
                        @endif
                        @if($isCorrect)
                            <span style="float: right; color: #28a745; font-weight: bold;">(Correct)</span>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div style="margin-top: 10px; font-style: italic; color: #666;">
                Status: {{ $question->marked_correct }}
            </div>
        </div>
    @endforeach

    @if($result)
        <div class="score-section">
            <h3>Overall Performance</h3>
            <div class="total-score">{{ $result->score }} / {{ $result->total_marks }} ({{ round(($result->score / $result->total_marks) * 100, 1) }}%)</div>
            <p>Questions Attempted: {{ $questions->where('student_answer', '!=', 'Not Attempted')->count() }} / {{ $questions->count() }}</p>
        </div>
    @endif
</body>
</html>
