<?php

namespace App\Models\TransferCertificate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferCertificateAuditLog extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'tc_audit_logs';

    protected $fillable = [
        'certificate_id',
        'student_id',
        'scholar_no',
        'action',
        'description',
        'old_values',
        'new_values',
        'performed_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
