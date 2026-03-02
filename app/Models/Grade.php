<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;
    protected $table="grademaster";
    protected $primarykey="id";

    protected $fillable = [
        'grading_name',
        'applicable',
        'min_per',
        'max_per',
        'grade',        
        'groups',        
    
    ];



}
