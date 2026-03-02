<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeAttendance extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_attendance";    

    protected $fillable = [
        'ess_emp_code',
        'log_date',
        'in_time',
        'out_time',
        'employee_id',
		'entry_type',
        'status'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
