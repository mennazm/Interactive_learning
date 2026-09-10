<?php

namespace App\Services;

use App\Services\Contracts\TTSServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ElevenLabsTTSService implements TTSServiceInterface
{
    /**
     * Synthesize text to speech using ElevenLabs API.
     * Returns base64-encoded MP3 audio, or empty string if not configured.
     *
     * @param string $text The text to convert to speech
     * @param string $voice Voice ID (ignored, uses ELEVENLABS_VOICE_ID from config)
     * @return string Base64-encoded MP3 audio or empty string
     */
    public function synthesize(string $text, string $voice = ''): string
    {
        $apiKey = config('services.elevenlabs.api_key');
        $voiceId = config('services.elevenlabs.voice_id');

        // If not configured, return empty — frontend will fallback to Web Speech API
        if (empty($apiKey) || $apiKey === 'YOUR-KEY-HERE' || empty($voiceId)) {
            Log::info('ElevenLabs not configured, falling back to browser TTS');
            return '';
        }

        // Skip empty text
        if (empty(trim($text))) {
            return '';
        }

        try {
            $response = Http::withHeaders([
                    'xi-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'audio/mpeg',
                ])
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                    'text' => $text,
                    'model_id' => 'eleven_turbo_v2_5',
                    'voice_settings' => [
                        'stability' => 0.5,
                        'similarity_boost' => 0.75,
                        'style' => 0.0,
                        'use_speaker_boost' => true,
                    ],
                ]);

            if ($response->successful()) {
                $audioData = $response->body();
                if (!empty($audioData)) {
                    return base64_encode($audioData);
                }
                Log::error('ElevenLabs returned empty audio body');
            } else {
                Log::error('ElevenLabs API Error: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('ElevenLabs Exception: ' . $e->getMessage());
        }

        return '';
    }
}
