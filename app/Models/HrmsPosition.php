<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrmsPosition extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_positions";

    protected $fillable = [
        'position_name'
    ];

    public function employees()
    {
        return $this->hasMany(HrmsEmployee::class);
    }

}
