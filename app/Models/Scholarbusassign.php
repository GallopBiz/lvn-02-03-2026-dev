<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarbusassign extends Model
{
    use HasFactory;
    protected $table="scholarbusassign";
    protected $primarykey="id";
    protected $fillable = ['student_id_select_p','pick_shedule_name','pick_up_routes','pickup_area_name','pickup_bus_stop_names','pickup_bus_no','studentaddcheck','latLngInput'];
}
