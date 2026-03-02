<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedulemasterall extends Model
{
    use HasFactory;
    protected $table="schedulemasterall";
    protected $primarykey="id";
    protected $fillable = ['schedule_name','schedule_date','schedule_time_from','schedule_time_to','schedule_print_option','schedule_point','schedule_order'];
}
