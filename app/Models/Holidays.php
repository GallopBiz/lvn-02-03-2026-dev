<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holidays extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="hrms_holidays";
    protected $primarykey="id";

    protected $fillable = [
        'HolidayName',
        'HolidayStartDate',
        'HolidayEndDate',
        'HolidayDescription'
    ];



}
