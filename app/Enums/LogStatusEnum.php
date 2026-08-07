<?php

namespace App\Enums;

enum LogStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case RETRYING = 'retrying';
    case COMPENSATED = 'compensated';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}