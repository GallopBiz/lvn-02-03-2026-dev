<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeSecurityDeposit extends Model
{
    // Use dynamic database connection
    protected $connection = 'dynamic';

    // Set correct table name
    protected $table = 'hrms_employee_security_deposits';

    // Fields that can be mass-assigned
    protected $fillable = [
        'employee_id',
        'security_deposit',
        'emi_amount',
        'duration_months',
        'status',
        'start_date',
        'end_date',
        'description',
    ];

    /**
     * Relationship: belongs to employee
     */
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class, 'employee_id');
    }

    /**
     * Relationship: has many EMIs
     */
    public function emis()
    {
        return $this->hasMany(HrmsEmployeeSecurityDepositEmi::class, 'security_deposit_id');
    }
}
