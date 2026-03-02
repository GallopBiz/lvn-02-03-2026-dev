<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    use HasFactory;
    protected $table="academic_class_subject";
    protected $primaryKey="id";
    protected $fillable = ['subject_id','class_id','stream_id'];

    public $timestamps = true;

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }
}
