<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsStatutoryInformation extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_statutory_info";    

    protected $fillable = [
        'employee_id',
        'esic_number',
        'epf_number',
        'uan_number',
        'samagra_id',
        'pan_number',
        'aadhar_number',
        'ayushman_number'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
