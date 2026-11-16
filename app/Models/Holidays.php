<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holidays extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = "hrms_holidays";
    protected $primaryKey = "HolidayID";

    protected $fillable = [
        'HolidayName',
        'HolidayStartDate',
        'HolidayEndDate',
        'HolidayDescription',
        'for_hrms',
        'for_academic',
    ];

    protected $casts = [
        'for_hrms' => 'boolean',
        'for_academic' => 'boolean',
    ];

    public function scopeForAcademic($query)
    {
        return $query->where('for_academic', 1);
    }

    public function scopeForHrms($query)
    {
        return $query->where(function ($q) {
            $q->where('for_hrms', 1)
                ->orWhere('for_academic', 1);
        });
    }



}
