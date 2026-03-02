<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsWorkExperience extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_experiences";    

    protected $fillable = [
        'employee_id',
        'organization_name',
        'job_title',
        'start_date',
        'end_date',
        'roles_responsibilities',
		'total_experience'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
