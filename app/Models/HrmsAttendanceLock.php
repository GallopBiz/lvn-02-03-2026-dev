<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsAttendanceLock extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_attendance_locks";    

    protected $fillable = ['year', 'month', 'is_locked'];

    public static function isLocked($year, $month)
    {
        return self::where('year', $year)->where('month', $month)->where('is_locked', true)->exists();
    }


}
