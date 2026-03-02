<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsPayroll extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_payrolls";    

    protected $fillable = [
        'employee_id', 'gross_salary', 'basic_salary','earn_salary', 'net_salary', 'deductions', 'hra', 'da', 'month', 'epfa', 'esic', 'approved_leaves','unapproved_leaves','lwp_days', 'late_deduction_amount', 'year','generated_at'
    ];

    protected $casts = [
		'basic_salary' => 'float',
		'hra' => 'float',
		'da' => 'float',
		'epfa' => 'float',
        'esic' => 'float',
		'late_deduction_amount' => 'float',
        'deductions' => 'array',
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }
}
