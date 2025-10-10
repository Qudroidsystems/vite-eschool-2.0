<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'question_id',
        'option_id',
        'short_answer',
    ];

    /**
     * Relationship to the Option model (for MCQ/True/False).
     */
    public function option()
    {
        return $this->belongsTo(Option::class, 'option_id', 'id');
    }

    /**
     * Relationship to the Question model.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    /**
     * Relationship to the User model (assuming Student is a User).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // Adjust to StudentRegistration if needed
    }

    /**
     * Relationship to the Exam model.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'id');
    }

    /**
     * Get the answer text (handles both option and short_answer).
     */
    public function getAnswerTextAttribute()
    {
        if ($this->option_id) {
            return $this->option ? $this->option->option_text : null;
        }
        return $this->short_answer;
    }
}