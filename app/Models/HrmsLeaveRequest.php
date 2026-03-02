<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsLeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "hrms_leave_requests";

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'attachment_path',
        'status',
        'is_half_day',
        'half_day_type',
        'approved_by'
    ];

    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(HrmsLeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(HrmsEmployee::class, 'approved_by');
    }

    public function datewiseEntries()
    {
        return $this->hasMany(HrmsLeaveDatewiseEntry::class, 'leave_request_id');
    }
}