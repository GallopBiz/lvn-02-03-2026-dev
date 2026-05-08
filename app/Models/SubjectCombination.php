<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectCombination extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table="subject_combinations";
    protected $primarykey="id";
    // protected $fillable = ['nature_of_work_name', 'nature_of_work_remarks'];


    protected $fillable = [
        'combination_name',
        'alise_name',
        'streams',
        'is_academic_comb',
        'combination_type',
        'class_id',
        /* 'selected_subjects_data',
        'selected_classes_data', */



    ];
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'combination_subject', 'subject_combination_id', 'subject_id')->withPivot('subject_order')->withTimestamps();
    }

    public function class()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }


}
