<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsLeaveResetHistory extends Model
{
    use HasFactory;

    protected $table = 'hrms_leave_reset_histories';

    protected $fillable = [
        'created_by',
        'ip_address',
        'user_agent',
        'message',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
