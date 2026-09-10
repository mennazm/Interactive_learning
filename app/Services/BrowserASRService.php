<?php

namespace App\Services;

use App\Services\Contracts\ASRServiceInterface;

class BrowserASRService implements ASRServiceInterface
{
    /**
     * Transcribe audio to text.
     * Since we use Web Speech API in browser, this just returns the text.
     */
    public function transcribe(string $audioData, string $language = 'en-US'): array
    {
        // Expecting $audioData to actually be the transcribed string from frontend
        return [
            'text' => $audioData,
            'confidence' => 1.0
        ];
    }
}
