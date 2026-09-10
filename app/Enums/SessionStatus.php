<?php

namespace App\Enums;

enum SessionStatus: string
{
    case NOT_STARTED = 'not_started';
    case MIC_CHECK = 'mic_check';
    case INTRO = 'intro';
    case VOCAB = 'vocab';
    case CONVERSATION = 'conversation';
    case FEEDBACK = 'feedback';
    case CLOSING = 'closing';
    case COMPLETED = 'completed';
    case INTERRUPTED = 'interrupted';

    public function label(): string
    {
        return match($this) {
            self::NOT_STARTED => 'لم تبدأ',
            self::MIC_CHECK => 'فحص الميكروفون',
            self::INTRO => 'المقدمة',
            self::VOCAB => 'عرض المفردات',
            self::CONVERSATION => 'المحادثة',
            self::FEEDBACK => 'التغذية الراجعة',
            self::CLOSING => 'الخاتمة',
            self::COMPLETED => 'مكتملة',
            self::INTERRUPTED => 'مقطوعة',
        };
    }

    /**
     * Get phase duration in seconds.
     */
    public function durationSeconds(): int
    {
        return match($this) {
            self::INTRO => 5 * 60,          // 5 minutes
            self::VOCAB => 5 * 60,          // 5 minutes
            self::CONVERSATION => 20 * 60,  // 20 minutes
            self::FEEDBACK => 7 * 60,       // 7 minutes
            self::CLOSING => 3 * 60,        // 3 minutes
            default => 0,
        };
    }

    /**
     * Get the next phase in order.
     */
    public function next(): ?self
    {
        return match($this) {
            self::NOT_STARTED => self::INTRO,
            self::MIC_CHECK => self::INTRO,
            self::INTRO => self::VOCAB,
            self::VOCAB => self::CONVERSATION,
            self::CONVERSATION => self::FEEDBACK,
            self::FEEDBACK => self::CLOSING,
            self::CLOSING => self::COMPLETED,
            default => null,
        };
    }

    /**
     * Get ordered phases for the session timeline.
     */
    public static function sessionPhases(): array
    {
        return [
            self::INTRO,        // 5 min
            self::VOCAB,        // 5 min
            self::CONVERSATION, // 20 min
            self::FEEDBACK,     // 7 min
            self::CLOSING,      // 3 min
        ];
    }

    public static function totalDurationSeconds(): int
    {
        return 40 * 60; // 40 minutes
    }
}

