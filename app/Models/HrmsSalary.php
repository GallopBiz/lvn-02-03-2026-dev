<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsSalary extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_salaries";    

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'health_insurance',
        'retirement_benefits',
        'effective_date',
        'is_active'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }
    public function employeeDeductions()
    {
        return $this->hasMany(HrmsEmployeeDeduction::class,'employee_salary_id');
    }
    public function employeeChilds()
    {
        return $this->hasMany(HrmsEmployeeChild::class,'employee_salary_id');
    }
}
