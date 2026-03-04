<?php

namespace App\Enums;

enum WorkStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Kutilmoqda',
            self::PROCESSING => 'Jarayonda',
            self::COMPLETED => 'Tugallangan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
        };
    }
}
