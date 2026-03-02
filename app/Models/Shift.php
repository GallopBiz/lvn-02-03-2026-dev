<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural of the model name
    protected $table = 'shift';

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'Shift_ID';

    // Allow mass assignment for these fields
    protected $fillable = [
        'Shift_Type_ID',
        'Start_Time',
        'End_Time',
        'Late_Coming_Threshold',
    ];

    // Define a relationship with ShiftType if necessary
    public function shiftType()
    {
        return $this->belongsTo(Shifttype::class, 'Shift_Type_ID','Shift_Type_ID');
    }
}
