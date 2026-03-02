<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextYearStudent extends Model
{
    protected $connection = 'next_session_db'; // Connect to next year's DB
    protected $table = 'student_registration'; // Same table name

    protected $fillable = [
        'application_for',
        'form_number',
        'scholar_no',
        'date_of_birth',
        'class_name',
        'student_name',
        'session_name',
        'json_str',
        'staff_name',
        'phone_number',
        'mobile_number',
        'inq_mode',
        'driver',
        'status',
        'type',
        'password',
        'created_at',
        'updated_at',
    ];
}
