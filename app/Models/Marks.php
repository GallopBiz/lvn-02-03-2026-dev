<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marks extends Model
{
    use HasFactory;
    protected $connection = 'dynamic';
    protected $table="previosly_saved_marks_entry";
    protected $primarykey="id";

    protected $fillable = [
        'teacher_id',
        'exam_id',
        'class_id',
        'Stream_id',
        'section_name',
        'subject_id',

    ];

    public function Class()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }

    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    public function Teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'teacher_id');
    }
    public function Exammaster()
    {
        return $this->belongsTo(\App\Models\Academic\Exam::class, 'exam_id');
    }

}
