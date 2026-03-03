<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $fillable = [
        'type',
        'title',
        'status',
        'is_active',
        'message',
        'scheduled_at',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'message' => 'array',
        'is_active' => 'boolean',
        'scheduled_at' => 'dateTime ',
        'started_at' => 'dateTime ',
        'completed_at' => 'dateTime ',
    ];
}
