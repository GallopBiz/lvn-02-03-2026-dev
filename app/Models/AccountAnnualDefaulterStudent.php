<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountAnnualDefaulterStudent extends Model
{
    use HasFactory;

    protected $table = 'account_annual_defaulter_student'; // Define table name

    protected $primaryKey = 'id'; // Define primary key

    public $timestamps = false; // Disable timestamps if not needed

    protected $fillable = [
        'scholar_no',
        'class',
        'section',
        'student_name',
        'admission_fees',
        'alumni_fees',
        'bus_fees',
        'caution_money',
        'lunch_fees',
        'tuition_fees',
        'total',
    ];
}
