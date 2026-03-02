<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class busattandence extends Model
{
    use HasFactory;
    protected $table="busattandence";
    protected $primarykey="BA_id";
    protected $fillable = ['DC_name','Bus_no','json_str'];
}
