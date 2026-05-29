<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dailyattandence extends Model
{
    use HasFactory;
    protected $table="academic_attendance";
    protected $primarykey="id";
    protected $fillable = ['teacher_id','section_name','attendance_date','class_id','class_name','academic_session','total_students','present_count','absent_count','leave_count','half_day_count'];

    public function Class()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }
    public function Teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'teacher_id');
    }
}
