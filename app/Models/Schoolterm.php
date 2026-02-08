<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schoolterm extends Model
{
    use HasFactory;

    protected $table = "schoolterm";

    protected $fillable = [
        'term',
        'status',           // ← add this
    ];

    // Optional: scope for active terms only
    public function scopeActive($query)
    {
        return $query->where('status', true);
        // or → where('status', 1) / where('status', 'active')
    }
}
