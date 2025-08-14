<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Principalscomment extends Model
{
    use HasFactory;
    protected $table = "principalscomments";

    protected $fillable = [
        'staffId',
        'schoolclassid',
    ];
}
