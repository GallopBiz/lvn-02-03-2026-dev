<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsShiftType extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_shift_types";    

    protected $fillable = [
        'shift_type_name'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($shiftType) {
            if ($shiftType->shifts()->exists()) {
                throw new \Exception("Cannot delete this shift type because it is assigned to one or more shifts or staff.");
            }
        });
    }
    public function shifts()
    {
        return $this->hasMany(HrmsShift::class, 'shift_type_id');
    }
    public function staff()
    {
        return $this->hasMany(HrmsStaffType::class, 'shift_type_id');
    }

}
