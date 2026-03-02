<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusStaf extends Model
{
    use HasFactory;
    protected $table="busstaff";
    protected $primarykey="id";
    protected $fillable = ['role', 'ename', 'mobile_number', 'aadhar_number', 'sssmid', 'current_address', 'parmanent_address', 'license_no',
     'license_expire', 'license_lssue', 'voter_id_no', 'joining_date', 'leaving_date', 'leaving_date1', 'call_no',
    'offical_mobile_no', 'remarks', 'healthstatus'];

}
