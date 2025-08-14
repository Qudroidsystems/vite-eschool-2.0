<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staffpicture extends Model
{
    use HasFactory;
    protected $table = "staffpicture";
    protected $primaryKey = "staffid";

    protected $fillable = [
        'staffId',
        'picture',

    ];
}
