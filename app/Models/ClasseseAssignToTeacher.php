<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasseseAssignToTeacher extends Model
{
    use HasFactory;
    protected $table="classasigntoteacher";
    protected $primarykey="id";
    protected $fillable = ['Class','Section','Teacher_1','Teacher_2'];
}
