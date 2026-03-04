<?php

namespace App\Enums;

enum ItemStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Kutilmoqda',
            self::PROCESSING => 'Jarayonda',
            self::SENT => 'Yuborilgan',
            self::FAILED => 'Xatolik',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::SENT => 'success',
            self::FAILED => 'danger',
        };
    }
}
