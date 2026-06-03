<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDepartmentAttendanceConfiguration extends Model
{
    use HasFactory;

    protected $table = 'hrms_payroll_department_attendance_configuration';

    protected $fillable = [
        'department_id',
        'biometric_required',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'biometric_required' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(HrmsDepartment::class, 'department_id');
    }
}
