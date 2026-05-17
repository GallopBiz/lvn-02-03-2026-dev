<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Remark extends Model
{
    use HasFactory;
    protected $table = "academic_remarksmaster";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = [
        'remark',
        'not_show',
        
    ];
}
