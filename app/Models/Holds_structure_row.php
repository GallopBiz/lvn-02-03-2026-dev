<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holds_structure_row extends Model
{
    use HasFactory;
    protected $table="holds_structure_row";
    protected $primarykey="id";
    protected $fillable = ['fees_date','due_date','term','account_name','fees'];
 
}
