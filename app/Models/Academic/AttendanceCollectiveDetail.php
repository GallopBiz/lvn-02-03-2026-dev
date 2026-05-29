<?php

namespace App\Models\Academic;

use App\Models\Student_registration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCollectiveDetail extends Model
{
    use HasFactory;

    protected $table = 'academic_attendance_collective_details';

    public $timestamps = false;

    protected $fillable = [
        'collective_id',
        'student_id',
        'student_name',
        'scholar_no',
        'present_days',
        'working_days',
        'attendance_percentage',
        'is_below_minimum',
    ];

    protected $casts = [
        'present_days' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
        'is_below_minimum' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student_registration::class, 'student_id');
    }
}
