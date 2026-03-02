<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Busstop extends Model
{
    use HasFactory;
    protected $table="busstop";

    protected $fillable = ['area_name', 'bus_stop_name', 'latitude', 'langitude'];

}
