<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalAssessmentMaster extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'academic_internal_assessment_master';

    protected $fillable = [
        'assessment_code',
        'assessment_name',
        'description',
        'display_order',
        'is_active',
        'is_delete',
    ];
}
