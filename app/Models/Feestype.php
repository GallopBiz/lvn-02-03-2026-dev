<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feestype extends Model
{
    use HasFactory;
    protected $table="feestypes";
    protected $fillable = ['feestype', 'remarks'];
}
