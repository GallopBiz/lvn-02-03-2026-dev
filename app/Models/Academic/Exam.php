<?php
namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Classes;

class Exam extends Model
{
    protected $connection = 'dynamic';
    protected $table = 'academic_exam';
    protected $fillable = [
        'exam_group_id',
        'exam_name',
        'exam_title',
        'exam_type',
        'exam_type_id',
        'max_marks_theory',
        'max_marks_practical',
        'fail_percent',
        'is_ser',
        'is_locked',
        'class_id',
        'session_year',
        'created_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function classInfo()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
