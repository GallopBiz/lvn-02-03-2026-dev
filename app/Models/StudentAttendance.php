<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;
    protected $table="academic_attendance_details";
    protected $primarykey="id";
    protected $fillable = ['student_id','attendance_id','status'];

    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student_registration::class, 'student_id');
    }
}
