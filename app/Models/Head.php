<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Head extends Model
{
    // use HasFactory;
    use HasFactory;
    protected $table="headmaster";
    protected $primarykey="id";

    protected $fillable = [
        'class_name',
        'group_name',
        'head_name',
        'display_order',
        'applicable_to',
        'is_elective',
        
    ];
}
