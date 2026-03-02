<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenanceheadmaster extends Model
{
    use HasFactory;
    protected $table="maintenanceheadmaster";
    protected $primarykey="id";
    protected $fillable = ['maintenance_group_name', 'maintenance_head_name'];
}
