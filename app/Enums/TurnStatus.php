<?php

namespace App\Enums;

enum TurnStatus: string
{
    case WAITING_INPUT = 'waiting_input';
    case PROCESSING = 'processing';
    case GENERATING = 'generating';
    case DELIVERING = 'delivering';
    case AWAITING_RETRY = 'awaiting_retry';

    public function label(): string
    {
        return match($this) {
            self::WAITING_INPUT => 'في انتظار الإدخال',
            self::PROCESSING => 'قيد المعالجة',
            self::GENERATING => 'توليد الرد',
            self::DELIVERING => 'إيصال الرد',
            self::AWAITING_RETRY => 'بانتظار المحاولة مرة أخرى',
        };
    }
}
