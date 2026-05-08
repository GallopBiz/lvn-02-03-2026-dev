<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;
    protected $connection = 'dynamic';
    protected $table="classes";
    protected $primarykey="id";
    protected $fillable = ['class_name','section_name','start_time','end_time'];


}
