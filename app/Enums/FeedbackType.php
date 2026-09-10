<?php

namespace App\Enums;

enum FeedbackType: string
{
    case NONE = 'none';
    case RECAST = 'recast';
    case CLARIFICATION = 'clarification';
    case MODEL = 'model';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'بدون',
            self::RECAST => 'إعادة صياغة',
            self::CLARIFICATION => 'طلب توضيح',
            self::MODEL => 'نمذجة',
        };
    }
}
