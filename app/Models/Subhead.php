<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Subhead extends Model
{
    use HasFactory;
    protected $table="subheads";
    protected $primarykey="id";

    protected $fillable = [
        'class_name',
        'head_name',
        'sub_head_name',
        'display_order',
        'visibility',
        'entry_type_e1',
        'entry_type_e2',
        'entry_type_e3',
        'entry_type_e4',
    ];
}
