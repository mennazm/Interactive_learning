<?php
// Test ElevenLabs TTS
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['ELEVENLABS_API_KEY'] ?? 'NOT SET';
$voiceId = $_ENV['ELEVENLABS_VOICE_ID'] ?? 'NOT SET';

echo "API Key: " . substr($apiKey, 0, 10) . "...\n";
echo "Voice ID: $voiceId\n\n";

if ($apiKey === 'NOT SET' || $voiceId === 'NOT SET') {
    echo "ERROR: Keys not set!\n";
    exit(1);
}

echo "Testing ElevenLabs API...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.elevenlabs.io/v1/text-to-speech/$voiceId",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        "xi-api-key: $apiKey",
        "Content-Type: application/json",
        "Accept: audio/mpeg",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'text' => 'Hello! I am Ahmad. Welcome to our English speaking practice.',
        'model_id' => 'eleven_turbo_v2_5',
        'voice_settings' => [
            'stability' => 0.5,
            'similarity_boost' => 0.75,
        ],
    ]),
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
} elseif ($httpCode === 200) {
    $size = strlen($response);
    echo "SUCCESS! Audio size: $size bytes\n";
    // Save test audio
    file_put_contents(__DIR__ . '/storage/app/test_ahmad.mp3', $response);
    echo "Saved to storage/app/test_ahmad.mp3\n";
} else {
    echo "ERROR Response: $response\n";
}
