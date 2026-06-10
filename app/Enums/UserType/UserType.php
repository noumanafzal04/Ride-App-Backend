<?php

namespace App\Enums\UserType;

enum UserType: string
{
    case USER = 'user';
    case DRIVER = 'driver';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function userDriver(): array
    {
        return [
            self::USER->value,
            self::DRIVER->value,
        ];
    }
}
