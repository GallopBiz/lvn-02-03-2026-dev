<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salaries extends Model
{
    use HasFactory;
    protected $table="salaries";
    protected $primarykey="id";

    protected $fillable = [
        'SalaryAmount',
        'EffectiveDate',
        'AttendanceMonth',
        'EmployeeID'
        
    
    ];



}
