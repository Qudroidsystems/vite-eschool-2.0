<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'staffId',
        'title',
        'description',
        'duration',
        'start_time',
        'end_time',
        'termid',
        'session',
        'subject_id',
        'schoolclass_id',
        'is_published'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Optional: add relation for convenience
    public function schoolclass()
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclass_id');
    }
}
