<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SMS extends Model
{
    protected $table = 's_m_s';

    protected $fillable = [
        'work_id',
        'phone_number_id',
        'status',
        'retry',
        'response',
    ];

    protected $casts = [
        'status' => ItemStatus::class,
        'response' => 'array',
        'retry' => 'integer',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function phoneNumber(): BelongsTo
    {
        return $this->belongsTo(PhoneNumber::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', ItemStatus::PENDING);
    }
}
