<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeShiftHistory extends Model
{
    use HasFactory;

    protected $table = 'hrms_employee_shift_history';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'effective_from',
        'effective_to',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class, 'employee_id');
    }

    public function shift()
    {
        return $this->belongsTo(HrmsShift::class, 'shift_id');
    }
}
