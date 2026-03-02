<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Rtopaper extends Model
{
    use HasFactory;
    protected $table="rtopaper";
    protected $primarykey="id";
    protected $fillable = ['rto_paper_name', 'remark', 'is_permit'];
}
