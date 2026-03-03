<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SMS extends Model
{
    protected $fillable = [
        'work_id',
        'phone_number_id',
        'status',
        'retry',
        'response'
    ];

    protected $casts = [
        'response' => 'array'
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function phoneNumber(): BelongsTo
    {
        return $this->belongsTo(PhoneNumber::class);
    }
}
