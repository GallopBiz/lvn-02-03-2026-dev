<?php

namespace App\Models\TransferCertificate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferCertificateStudentStatus extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'tc_student_session_statuses';

    protected $fillable = [
        'student_id',
        'scholar_no',
        'certificate_id',
        'tc_session_name',
        'inactive_from_session',
        'status',
        'reason',
        'marked_by',
        'marked_at',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
    ];
}
