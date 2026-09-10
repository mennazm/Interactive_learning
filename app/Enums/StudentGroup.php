<?php

namespace App\Enums;

enum StudentGroup: string
{
    case EXPERIMENTAL = 'experimental';
    case CONTROL = 'control';

    public function label(): string
    {
        return match($this) {
            self::EXPERIMENTAL => 'مجموعة تجريبية',
            self::CONTROL => 'مجموعة ضابطة',
        };
    }
}
