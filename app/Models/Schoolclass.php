<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schoolclass extends Model
{
    use HasFactory;
    protected $table = "schoolclass";

    protected $fillable = ['schoolclass','arm','description'];

    public function arms()
    {
        return $this->belongsTo(Schoolarm::class, 'arm', 'id');
    }

    public function classcategories()
    {
        return $this->belongsToMany(Classcategory::class, 'schoolclass_classcategory', 'schoolclass_id', 'classcategory_id');
    }
}