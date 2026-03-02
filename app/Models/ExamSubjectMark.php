<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubjectMark extends Model
{
    use HasFactory;

    protected $table = 'academic_exam_subject_marks';

    protected $fillable = [
        'exam_id',
        'subject_id',
        'min_marks',
        'max_marks',
    ];

    public function exam()
    {
        return $this->belongsTo(ExamMaster::class, 'exam_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

}
