<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentclass extends Model
{
    use HasFactory;
    protected $table = "studentclass";
    protected $primaryKey= "studentId";

    protected $fillable = [
        'studentId',
        'schoolclassid',
        'termid',
        'sessionid',

    ];

}
