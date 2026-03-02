<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultersList extends Model
{
    use HasFactory;


    protected $table="defaulters_lists";
    protected $primarykey="id";
    

    protected $fillable = [
        'scholar_no',
        'enrollment_no',
        'student_name',
        'class_name',
        'section_name',
        'account_name',
        'balance_amount',
        'min_date',
        'max_date',
        'student',
        'year',
        'date_type',
        'date',
        'status',
        'ac_head_name',
        'next_yesr_fees',
        'rte',
        'staff_ward',
        'session_name',
        'scholarship',
        'student_id',
    ];


}
