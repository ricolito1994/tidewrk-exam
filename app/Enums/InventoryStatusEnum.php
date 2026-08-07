<?php

namespace App\Enums;

enum InventoryStatusEnum: string
{
    case PENDING = "pending";
    case RESERVED = "reserved";
    case FAILED = "failed";
    case RELEASED = "released";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}