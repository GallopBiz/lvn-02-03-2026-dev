<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class rto_paper extends Model
{
    use HasFactory;
    protected $table="rto_paper";
    protected $primarykey="id";
    protected $fillable = ['Renewal_Date', 'Next_Renewal_Date', 'Registration_Date', 'Vehicle', 'RTO_Paper_Name', 'Transfer_date', 'Document', 'Reminder_Frequency','image'];
}
