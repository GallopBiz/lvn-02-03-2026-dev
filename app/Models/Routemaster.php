<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routemaster extends Model
{
    use HasFactory;
    protected $table="routemaster";
    protected $primarykey="id";
    protected $fillable = ['route_name'];
}
