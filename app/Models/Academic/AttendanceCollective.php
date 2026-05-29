<?php

namespace App\Models\Academic;

use App\Models\Classname;
use App\Models\HrmsEmployee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCollective extends Model
{
    use HasFactory;

    protected $table = 'academic_attendance_collectives';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'class_name',
        'section_name',
        'exam_id',
        'exam_name',
        'academic_session',
        'start_date',
        'end_date',
        'working_days',
        'student_count',
        'is_delete',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(AttendanceCollectiveDetail::class, 'collective_id');
    }

    public function teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'teacher_id');
    }

    public function className()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }
}
