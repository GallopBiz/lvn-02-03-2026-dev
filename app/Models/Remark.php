<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Remark extends Model
{
    use HasFactory;
    protected $table="remarksmaster";
    protected $primarykey="id";

    protected $fillable = [
        'remark',
        'not_show',
        
    ];
}
