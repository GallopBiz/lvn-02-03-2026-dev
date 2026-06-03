<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollStaffAttendanceConfiguration extends Model
{
    use HasFactory;

    protected $table = 'hrms_payroll_staff_attendance_configuration';

    protected $fillable = [
        'staff_type_id',
        'biometric_required',
    ];

    protected $casts = [
        'staff_type_id' => 'integer',
        'biometric_required' => 'boolean',
    ];

    public function staffType()
    {
        return $this->belongsTo(HrmsStaffType::class, 'staff_type_id');
    }
}
