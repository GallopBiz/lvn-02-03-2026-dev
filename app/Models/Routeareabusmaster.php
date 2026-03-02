<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routeareabusmaster extends Model
{
    use HasFactory;
    protected $table="routeareabusmaster";
    protected $primarykey="id";
    protected $fillable = ['route_name','area_name','bus_stop_name'];
}
