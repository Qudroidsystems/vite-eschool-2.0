<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View question', ['only' => ['index', 'show', 'showDetails', 'edit']]);
        $this->middleware('permission:Create question', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update question', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete question', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'exam_id' => 'required|exists:exams,id',
            'question_text' => 'required|string',
            'type' => 'required|in:mcq,true_false,short_answer',
            'image' => 'nullable|image|max:2048',
            'options' => 'required|array',
            'correct_option' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $type = $request->input('type');
                    if ($type === 'mcq' && !in_array($value, ['a', 'b', 'c', 'd', 'e'])) {
                        $fail("The selected correct option for MCQ must be one of A, B, C, D, or E. Received: '$value'");
                    } elseif ($type === 'true_false' && !in_array($value, ['true', 'false'])) {
                        $fail("The selected correct option for True/False must be True or False. Received: '$value'");
                    } elseif ($type === 'short_answer' && $value !== 'answer') {
                        $fail("The selected correct option for Short Answer must be 'answer'. Received: '$value'");
                    }
                },
            ],
        ];

        if ($request->type === 'mcq') {
            $rules['options.*.option_text'] = 'nullable|string';
        } elseif ($request->type === 'true_false') {
            $rules['options.*.option_text'] = 'nullable|string';
        } elseif ($request->type === 'short_answer') {
            $rules['options.answer.option_text'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Handle image upload
        $imagePath = "";
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('question_images', 'public');
        }


        // Create question with image path
        $question = Question::create([
            'exam_id' => $validated['exam_id'],
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'image' => $imagePath,
        ]);

        if ($validated['type'] === 'mcq') {
            $optionLabels = ['a', 'b', 'c', 'd', 'e'];
            $filledOptions = 0;
            foreach ($optionLabels as $label) {
                if (isset($validated['options'][$label]['option_text']) && !empty($validated['options'][$label]['option_text'])) {
                    $question->options()->create([
                        'option_text' => $validated['options'][$label]['option_text'],
                        'is_correct' => $validated['correct_option'] === $label,
                        'label' => $label,
                    ]);
                    $filledOptions++;
                }
            }
            if ($filledOptions < 2) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $question->delete();
                return back()->withErrors(['options' => 'At least 2 options must be filled for MCQ']);
            }
        } elseif ($validated['type'] === 'true_false') {
            $question->options()->create([
                'option_text' => 'True',
                'is_correct' => $validated['correct_option'] === 'true',
                'label' => 'true',
            ]);
            $question->options()->create([
                'option_text' => 'False',
                'is_correct' => $validated['correct_option'] === 'false',
                'label' => 'false',
            ]);
        } elseif ($validated['type'] === 'short_answer') {
            $question->options()->create([
                'option_text' => $validated['options']['answer']['option_text'],
                'is_correct' => true,
                'label' => 'answer',
            ]);
        }

        return redirect()->route('questions.show', $request->exam_id)->with('success', 'Question added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Ensure we get a single Exam instance with its questions and options
        $exam = Exam::with([
            'questions.options',
            'schoolclass.armRelation' // Eager load the arm relationship
        ])->findOrFail($id);

        $pagetitle = 'Questions Management';
        return view('question.index', compact('exam', 'pagetitle'));
    }

    public function showDetails(Question $question)
    {
        $question->load('exam.schoolclass.armRelation');

        return response()->json([
            'exam_id' => $question->exam_id,
            'exam_title' => $question->exam->title,
            'question_text' => $question->question_text,
            'type' => $question->type,
            'image' => $question->image,
            'options' => $question->options->map(function($option) {
                return [
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                    'label' => $option->label ?? '',
                ];
            })
        ]);
    }

    public function edit(Question $question)
    {
        $question->load('options');

        return response()->json([
            'success' => true,
            'exam_id' => $question->exam_id,
            'question' => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'image' => $question->image,
            ],
            'options' => $question->options->map(function($option) {
                return [
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                    'label' => $option->label ?? '',
                ];
            })
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $type = $question->type; // Use model's fixed type

        $rules = [
            'question_text' => 'required|string',
            'correct_option' => [
                'required',
                function ($attribute, $value, $fail) use ($type) {
                    if ($type === 'mcq' && !in_array($value, ['a', 'b', 'c', 'd', 'e'])) {
                        $fail("The selected correct option for MCQ must be one of A, B, C, D, or E. Received: '$value'");
                    } elseif ($type === 'true_false' && !in_array($value, ['true', 'false'])) {
                        $fail("The selected correct option for True/False must be True or False. Received: '$value'");
                    } elseif ($type === 'short_answer' && $value !== 'answer') {
                        $fail("The selected correct option for Short Answer must be 'answer'. Received: '$value'");
                    }
                },
            ],
            'exam_id' => 'required|exists:exams,id'
        ];

        if ($type === 'mcq') {
            $rules['options'] = 'required|array';
            $rules['options.*.option_text'] = 'nullable|string';
        } elseif ($type === 'short_answer') {
            $rules['options.answer.option_text'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        // Extra check for MCQ: at least 2 non-empty options
        if ($type === 'mcq') {
            $nonEmptyOptions = 0;
            if (isset($request->options)) {
                foreach ($request->options as $option) {
                    if (!empty(trim($option['option_text'] ?? ''))) {
                        $nonEmptyOptions++;
                    }
                }
            }
            if ($nonEmptyOptions < 2) {
                $validator->errors()->add('options', 'At least 2 options must be filled for MCQ');
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]);
        }

        $validated = $validator->validated();

        // Update question (no type change)
        $question->update([
            'question_text' => $validated['question_text'],
            'exam_id' => $validated['exam_id']
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }
            $question->update([
                'image' => $request->file('image')->store('question_images', 'public')
            ]);
        } elseif ($request->has('remove_image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }
            $question->update(['image' => null]);
        }

        // Update options
        $question->options()->delete();

        if ($type === 'mcq') {
            foreach ($request->options as $key => $option) {
                if (!empty(trim($option['option_text'] ?? ''))) {
                    $question->options()->create([
                        'option_text' => $option['option_text'],
                        'is_correct' => $request->correct_option === $key,
                        'label' => $key,
                    ]);
                }
            }
        } elseif ($type === 'true_false') {
            $question->options()->createMany([
                ['option_text' => 'True', 'is_correct' => $request->correct_option === 'true', 'label' => 'true'],
                ['option_text' => 'False', 'is_correct' => $request->correct_option === 'false', 'label' => 'false']
            ]);
        } elseif ($type === 'short_answer') {
            $question->options()->create([
                'option_text' => $request->input('options.answer.option_text'),
                'is_correct' => true,
                'label' => 'answer',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully'
        ]);
    }

    public function destroy(Question $question)
    {
        if ($question->image) {
            Storage::disk('public')->delete($question->image);
        }
        $question->options()->delete();
        $question->delete();
        return response()->json(['success' => true]);
    }
}
