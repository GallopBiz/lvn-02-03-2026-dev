<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferCertificate extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'tc_certificates';

    protected $fillable = [
        'certificate_no',
        'student_id',
        'scholar_no',
        'session_name',
        'class_name',
        'section_name',
        'student_name',
        'status',
        'issue_date',
        'leaving_date',
        'reason_for_leaving',
        'conduct',
        'remarks',
        'tc_form_data',
        'snapshot',
        'generated_by',
        'last_issue_id',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'tc_form_data' => 'array',
        'issue_date' => 'date',
        'leaving_date' => 'date',
    ];

    public function issues()
    {
        return $this->hasMany(TransferCertificate\TransferCertificateIssue::class, 'certificate_id');
    }

    public function latestIssue()
    {
        return $this->belongsTo(TransferCertificate\TransferCertificateIssue::class, 'last_issue_id');
    }
}
