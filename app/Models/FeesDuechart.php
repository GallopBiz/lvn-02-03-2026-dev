<?php
 
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class FessDuechartModel extends Model
{
    use HasFactory;

    protected $table = 'course_fees_structure_master';

    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $fillable = [
        'class_name', 'session_name', 'fees_type_name', 'cast_category', 'batch', 'json_str', 'total_above_fees', 'is_delete'
    ];

}