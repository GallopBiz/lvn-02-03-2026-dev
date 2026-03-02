<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generate_duechartstatus extends Model
{
    use HasFactory;
    protected $table="generate_duechartstatus";
    protected $primarykey="id";
    protected $fillable = ['student_id','class_name','status','amount','session_name','updated_date'];
}