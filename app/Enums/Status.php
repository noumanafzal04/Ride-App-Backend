<?php

namespace App\Enums;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function activeInactive(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
        ];
    }
}
