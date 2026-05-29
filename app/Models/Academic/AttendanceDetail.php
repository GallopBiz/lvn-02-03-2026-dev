<?php

namespace App\Models\Academic;

use App\Models\Student_registration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDetail extends Model
{
    use HasFactory;

    protected $table = 'academic_attendance_details';

    public $timestamps = false;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'status',
        'remarks',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function student()
    {
        return $this->belongsTo(Student_registration::class, 'student_id');
    }
}
