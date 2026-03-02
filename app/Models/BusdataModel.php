<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusdataModel extends Model
{
    use HasFactory;
    protected $table="busdata";
    protected $primarykey="id";
    protected $fillable = ['vehicle_name', 'gps', 'vehicle_no', 'branch', 'vehicletype', 
    'status', 'speed', 'ign', 'battery_percentage', 'power', 'location'];

}
