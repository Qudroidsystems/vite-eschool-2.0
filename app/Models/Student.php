<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'home_address',
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
    ];

    protected $casts = [
        'dateofbirth' => 'date',
        'admission_date' => 'date', // Added
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function picture()
    {
        return $this->hasOne(Studentpicture::class, 'studentid', 'id');
    }

    public function schoolClass()
    {
        return $this->hasOne(Studentclass::class, 'studentId', 'id');
    }

    public function parent()
    {
        return $this->hasOne(ParentRegistration::class, 'studentId', 'id');
    }
}