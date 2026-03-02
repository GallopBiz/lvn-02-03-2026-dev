<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaverequests extends Model
{
    use HasFactory;
    protected $table="leaverequests";
    protected $primarykey="id";

    protected $fillable = [
        'LeaveStartDate',
        'LeaveEndDate',
        'LeaveType',
        'Status'
        
    
    ];



}
