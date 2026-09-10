<?php

namespace App\Services\Contracts;

interface TTSServiceInterface
{
    /**
     * Synthesize text to speech.
     *
     * @param string $text
     * @param string $voice
     * @return string Audio URL or base64 representation
     */
    public function synthesize(string $text, string $voice = 'en-US-Standard-B'): string;
}
