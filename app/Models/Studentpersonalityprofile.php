<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentpersonalityprofile extends Model
{
    use HasFactory;

    protected $table = 'studentpersonalityprofiles';

    protected $primaryKey = 'studentid';

    protected $fillable = [
        'studentid',
        'staffid',
        'schoolclassid',
        'punctuality',
        'neatness',
        'leadership',
        'attitude',
        'reading',
        'honesty',
        'cooperation',
        'selfcontrol',
        'politeness',
        'physicalhealth',
        'stability',
        'gamesandsports',
        'principalscomment',
        'classteachercomment',
        'remark_on_other_activities',
        'no_of_times_school_absent',
        'signature',
        'guidancescomment',
        'termid',
        'sessionid',
        'attendance',
        'attentiveness_in_class',
        'class_participation',
        'relationship_with_others',
        'doing_assignment',
        'writing_skill',
        'reading_skill',
        'spoken_english_communication',
        'hand_writing',
        'club',
        'music',
    ];
}