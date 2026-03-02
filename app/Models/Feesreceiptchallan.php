<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feesreceiptchallan extends Model
{
    use HasFactory;
    protected $table="feesreceiptchallan";
    protected $primarykey="id";
    protected $fillable = ['student_id', 'student_dob', 'recpt_chain', 'due_upto', 'name_student', 'str_json'];

}
