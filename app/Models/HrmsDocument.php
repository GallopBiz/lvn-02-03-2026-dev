<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsDocument extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_employee_documents";    

    protected $fillable = [
        'employee_id',
        'document_type',
        'is_uploaded',
        'file_path'
    ];
    public function employee()
    {
        return $this->belongsTo(HrmsEmployee::class);
    }


}
