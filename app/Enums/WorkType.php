<?php

namespace App\Enums;

enum WorkType: string
{
    case SMS = 'sms';
    case CALL = 'call';

    public function label(): string
    {
        return match ($this) {
            self::SMS => 'SMS',
            self::CALL => 'Qo\'ng\'iroq',
        };
    }
}
