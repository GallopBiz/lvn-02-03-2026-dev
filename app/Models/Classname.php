<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classname extends Model
{
    use HasFactory;
    protected $table="class_name";
    protected $primarykey="id";
    protected $fillable = ['class_name'];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'academic_class_subject', 'class_id', 'subject_id');
    }
}
