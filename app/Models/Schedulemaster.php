<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedulemaster extends Model
{
    use HasFactory;
    protected $table="schedulemaster";
    protected $primarykey="id";
    protected $fillable = ['schedule_name'];
}
