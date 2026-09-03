<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student_registration extends Model
{
    use HasFactory;
    protected $connection = 'dynamic';
    protected $table="student_registration";
    protected $primarykey="id";
    protected $fillable = ['application_for','form_number','scholar_no','date_of_birth','class_name','student_name','session_name','staff_name','json_str','jsondata','phone_number','mobile_number','inq_mode','status','transfer_status','transferred_at','tc_certificate_id','is_archived','created_at','updated_at'];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('not_archived', function ($query) {
            $query->where(function ($q) {
                $q->where('is_archived', 0)->orWhereNull('is_archived');
            });
        });
    }

    public function emis()
    {
        return $this->hasMany(HrmsEmployeeFeeEMI::class,'student_id');
    }

    public function section()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function scopeActiveForListings($query)
    {
        return $query->where(function ($q) {
            $q->where('is_archived', 0)->orWhereNull('is_archived');
        })->where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', 't');
        })->where(function ($q) {
            $q->whereNull('transfer_status')->orWhere('transfer_status', '!=', 'transferred');
        });
    }

    public function scopeArchived($query)
    {
        return $query->withoutGlobalScope('not_archived')->where('is_archived', 1);
    }
}
