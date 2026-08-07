<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    //
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PARTIALLY_FAILED = 'partially_failed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
