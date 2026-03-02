<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stafftype extends Model
{
    use HasFactory;
    protected $table="staff_type";
    protected $primarykey="Staff_Type_ID";
    // protected $primarykey="id";

    protected $fillable = [
        'Type',
        'shift'
       
        
    
    ];



}
