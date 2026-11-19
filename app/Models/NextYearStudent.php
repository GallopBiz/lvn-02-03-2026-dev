<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextYearStudent extends Model
{
    protected $connection = 'next_session_db'; // Connect to next year's DB
    protected $table = 'student_registration'; // Same table name

    protected $fillable = [
        'id',
        'application_for',
        'form_number',
        'scholar_no',
        'date_of_birth',
        'class_name',
        'section_name',
        'class_id',
        'student_name',
        'session_name',
        'json_str',
        'staff_name',
        'phone_number',
        'mobile_number',
        'inq_mode',
        'driver',
        'status',
        'next_year',
        'next_year_section',
        'next_year_stream',
        'next_year_subject_combination',
        'type',
        'password',
        'created_at',
        'updated_at',
    ];
}
