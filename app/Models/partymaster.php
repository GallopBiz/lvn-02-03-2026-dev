<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class partymaster extends Model
{
    use HasFactory;
    protected $table="partymaster";
    protected $primarykey="id";
    protected $fillable = ['Party_Name','Address','Tax','City','locality','State','PinCode','STDCode','REsidence_ph_no_1','Office_ph_no_1','REsidence_ph_no_2','Office_ph_no_2','Mobile','emailId','Fax_no_','Service_Tax_no_','PAN_no_','CST_no_','TIN_no_','TAN_no_','GST_no_','Contactif','validUpto','Person_Name','Mobile_NO_','Department_','Party_Flag','Remarks','Post'];
}

