<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentpicture extends Model
{
    use HasFactory;
    protected $table = "studentpicture";
    protected $primaryKey = "studentid";

    protected $fillable = [
        'studentid',
        'picture',
    ];
}
