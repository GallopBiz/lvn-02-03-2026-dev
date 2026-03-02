<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenancegroupmaster extends Model
{
    use HasFactory;
    protected $table="maintenancegroupmaster";
    protected $primarykey="id";
    protected $fillable = ['maintenance_group_name'];
}
