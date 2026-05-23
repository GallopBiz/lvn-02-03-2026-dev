<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicRemark extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';
    protected $table = 'academic_remarksmaster';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'remark',
        'not_show',
        'is_delete',
    ];
}
