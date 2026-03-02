<?php
 
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class Inquiry_registration extends Model
{
    use HasFactory;

    protected $table = 'inquiry_registration';

    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $fillable = [
        'application_for', 'form_number', 'date_of_birth', 'class_name', 'student_name', 'session_name', 'json_str', 'phone_number', 'mobile_number', 'inq_mode', 'status' , 'save_status'
    ];
}