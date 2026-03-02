<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dailyattandence extends Model
{
    use HasFactory;
    protected $table="dailattandence";
    protected $primarykey="id";
    protected $fillable = ['Teacher_id','section_name','Attandence_date','class_id'];

    public function Class()
    {
        return $this->belongsTo(Classname::class, 'class_id');
    }
    public function Teacher()
    {
        return $this->belongsTo(HrmsEmployee::class, 'Teacher_id');
    }
}
