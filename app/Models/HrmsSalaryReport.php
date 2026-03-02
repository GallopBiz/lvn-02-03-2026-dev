<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmsSalaryReport extends Model
{
    protected $table = 'hrms_payrolls';

    protected $casts = [
        'deductions' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class, 'employee_id');
    }
}
