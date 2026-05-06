<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;

class StudentRollNo extends Model
{
    protected $connection = 'dynamic';
    protected $table = 'academic_student_roll_no';
    protected $fillable = [
        'student_id', 'class_id', 'section_id', 'session_id', 'exam_id', 'roll_no', 'room_no'
    ];
}
