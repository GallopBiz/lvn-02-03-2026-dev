<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTcFile extends Model
{
    protected $connection = 'dynamic';
    protected $table = 'student_tc_files';
    protected $fillable = ['scholar_no', 'session_name', 'file_path', 'original_filename', 'file_size', 'mime_type', 'uploaded_by', 'uploaded_at'];
    protected $casts = ['uploaded_at' => 'datetime'];
}
