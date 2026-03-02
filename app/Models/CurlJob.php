<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurlJob extends Model
{
    use HasFactory;

    protected $table = "transaction_logs";
    protected $primaryKey = "id";

    protected $fillable = [
        'user_id',
        'log_date',
        'in_time',
        'out_time',
    ];
}
