<?php

declare(strict_types=1);

namespace App\Enum;

enum BookPromotionStatus: string
{
    case None = 'None';
    case Basic = 'Basic';
    case Pro = 'Pro';

    public static function toArray(): array
    {
        return [
            self::None->value,
            self::Basic->value,
            self::Pro->value,
        ];
    }
}
