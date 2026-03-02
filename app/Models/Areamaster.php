<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Areamaster extends Model
{
    use HasFactory;
    protected $table="areamaster";
    protected $primarykey="id";
    protected $fillable = ['area_name'];
}
