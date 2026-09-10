<?php

namespace App\Enums;

enum IncidentType: string
{
    case MIC_FAILURE = 'mic_failure';
    case CONNECTION_LOST = 'connection_lost';
    case TIMEOUT = 'timeout';
    case OFF_TOPIC = 'off_topic';
    case INAPPROPRIATE = 'inappropriate';
    case FACILITATOR_INTERVENTION = 'facilitator_intervention';
    case STUDENT_ABSENT = 'student_absent';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::MIC_FAILURE => 'عطل في الميكروفون',
            self::CONNECTION_LOST => 'فقدان الاتصال',
            self::TIMEOUT => 'انتهى الوقت',
            self::OFF_TOPIC => 'خروج عن الموضوع',
            self::INAPPROPRIATE => 'محتوى غير لائق',
            self::FACILITATOR_INTERVENTION => 'تدخل الميسر',
            self::STUDENT_ABSENT => 'غياب الطالب',
            self::OTHER => 'أخرى',
        };
    }
}
