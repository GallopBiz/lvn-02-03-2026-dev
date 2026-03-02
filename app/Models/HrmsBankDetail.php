<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsBankDetail extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_bank_details";    

    protected $fillable = [
        'employee_id', 'bank_name', 'account_number', 
        'ifsc_code', 'branch_name', 'pan_number',
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
