<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsDepartment extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_departments";    

    protected $fillable = [
        'department_name'
    ];

    public function employees()
    {
        return $this->hasMany(HrmsEmployee::class);
    }

}
