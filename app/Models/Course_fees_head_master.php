<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course_fees_head_master extends Model
{
    use HasFactory;
    protected $table="course_fees_head_master";
    protected $primarykey="id";
    protected $fillable = ['ac_head_name','remarks','order'];
 
}
