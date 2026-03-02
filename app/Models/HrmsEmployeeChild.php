<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeChild extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_childrens";    

    protected $fillable = [
       'employee_id', 'student_id', 'is_active', 'fee_amount','employee_salary_id','emi_amount'
    ];

    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }
    public function salary()
    {
        return $this->belongsTo(HrmsSalary::class, 'employee_salary_id');
    }

    public function student()
    {
        return $this->belongsTo(Student_registration::class, 'student_id');
    }
    
    
}
