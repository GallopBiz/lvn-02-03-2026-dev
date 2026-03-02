<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddVehial extends Model
{
    use HasFactory;

    protected $table = "vehicel"; // Ensure table name matches exactly in the database
    protected $primaryKey = "id"; // Fix spelling mistake
    public $timestamps = false; // Disable timestamps if not used

    protected $fillable = [
        'callno', 'vehicelno', 'vehiceltype', 'nature', 'model', 'purchase',
        'capacity', 'standard', 'imei', 'machine', 'studentrelated', 'scrapped', 'is_delete'
    ];
}
