<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;

class Marksheet extends Model
{
    protected $connection = 'dynamic';
    protected $table = 'academic_marksheets';

    protected $fillable = [
        'student_id',
        'class_id',
        'exam_id',
        'session_year',
        'snapshot',
        'status',
        'printed_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'printed_at' => 'datetime',
    ];
}
