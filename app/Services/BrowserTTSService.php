<?php

namespace App\Services;

use App\Services\Contracts\TTSServiceInterface;

class BrowserTTSService implements TTSServiceInterface
{
    /**
     * Synthesize text to speech.
     * Placeholder since TTS is handled by the browser. Returns text.
     */
    public function synthesize(string $text, string $voice = 'en-US-Standard-B'): string
    {
        return $text;
    }
}
