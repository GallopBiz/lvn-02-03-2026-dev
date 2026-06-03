<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAttendanceConfiguration extends Model
{
    use HasFactory;

    protected $table = 'hrms_payroll_attendance_configuration';

    protected $fillable = [
        'month_number',
        'biometric_required',
    ];

    protected $casts = [
        'month_number' => 'integer',
        'biometric_required' => 'boolean',
    ];
}
