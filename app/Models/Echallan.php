<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echallan extends Model
{
    use HasFactory;
    protected $table="challan";
    protected $primarykey="id";
}
