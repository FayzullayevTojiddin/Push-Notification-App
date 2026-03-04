<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Enums\WorkStatus;
use App\Enums\WorkType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'completed_at',
    ];

    protected $casts = [
        'type' => WorkType::class,
        'status' => WorkStatus::class,
        'message' => 'array',
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function smses(): HasMany
    {
        return $this->hasMany(SMS::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getProgressAttribute(): float
    {
        if ($this->type === WorkType::SMS) {
            $total = $this->smses()->count();
            if ($total === 0) return 0;
            $done = $this->smses()->whereIn('status', [ItemStatus::SENT, ItemStatus::FAILED])->count();
            return round(($done / $total) * 100, 1);
        }

        $total = $this->calls()->count();
        if ($total === 0) return 0;
        $done = $this->calls()->whereIn('status', [ItemStatus::SENT, ItemStatus::FAILED])->count();
        return round(($done / $total) * 100, 1);
    }
}
