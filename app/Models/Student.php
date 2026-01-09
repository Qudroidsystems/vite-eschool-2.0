<?php

namespace App\Models;

use App\Models\ParentRegistration;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Studentclass;
use App\Models\Studentpicture;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $table = 'studentRegistration';

    protected $fillable = [
        'userid',
        'title',
        'firstname',
        'lastname',
        'othername',
        'nationality',
        'gender',
        'phone_number',
        'future_ambition', // Updated from home_address
        'home_address2',
        'placeofbirth',
        'dateofbirth',
        'age',
        'religion',
        'state',
        'local',
        'last_school',
        'last_class',
        'registeredBy',
        'statusId',
        'batchid',
        'student_category',
        'student_status',
        'nin_number',
        'blood_group',
        'mother_tongue',
        'reason_for_leaving',
        'admissionNo',
        'admission_date', // Added
        'admissionYear', // Added (optional, remove if not needed)
        'present_address', // Added
        'permanent_address', // Added
        'sport_house', // Added (from the store method)
        'email', // Added (from the store method)
        'city', // Added (from the store method)
        'can_view_assessments',
    ];

    protected $casts = [
        'dateofbirth' => 'date',
        'admission_date' => 'date', // Added
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

      // Add relationship to User
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'student_id', 'id');
    }


    public function picture()
    {
        return $this->hasOne(Studentpicture::class, 'studentid', 'id');
    }

    public function schoolClass()
    {
        return $this->hasOne(Studentclass::class, 'studentId', 'id');
    }

    public function class()
    {
        return $this->hasOneThrough(
            Schoolclass::class,
            Studentclass::class,
            'studentId', // Foreign key on the intermediate model (Studentclass)
            'id',        // Foreign key on the target model (Schoolclass)
            'id',        // Local key on the parent model (Student)
            'schoolclassid' // Local key on the intermediate model (Studentclass)
        );
    }

    public function term()
    {
        return $this->hasOneThrough(
            Schoolterm::class,
            Studentclass::class,
            'studentId', // Foreign key on the intermediate model (Studentclass)
            'id',        // Foreign key on the target model (Schoolterm)
            'id',        // Local key on the parent model (Student)
            'termid'     // Local key on the intermediate model (Studentclass)
        );
    }

    public function session()
    {
        return $this->hasOneThrough(
            Schoolsession::class,
            Studentclass::class,
            'studentId', // Foreign key on the intermediate model (Studentclass)
            'id',        // Foreign key on the target model (Schoolsession)
            'id',        // Local key on the parent model (Student)
            'sessionid'  // Local key on the intermediate model (Studentclass)
        );
    }

    public function parent()
    {
        return $this->hasOne(ParentRegistration::class, 'studentId', 'id');
    }

     public function currentClass()
    {
        return $this->hasOne(Studentclass::class, 'studentId', 'id')
            ->whereIn('sessionid', function ($query) {
                $query->select('id')
                      ->from('schoolsession')
                      ->where('status', 'Current')
                      ->orWhereRaw('id = (SELECT MAX(id) FROM schoolsession)');
            })
            ->with(['schoolclass.armRelation', 'term', 'session'])
            ->withDefault([
                'schoolclass' => ['schoolclass' => 'Not Assigned', 'armRelation' => null],
                'term' => ['term' => 'N/A'],
                'session' => ['session' => 'N/A']
            ]);
    }

    public function classHistory()
    {
        return $this->hasMany(Studentclass::class, 'studentId', 'id')
            ->with(['schoolclass.armRelation', 'term', 'session', 'promotion'])
            ->orderByDesc('sessionid')
            ->orderByDesc('termid');
    }

    public function promotion()
    {
        return $this->hasOne(PromotionStatus::class, 'studentId', 'id')
            ->whereColumn('schoolclassid', 'studentclass.schoolclassid')
            ->whereColumn('sessionid', 'studentclass.sessionid')
            ->whereColumn('termid', 'studentclass.termid');
    }

    
}
