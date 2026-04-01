<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Classcategory extends Model
{
    use HasFactory;

    protected $table = 'classcategories';

    protected $fillable = [
        'category',
        'is_senior',
    ];

    protected $casts = [
        'is_senior' => 'boolean',
    ];

    /**
     * Relationship to Assessments
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'classcategory_id');
    }

    public function schoolClasses()
    {
        return $this->belongsToMany(Schoolclass::class, 'schoolclass_classcategory', 'classcategory_id', 'schoolclass_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'classcategory_id');
    }

    /**
     * Calculate grade based on total score and class type
     *
     * @param float $score The TOTAL score to calculate grade for
     * @return string The calculated grade
     */
    public function calculateGrade($score)
    {
        Log::debug('Classcategory::calculateGrade called', [
            'score' => $score,
            'is_senior' => $this->is_senior,
            'category' => $this->category
        ]);

        if ($this->is_senior) {
            return $this->calculateSeniorGrade($score);
        }
        return $this->calculateJuniorGrade($score);
    }

    /**
     * Calculate grade for junior classes (JSS 1-3)
     */
    private function calculateJuniorGrade($score)
    {
        Log::debug('Calculating junior grade', ['score' => $score]);

        if ($score === null || $score == 0) {
            return 'F';
        }

        if ($score >= 70 && $score <= 100) {
            return 'A';
        } elseif ($score >= 60 && $score <= 69) {
            return 'B';
        } elseif ($score >= 50 && $score <= 59) {
            return 'C';
        } elseif ($score >= 40 && $score <= 49) {
            return 'D';
        } elseif ($score >= 30 && $score <= 39) {
            return 'E';
        } else {
            return 'F';
        }
    }

    /**
     * Calculate grade for senior classes (SSS 1-3) - WAEC/NECO Standard
     * Uses TOTAL score for grading
     *
     * Grading Scheme:
     * 75 - 100: A1 (Excellent)
     * 70 - 74: B2 (Very Good)
     * 65 - 69: B3 (Good)
     * 60 - 64: C4 (Credit)
     * 55 - 59: C5 (Credit)
     * 50 - 54: C6 (Credit)
     * 45 - 49: D7 (Pass)
     * 40 - 44: E8 (Pass)
     * 0 - 39: F9 (Fail)
     */
    private function calculateSeniorGrade($score)
    {
        Log::debug('Calculating senior grade', ['score' => $score]);

        // Handle null, zero, or negative scores
        if ($score === null || $score <= 0) {
            return 'F9';
        }

        // WAEC/NECO Grading Scheme - Based on TOTAL score
        if ($score >= 75 && $score <= 100) {
            return 'A1';
        } elseif ($score >= 70 && $score <= 74) {
            return 'B2';
        } elseif ($score >= 65 && $score <= 69) {
            return 'B3';
        } elseif ($score >= 60 && $score <= 64) {
            return 'C4';
        } elseif ($score >= 55 && $score <= 59) {
            return 'C5';
        } elseif ($score >= 50 && $score <= 54) {
            return 'C6';
        } elseif ($score >= 45 && $score <= 49) {
            return 'D7';
        } elseif ($score >= 40 && $score <= 44) {
            return 'E8';
        } else {
            return 'F9';
        }
    }

    /**
     * Get grade point based on score (for GPA calculation)
     *
     * @param float $score The total score
     * @return float The grade point
     */
    public function getGradePoint($score)
    {
        Log::debug('Getting grade point', ['score' => $score, 'is_senior' => $this->is_senior]);

        if ($score === null || $score <= 0) {
            return 0.0;
        }

        if ($this->is_senior) {
            return $this->getSeniorGradePoint($score);
        }
        return $this->getJuniorGradePoint($score);
    }

    /**
     * Get grade point for senior classes (WAEC/NECO Standard)
     */
    private function getSeniorGradePoint($score)
    {
        if ($score >= 75 && $score <= 100) {
            return 5.0; // A1
        } elseif ($score >= 70 && $score <= 74) {
            return 4.5; // B2
        } elseif ($score >= 65 && $score <= 69) {
            return 4.0; // B3
        } elseif ($score >= 60 && $score <= 64) {
            return 3.5; // C4
        } elseif ($score >= 55 && $score <= 59) {
            return 3.0; // C5
        } elseif ($score >= 50 && $score <= 54) {
            return 2.5; // C6
        } elseif ($score >= 45 && $score <= 49) {
            return 2.0; // D7
        } elseif ($score >= 40 && $score <= 44) {
            return 1.0; // E8
        } else {
            return 0.0; // F9
        }
    }

    /**
     * Get grade point for junior classes
     */
    private function getJuniorGradePoint($score)
    {
        if ($score >= 70 && $score <= 100) {
            return 5.0; // A
        } elseif ($score >= 60 && $score <= 69) {
            return 4.0; // B
        } elseif ($score >= 50 && $score <= 59) {
            return 3.0; // C
        } elseif ($score >= 40 && $score <= 49) {
            return 2.0; // D
        } elseif ($score >= 30 && $score <= 39) {
            return 1.0; // E
        } else {
            return 0.0; // F
        }
    }

    /**
     * Get the grade type label
     */
    public function getGradeTypeAttribute()
    {
        return $this->is_senior ? 'Senior' : 'Junior';
    }

    /**
     * Scope to filter by class type
     */
    public function scopeSenior($query)
    {
        return $query->where('is_senior', true);
    }

    public function scopeJunior($query)
    {
        return $query->where('is_senior', false);
    }

    /**
     * Calculate total maximum score from assessments
     */
    public function getTotalMaxScoreAttribute()
    {
        return $this->assessments->sum('max_score');
    }
}
