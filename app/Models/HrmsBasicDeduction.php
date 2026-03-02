<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsBasicDeduction extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_basic_deductions";    

    protected $fillable = [
        'name',
        'amount',
        'description'
    ];

    public function employees()
    {
        return $this->belongsToMany(HrmsEmployee::class, 'hrms_employee_deductions')
                    ->withTimestamps();
    }
    
}
