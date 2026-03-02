<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Natureofwork extends Model
{
    use HasFactory;
    protected $table="natureofwork";
    protected $primarykey="id";
    protected $fillable = ['nature_of_work_name', 'nature_of_work_remarks'];
}
