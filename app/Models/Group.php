<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    // use HasFactory;
    use HasFactory;
    protected $table="groupmaster";
    protected $primarykey="id";

    protected $fillable = [
        'class_name',
        'primary_group_name',
        'group_name',
        'display_order',
        'health_group',
        'entry_type',
    ];
}
