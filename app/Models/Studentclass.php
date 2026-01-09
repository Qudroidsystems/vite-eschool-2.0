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

    public function currentClass()
{
    return $this->hasOne(Studentclass::class, 'studentId', 'id')
        ->whereIn('sessionid', function ($query) {
            $query->select('id')
                  ->from('schoolsession')
                  ->where('status', 'Current')
                  ->orWhereRaw('id = (SELECT MAX(id) FROM schoolsession)');
        })
        ->with(['schoolclass.armRelation', 'term', 'session'])
        ->withDefault([
            'schoolclass' => ['schoolclass' => 'Not Assigned', 'armRelation' => null],
            'term' => ['term' => 'N/A'],
            'session' => ['session' => 'N/A']
        ]);
}

}
