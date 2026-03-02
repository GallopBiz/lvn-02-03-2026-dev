<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmergencyContact extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_emergency_contacts";    

    protected $fillable = [
        'employee_id',
        'contact_name',
        'relationship',
        'phone_number',
        'alternative_phone_number'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


}
