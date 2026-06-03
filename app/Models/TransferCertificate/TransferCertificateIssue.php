<?php

namespace App\Models\TransferCertificate;

use App\Models\TransferCertificate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferCertificateIssue extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'tc_certificate_issues';

    protected $fillable = [
        'certificate_id',
        'issue_no',
        'is_duplicate',
        'duplicate_label',
        'reason',
        'pdf_path',
        'issued_by',
        'issued_at',
        'metadata',
    ];

    protected $casts = [
        'is_duplicate' => 'boolean',
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function certificate()
    {
        return $this->belongsTo(TransferCertificate::class, 'certificate_id');
    }
}
