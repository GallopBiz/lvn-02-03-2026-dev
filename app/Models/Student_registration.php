<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student_registration extends Model
{
    use HasFactory;
    protected $table="student_registration";
    protected $primarykey="id";
    protected $fillable = ['application_for','form_number','scholar_no','date_of_birth','class_name','student_name','session_name','staff_name','json_str','jsondata','phone_number','mobile_number','inq_mode','status','created_at','updated_at'];

    public function emis()
    {
        return $this->hasMany(HrmsEmployeeFeeEMI::class,'student_id');
    }

    public function section()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
