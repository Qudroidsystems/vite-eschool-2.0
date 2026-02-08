<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCurrentTerm extends Model
{
    use HasFactory;

    protected $table = "student_current_term";

    protected $fillable = [
        'studentId',
        'schoolclassId',
        'termId',
        'sessionId',
        'is_current'
    ];

    /**
     * Relationship with Student
     */
    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'studentId');
    }

    /**
     * Relationship with Class
     */
    public function schoolClass()
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclassId');
    }

    /**
     * Relationship with Term
     */
    public function term()
    {
        return $this->belongsTo(Schoolterm::class, 'termId');
    }

    /**
     * Relationship with Session
     */
    public function session()
    {
        return $this->belongsTo(Schoolsession::class, 'sessionId');
    }

    /**
     * Scope to get current terms
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope to get by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('studentId', $studentId);
    }

    /**
     * Scope to get by class and session
     */
    public function scopeByClassAndSession($query, $classId, $sessionId)
    {
        return $query->where('schoolclassId', $classId)
                     ->where('sessionId', $sessionId);
    }

    /**
     * Set as current term for student
     * This will automatically update previous current term
     */
    public function setAsCurrent()
    {
        // First, remove current flag from any existing current term for this student
        StudentCurrentTerm::where('studentId', $this->studentId)
                         ->where('is_current', true)
                         ->update(['is_current' => false]);

        // Then set this as current
        $this->is_current = true;
        $this->save();

        return $this;
    }

    /**
     * Get current term for a specific student
     */
    public static function getCurrentForStudent($studentId)
    {
        return self::with(['student', 'schoolClass', 'term', 'session'])
                   ->where('studentId', $studentId)
                   ->where('is_current', true)
                   ->first();
    }

    /**
     * Get students by class, term, and session
     */
    public static function getStudentsByClassTermSession($classId, $termId, $sessionId)
    {
        return self::with('student')
                   ->where('schoolclassId', $classId)
                   ->where('termId', $termId)
                   ->where('sessionId', $sessionId)
                   ->get();
    }

    /**
     * Check if student has a current term record
     */
    public static function hasCurrentTerm($studentId)
    {
        return self::where('studentId', $studentId)
                   ->where('is_current', true)
                   ->exists();
    }

    /**
     * Promote student to next class/term
     */
    public static function promoteStudent($studentId, $newClassId, $newTermId, $newSessionId)
    {
        // Get current term record
        $current = self::getCurrentForStudent($studentId);

        if ($current) {
            // Mark current as not current (historical record)
            $current->update(['is_current' => false]);

            // Create new current term record
            return self::create([
                'studentId' => $studentId,
                'schoolclassId' => $newClassId,
                'termId' => $newTermId,
                'sessionId' => $newSessionId,
                'is_current' => true
            ]);
        }

        return null;
    }
}
