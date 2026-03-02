<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeAddress extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_addresses";    

    protected $fillable = [
        'employee_id',
        'address_type',
        'address_line',
        'city',
        'tehsil',
        'district',
        'pin_code'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
