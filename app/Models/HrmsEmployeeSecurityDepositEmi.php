<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeSecurityDepositEmi extends Model
{
    // Use dynamic database connection
    protected $connection = 'dynamic';

    // Set correct table name
    protected $table = 'hrms_employee_security_deposit_emis';

    // Fields that can be mass-assigned
    protected $fillable = [
        'deposit_id',
        'emi_date',
        'emi_amount',
        'status',
    ];

    /**
     * Relationship: belongs to one deposit
     */
    public function deposit()
    {
        return $this->belongsTo(HrmsEmployeeSecurityDeposit::class, 'security_deposit_id');
    }
}
