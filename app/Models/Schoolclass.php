<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schoolclass extends Model
{
    use HasFactory;
    protected $table = "schoolclass";

    protected $fillable = ['schoolclass','arm','classcategoryid','description'];



    public function armRelation()
    {
        return $this->belongsTo(Schoolarm::class, 'arm', 'id');
    }

    public function classcategory()
    {
        return $this->belongsTo(Classcategory::class, 'classcategoryid', 'id');
    }
}
