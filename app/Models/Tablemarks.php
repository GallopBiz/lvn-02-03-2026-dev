<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tablemarks extends Model
{
    use HasFactory;
    protected $connection = 'dynamic';
    protected $table="academic_students_marks";
    protected $primarykey="id";

    protected $fillable = [
        'marks_id',
        'is_absent',
        'is_absent_pr',
        'student_name',
        'enrollment',
        'roll_no',
        'scholar_no',
        'mark_theory',
        'mark_practical',
        'total_marks',
        'grade',
        'result',
        'overall_grade',
        'internal_assessment_marks',
    
    ];



}
