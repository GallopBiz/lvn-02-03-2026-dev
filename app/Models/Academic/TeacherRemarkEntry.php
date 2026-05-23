<?php

namespace App\Models\Academic;

use App\Models\Classname;
use App\Models\HrmsEmployee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherRemarkEntry extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'academic_teacher_remark_entries';

    protected $fillable = [
        'teacher_id',
        'exam_id',
        'class_id',
        'section_name',
        'remark_id',
        'is_delete',
    ];

    public function className()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function remark()
    {
        return $this->belongsTo(AcademicRemark::class, 'remark_id');
    }

    public function teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'teacher_id');
    }
}
