<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLoan;

class HrmsSecurityDepositeRefund extends Model
{
    protected $table = 'hrms_security_deposite_refunds';

    // Set the correct primary key
    protected $primaryKey = 'id'; // assuming your primary column is still `id`
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'loan_id',
        'refund_amount',
        'refund_date',
        'payment_mode',
        'description',
        'status',
    ];

    // ✅ Relation to HrmsEmployee
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class, 'employee_id');
    }

    // ✅ Relation to HrmsEmployeeLoan
    public function loan()
    {
        return $this->belongsTo(HrmsEmployeeLoan::class, 'loan_id');
    }
}
