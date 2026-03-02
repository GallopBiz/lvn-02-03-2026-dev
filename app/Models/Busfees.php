<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Busfees extends Model
{
    use HasFactory;

    protected $fillable = ['select_batch', 'busfeestypename', 'date', 'amount', 'select_option'];

}
