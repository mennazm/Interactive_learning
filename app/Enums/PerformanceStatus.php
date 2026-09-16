<?php

namespace App\Enums;

enum PerformanceStatus: string
{
    case ACHIEVED = 'achieved';
    case PARTIALLY_ACHIEVED = 'partially_achieved';
    case NOT_ACHIEVED = 'not_achieved';
    case NOT_ASSESSABLE = 'not_assessable';

    public function label(): string
    {
        return match($this) {
            self::ACHIEVED => 'متحقق',
            self::PARTIALLY_ACHIEVED => 'متحقق جزئياً',
            self::NOT_ACHIEVED => 'لم يتحقق بعد',
            self::NOT_ASSESSABLE => 'غير قابل للتقييم',
        };
    }

    public function emoji(): string
    {
        return match($this) {
            self::ACHIEVED => '✅',
            self::PARTIALLY_ACHIEVED => '⚠️',
            self::NOT_ACHIEVED => '❌',
            self::NOT_ASSESSABLE => '🔧',
        };
    }
}
