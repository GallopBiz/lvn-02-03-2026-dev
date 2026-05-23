<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRemark extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'academic_student_remarks';

    protected $fillable = [
        'teacher_remark_entry_id',
        'student_id',
        'roll_no',
        'scholar_no',
        'remark_id',
        'remark_text',
        'total_marks',
        'total_obtained',
        'grade',
        'result_remark',
    ];
}
