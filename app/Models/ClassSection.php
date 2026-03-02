<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSection extends Model
{
    use HasFactory;

    protected $table = 'classes'; // Ensure this matches your database table name
    protected $fillable = ['class_name', 'section_name'];
}
