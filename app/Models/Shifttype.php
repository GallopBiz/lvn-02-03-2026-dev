<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shifttype extends Model
{
    use HasFactory;
    protected $table="shift_type";
    // protected $primarykey="Staff_Type_ID";
    protected $primarykey="id";

    protected $fillable = [
        'Type'
    ];
}
