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
    protected $fillable = [
        'application_for',
        'form_number',
        'scholar_no',
        'date_of_birth',
        'class_name',
        'section_name',
        'class_id',
        'student_name',
        'session_name',
        'staff_name',
        'driver',
        'password',
        'type',
        'registration_date',
        'json_str',
        'jsondata',
        'phone_number',
        'mobile_number',
        'inq_mode',
        'status',
        'transfer_status',
        'transferred_at',
        'tc_certificate_id',
        'is_archived',
        'next_year',
        'next_year_section',
        'next_year_stream',
        'next_year_subject_combination',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'next_year' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope('not_archived', function ($query) {
            $query->where(function ($q) {
                $q->where('is_archived', 0)->orWhereNull('is_archived');
            });
        });

        static::addGlobalScope('not_next_year', function ($query) {
            $query->where(function ($q) {
                $q->where('next_year', 0)->orWhereNull('next_year');
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
        $connection = $query->getConnection();
        $schema = \Illuminate\Support\Facades\Schema::connection($connection->getName());
        $table = $this->getTable();
        $hasArchived = $schema->hasColumn($table, 'is_archived');
        $hasTransferStatus = $schema->hasColumn($table, 'transfer_status');
        $hasNextYear = $schema->hasColumn($table, 'next_year');

        return $query->where(function ($q) use ($hasArchived, $hasTransferStatus, $hasNextYear) {
            if ($hasArchived) {
                $q->where(function ($sub) {
                    $sub->where('is_archived', 0)->orWhereNull('is_archived');
                });
            }
            if ($hasNextYear) {
                $q->where(function ($sub) {
                    $sub->where('next_year', 0)->orWhereNull('next_year');
                });
            }
            $q->where(function ($sub) {
                $sub->whereNull('status')->orWhere('status', '!=', 't');
            });
            if ($hasTransferStatus) {
                $q->where(function ($sub) {
                    $sub->whereNull('transfer_status')->orWhere('transfer_status', '!=', 'transferred');
                });
            }
        });
    }

    public function scopeArchived($query)
    {
        return $query->withoutGlobalScope('not_archived')->where('is_archived', 1);
    }

    public function scopeNextYear($query)
    {
        return $query->withoutGlobalScope('not_next_year')->where('next_year', 1);
    }

    public function scopeWithNextYear($query)
    {
        return $query->withoutGlobalScope('not_next_year');
    }
}
