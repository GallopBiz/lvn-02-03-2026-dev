<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeFeeEMI extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_student_fee_emis";    

    protected $fillable = [
        'employee_salary_id', 'student_id', 'emi_date', 'emi_amount', 'status', 'paid_in_cash','employee_child_id'
    ];

    public function student()
    {
        return $this->belongsTo(Student_registration::class, 'student_id');
    }

    public function salary()
    {
        return $this->belongsTo(HrmsSalary::class, 'employee_salary_id');
    }
}
