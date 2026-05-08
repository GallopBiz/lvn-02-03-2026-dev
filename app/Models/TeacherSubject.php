<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table="teacher_subjects";
    protected $primarykey="id";

    protected $fillable = [
        'class_id',
        'stream_id',
        'section_name',
        'subject_id',
        'teacher_id',
        'session_name',
        'current_date',
        'role',

        
    ];

    public function Class()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }

    public function Stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }
    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    public function Teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'teacher_id');
    }

}
