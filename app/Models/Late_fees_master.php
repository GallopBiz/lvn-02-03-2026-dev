<?php
 
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class Late_fees_master extends Model
{
    use HasFactory;

    protected $table = 'late_fees_master';

    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $fillable = [
        'id','late_fees_amount', 'from_amount', 'to_amount', 'upto'
    ];

}