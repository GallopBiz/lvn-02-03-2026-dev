<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leavealocate extends Model
{
    use HasFactory;

    protected $table = 'leave_allocation';
    protected $primaryKey = 'Leave_Allocation_ID';

    protected $fillable = [
        'Staff_Type_ID',
        'CL_Allocation',
        'ML_Allocation',
        'EL_Allocation',
        'Max_CL_At_Time',
        'Max_EL_At_Time',
        'Max_ML_At_Time',
        'PositionName',
        'DepartmentName',
        'short_name',
        'leave_name',
        'is_vacation',
        'is_paid',
        'is_delete'
    ];

    public $timestamps = true;

// In Leavealocate.php model
public function staffType()
{
    return $this->belongsTo(Stafftype::class, 'Staff_Type_ID', 'Staff_Type_ID')->withDefault([
        'Type' => 'N/A'
    ]);
}

public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', 0);
    }


}