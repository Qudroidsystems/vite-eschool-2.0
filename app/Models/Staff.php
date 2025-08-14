<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    protected $table = "staffbioinfo";
    protected $primaryKey = "userid";

    protected $fillable = [
        'userid',
        'title',
        'employmentid',
        'phonenumber',
        'email',
        'gender',
        'maritalstatus',
        'numberofchildren',
        'spousenumber',
        'address',
        'nationality',
        'state',
        'local',
        'religion',
        'dateofbirth',
    ];
}
