<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Broadsheets extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadsheet_record_id',
        'subjectclass_id',
        'term_id',
        'staff_id',
        'ca1',
        'ca2',
        'ca3',
        'exam',
        'total',
        'bf', // Added brought forward
        'cum', // Added cumulative score
        'grade',
        'allsubjectstotalscores',
        'subjectpositionclass',
        'cmin',
        'cmax',
        'avg',
        'remark',
        'submiitedby',
        'vettedby',
        'vettedstatus'
    ];

    public function broadsheetRecord()
    {
        return $this->belongsTo(BroadsheetRecord::class,'broadsheet_record_id', 'id');
    }

    public function term()
    {
        return $this->belongsTo(Schoolterm::class, 'term_id');
    }

    public function session()
    {
        return $this->belongsTo(Schoolsession::class, 'session_id');
    }

    public function subjectclass()
    {
        return $this->belongsTo(Subjectclass::class, 'subjectclass_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relationship to Assessment Scores (Dynamic Assessments)
     */
    public function assessmentScores()
    {
        return $this->hasMany(BroadsheetAssessmentScore::class, 'broadsheet_id');
    }

    /**
     * Relationship to Sub-Assessment Scores (for sub-assessments under assessments)
     */
    public function subAssessmentScores()
    {
        return $this->hasMany(BroadsheetSubAssessmentScore::class, 'broadsheet_id');
    }
}