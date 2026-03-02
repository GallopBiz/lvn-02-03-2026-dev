<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsLeaveType extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_leave_types";    

    protected $fillable = ['name', 'annual_entitlement', 'reset_month','rules','is_paid','is_carry_forward','apply_before_days','allow_backdate'];
    public function staffTypes()
    {
        return $this->belongsToMany(StaffType::class, 'hrms_staff_leave_allocation')
                    ->withPivot('is_paid', 'max_allowed', 'max_per_instance');
    }

    public function staffAllocations()
    {
        return $this->hasMany(HrmsLeaveStaffAllocation::class, 'leave_type_id');
    }
}
