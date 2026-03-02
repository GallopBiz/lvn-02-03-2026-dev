<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $table="subjectmaster";
    protected $primarykey="id";
    protected $fillable = ['subject_name', 'subject_type', 'evaluation', 'practical', 'is_delete'];

    public function Classname()
    {
        return $this->belongsToMany(Classname::class, 'academic_class_subject', 'subject_id', 'class_id')->withPivot('stream_id');
    }
    public function combinations()
    {
        return $this->belongsToMany(SubjectCombination::class, 'combination_subject', 'subject_id', 'subject_combination_id');
    }
}
