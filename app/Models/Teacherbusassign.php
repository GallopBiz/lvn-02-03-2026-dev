<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacherbusassign extends Model
{
    use HasFactory;
    protected $table="teacherbusassign";
    protected $primarykey="id";
    protected $fillable = ['student_id_select_p','pick_shedule_name','pick_up_routes','pickup_area_name','pickup_bus_stop_names','pickup_bus_no'];
}
