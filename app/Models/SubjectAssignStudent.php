<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAssignStudent extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table="subject_assign_student";
    protected $primarykey="id";
    


    protected $fillable = [
        'class_name',
        'section_name',
        'assign_this_combtoall',
        'students_details',
        'is_delete',
              
        
        
    ];

    public function class()
    {
        return $this->belongsTo(Classname::class, 'class_name','class_name');
    }

    public function Student()
    {
        return $this->belongsTo(Student_registration::class, 'students_details','id');
    }

    public function combination()
    {
        return $this->belongsTo(SubjectCombination::class, 'assign_this_combtoall');
    }
    

}
