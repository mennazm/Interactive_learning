<?php
// Test the TTS endpoint directly through Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test the TTS service directly
$ttsService = $app->make(App\Services\Contracts\TTSServiceInterface::class);
echo "TTS Service class: " . get_class($ttsService) . "\n";

$result = $ttsService->synthesize('Hello, I am Ahmad. How are you today?');
if (!empty($result)) {
    echo "SUCCESS! Audio base64 length: " . strlen($result) . " chars\n";
    echo "First 50 chars: " . substr($result, 0, 50) . "...\n";
} else {
    echo "FAILED - empty result\n";
    // Check logs
    $logFile = __DIR__ . '/storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $lines = array_slice(file($logFile), -10);
        echo "\nLast 10 log lines:\n";
        echo implode('', $lines);
    }
}
