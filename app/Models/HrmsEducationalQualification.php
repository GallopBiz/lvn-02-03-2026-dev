<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEducationalQualification extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "hrms_employee_educations";

    protected $fillable = [
        'employee_id',
        'qualification',
        'specialization',
        'institution_name',
        'year_of_graduation',
        'certification'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
