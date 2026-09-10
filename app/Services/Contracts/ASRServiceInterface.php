<?php

namespace App\Services\Contracts;

interface ASRServiceInterface
{
    /**
     * Transcribe audio to text.
     *
     * @param string $audioData
     * @param string $language
     * @return array Contains 'text' and 'confidence'
     */
    public function transcribe(string $audioData, string $language = 'en-US'): array;
}
