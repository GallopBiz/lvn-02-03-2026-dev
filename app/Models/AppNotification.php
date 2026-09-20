<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'entity_id',
        'entity_type',
        'event_key',
        'created_at',
        'expires_at',
        'is_read',
        'read_at',
        'is_hidden',
        'hidden_at',
        'url',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_hidden' => 'boolean',
        'created_at' => 'datetime',
        'expires_at' => 'date',
        'read_at' => 'datetime',
        'hidden_at' => 'datetime',
    ];
}
