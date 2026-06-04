<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsShift extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_shifts";    

    protected $fillable = [
        'shift_type_id',
        'start_time',
        'end_time',
        'late_coming_threshold'
    ];
    public function shiftType()
    {
        return $this->belongsTo(HrmsShiftType::class, 'shift_type_id');
    }
    public function employees()
    {
        return $this->hasMany(HrmsEmployee::class);
    }

    public function employeeShiftHistories()
    {
        return $this->hasMany(HrmsEmployeeShiftHistory::class, 'shift_id');
    }

}
