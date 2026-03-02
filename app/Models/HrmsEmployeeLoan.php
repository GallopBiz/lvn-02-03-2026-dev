<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "hrms_employee_loans";

    protected $fillable = [
        'employee_id',
        'loan_amount',
        'emi_amount',
        'interest_rate',
        'start_date',
        'end_date',
        'description',
        'is_active',
        'duration_months',
        'original_duration_months',
        'payment_mode',
        'refund_amount',
        'refund_date',
        'status'
    ];

    /**
     * Relationship: Loan belongs to an Employee
     */
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class, 'employee_id');
    }

    /**
     * Relationship: Loan has many EMIs
     */
    public function emis()
    {
        return $this->hasMany(HrmsEmployeeLoanEmi::class, 'loan_id');
    }
}
