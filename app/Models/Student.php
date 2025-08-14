<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Schoolterm;
use App\Models\Schoolsession;


class Student extends Model
{

    use HasFactory;
    protected $table = "studentRegistration";
    //protected $primaryKey = "admissionNo";

    protected $fillable = [
        'userid',
        // 'admissionNo',
        'title',
        'firstname',
        'lastname',
        'othername',
        'nationlity',
        'gender',
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

    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id'); // Assuming SchoolClass is the model
    }

    public function term()
    {
        return $this->belongsTo(SchoolTerm::class, 'term_id');
    }

    public function session()
    {
        return $this->belongsTo(Schoolsession::class, 'session_id');
    }

     public function studentClass()
    {
        return $this->hasOne(Studentclass::class, 'studentId', 'id');
    }
}
