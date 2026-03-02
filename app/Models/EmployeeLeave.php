<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    use HasFactory;

    protected $table = 'employeeleaves'; 
    protected $primaryKey = 'Emp_ID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'CL_Balance',
        'ML_Balance',
        'EL_Balance',
        'is_delete'
    ];

    public $timestamps = true;

    // Scope to get only non-deleted records
    public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', 0);
    }

//     public function employee()
//     {
//         return $this->belongsTo(Employees::class, 'Emp_ID', 'EmployeeID');
//     }
}
