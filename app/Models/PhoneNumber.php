<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneNumber extends Model
{
    protected $fillable = [
        'number',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function smses(): HasMany
    {
        return $this->hasMany(SMS::class);
    }
}
