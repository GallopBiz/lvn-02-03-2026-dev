<?php

use App\Models\Classes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSessionTransfer extends Model
{
    use HasFactory;

    protected $table = 'student_session_transfers'; // Ensure this matches your database table name

    protected $fillable = [
        'student_id',
        'previous_class_id',
        'new_class_id',
        'session_year',
        'status',
    ];

    // Define relationship with StudentRegistration model
    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id');
    }

    // Define relationship with Classes model
    public function previousClass()
    {
        return $this->belongsTo(Classes::class, 'previous_class_id');
    }

    public function newClass()
    {
        return $this->belongsTo(Classes::class, 'new_class_id');
    }
}
