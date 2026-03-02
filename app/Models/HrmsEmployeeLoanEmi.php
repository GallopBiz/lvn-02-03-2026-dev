<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeLoanEmi extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_loan_emis";    

    protected $fillable = [
        'loan_id', 'emi_date', 'emi_amount', 'principal_component', 'interest_component', 'status', 'payment_mode', 'paid_amount','notes'
    ];

    public function loan()
    {
        return $this->belongsTo(HrmsEmployeeLoan::class);
    }
}
