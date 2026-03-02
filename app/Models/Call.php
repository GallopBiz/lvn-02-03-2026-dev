<?php
 
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class Call extends Model
{
    use HasFactory;

    protected $table = 'call_details';

    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $fillable = [
        'mobile_number', 'call_tag', 'call_note'
    ];
}