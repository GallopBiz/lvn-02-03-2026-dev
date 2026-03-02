<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsLeaveStaffAllocation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "hrms_staff_leave_allocation";

    protected $fillable = ['hrms_staff_type_id', 'leave_type_id', 'is_paid', 'is_vacation', 'max_allowed', 'max_per_instance'];


    /**
     * Define relationship with StaffType model
     */
    public function staffType()
    {
        return $this->belongsTo(HrmsStaffType::class, 'hrms_staff_type_id');
    }

    /**
     * Define relationship with LeaveType model
     */
    public function leaveType()
    {
        return $this->belongsTo(HrmsLeaveType::class, 'leave_type_id');
    }

    /**
     * Check if leave is paid
     */
    public function isPaid()
    {
        return $this->is_paid;
    }

    /**
     * Get max allowed leave days for a specific staff type and leave type
     */
    public function getMaxAllowed()
    {
        return $this->max_allowed;
    }

    /**
     * Get max days per instance for a specific leave type
     */
    public function getMaxPerInstance()
    {
        return $this->max_per_instance;
    }

}
