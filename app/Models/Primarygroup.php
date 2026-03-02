<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Primarygroup extends Model
{
    // use HasFactory;
    use HasFactory;
    protected $table="primarygroup";
    protected $primarykey="id";

    protected $fillable = [
        'class_group',
        'primary_group_name',
        'display_order',
        'visibility',
    ];
}
