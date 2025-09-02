<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $table = 'assessments';

    protected $fillable = [
        'classcategory_id',
        'name',
        'max_score',
    ];

    /**
     * Relationship to Classcategory
     */
    public function classcategory()
    {
        return $this->belongsTo(Classcategory::class, 'classcategory_id');
    }
}