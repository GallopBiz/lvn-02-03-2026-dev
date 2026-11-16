<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $connection = 'dynamic';
    protected $table="subjectmaster";
    protected $primarykey="id";
    protected $fillable = [
        'subject_name',
        'subject_type',
        'evaluation',
        'practical',
        'do_not_print_in_main_scholastic_area',
        'special_formatting_classes',
        'is_delete',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('subjectmaster.is_delete', 0);
        });
    }

    public function scopeWithDeletedSubjects($query)
    {
        return $query->withoutGlobalScope('active');
    }

    public function isSpecialFormattingForClass($classId = null, $className = null): bool
    {
        return \App\Academic\AcademicHelper::isSpecialFormattingForClass($this, $classId, $className);
    }

    public function Classname()
    {
        return $this->belongsToMany(Classname::class, 'academic_class_subject', 'subject_id', 'class_id')->withPivot('stream_id');
    }
    public function combinations()
    {
        return $this->belongsToMany(SubjectCombination::class, 'combination_subject', 'subject_id', 'subject_combination_id');
    }
}
