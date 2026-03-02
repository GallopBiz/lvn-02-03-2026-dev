<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class HrmsEmployeeDeductionHistory extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_deduction_history";  
    protected $fillable = [
        'employee_id',
        'basic_deduction_id',
        'employee_salary_id',
        'amount',
        'month',
        'year'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class,'employee_id');
    }
    public function basic_deduction()
    {
        return $this->belongsTo(HrmsBasicDeduction::class,'basic_deduction_id');
    }
}
