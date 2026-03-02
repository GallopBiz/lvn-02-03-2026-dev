<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsStaffType extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_staff_type";    

    protected $fillable = [
        'staff_type_name',
        'shift_type_id'
    ];
    public function shiftType()
    {
        return $this->belongsTo(HrmsShiftType::class, 'shift_type_id');
    }
    public function employees()
    {
        return $this->hasMany(HrmsEmployee::class,'staff_type_id');
    }

    public function leaveAllocations()
    {
        return $this->hasMany(HrmsLeaveStaffAllocation::class, 'hrms_staff_type_id');
    }

}
