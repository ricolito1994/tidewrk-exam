<?php

namespace App\Enums;

enum ShipmentStatusEnum: string
{
    case PENDING = "pending";
    case BOOKED = "booked";
    case FAILED = "failed";
    case CANCELLED = "cancelled";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}