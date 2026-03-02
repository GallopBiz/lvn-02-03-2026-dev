<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Exammaster extends Model
{
    use HasFactory;


    // use HasFactory;
    protected $table="exammasters";
    protected $primarykey="id";
    // protected $fillable = ['nature_of_work_name', 'nature_of_work_remarks'];


    protected $fillable = [
        'exam_name',
        'exam_type',
        'class_id',
        'stream_id',

    ];

    public function subjectMarks()
    {
        return $this->hasMany(ExamSubjectMark::class, 'exam_id');
    }
    public function examType()
    {
        return $this->belongsTo(ExamType::class, 'exam_type');
    }
    public function Classname()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }
    public function Stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }
}
