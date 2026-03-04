<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneNumber extends Model
{
    public const MAX_FAILED_COUNT = 3;

    protected $fillable = [
        'number',
        'is_active',
        'failed_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'failed_count' => 'integer',
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

    public function incrementFailed(): void
    {
        $this->increment('failed_count');

        if ($this->failed_count >= self::MAX_FAILED_COUNT) {
            $this->update(['is_active' => false]);
        }
    }

    public function resetFailed(): void
    {
        if ($this->failed_count > 0) {
            $this->update(['failed_count' => 0]);
        }
    }
}
