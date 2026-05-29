<?php

namespace App\Models\Academic;

use App\Models\Classname;
use App\Models\HrmsEmployee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'academic_attendance';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'class_name',
        'section_name',
        'attendance_date',
        'academic_session',
        'total_students',
        'present_count',
        'absent_count',
        'leave_count',
        'half_day_count',
        'pl_count',
        'is_locked',
        'locked_at',
        'locked_by',
        'is_delete',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(AttendanceDetail::class, 'attendance_id');
    }

    public function className()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'teacher_id');
    }
}
