<?php

namespace App\Models\TransferCertificate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferCertificateTemplate extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'tc_templates';

    protected $fillable = [
        'name',
        'view_path',
        'paper_size',
        'orientation',
        'is_default',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'settings' => 'array',
    ];
}
