<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeLeaveBalance extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_leave_balances";    

    protected $fillable = ['employee_id', 'leave_type_id', 'balance'];
}
