<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
        'is_published' => 'boolean',
        // Add any other dates if needed
    ];

    /**
     * Get the school class that owns the exam.
     */
    public function schoolclass(): BelongsTo
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclass_id');
    }

    /**
     * Get the questions for the exam.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Formatted start time (safe accessor)
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? $this->start_time->format('d M Y H:i') : '—';
    }

    /**
     * Formatted end time (safe accessor)
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ? $this->end_time->format('d M Y H:i') : '—';
    }

    /**
     * Combined class + arm display (optional helper)
     */
    public function getClassDisplayAttribute(): string
    {
        if (!$this->schoolclass) {
            return '—';
        }

        $className = $this->schoolclass->schoolclass;
        $arm = $this->schoolclass->arm;

        return $arm ? "$className ($arm)" : $className;
    }
}
