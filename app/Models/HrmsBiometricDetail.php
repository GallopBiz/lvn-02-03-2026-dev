<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsBiometricDetail extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_biometric_details";    

    protected $fillable = [
        'employee_id', 'card_number', 'ess_emp_code', 'device_code',
    ];

    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
