<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeDeduction extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_deductions";    

    protected $fillable = [
       'employee_id', 'basic_deduction_id','employee_salary_id','manual_amount'
    ];

    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }

    public function basicDeduction()
    {
        return $this->belongsTo(HrmsBasicDeduction::class);
    }
    
    public function salary()
    {
        return $this->belongsTo(HrmsSalary::class, 'employee_salary_id');
    }
    
}
