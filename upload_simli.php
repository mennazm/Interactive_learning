<?php
$apiKey = 'svmzjpp7kogegl7z3ev6e7';
$imagePath = 'C:/Users/Elalamia/.gemini/antigravity/brain/71b484df-b8d2-493b-a73b-6c03750c8e52/ahmad_greenscreen_1788981870254.jpg';

$ch = curl_init('https://api.simli.ai/createFace');
$cfile = new CURLFile($imagePath, 'image/jpeg', 'ahmad_green.jpg');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['x-simli-api-key: ' . $apiKey],
    CURLOPT_POSTFIELDS => ['image' => $cfile],
    CURLOPT_TIMEOUT => 30,
]);
$r = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);
echo "HTTP: $code\n";
if ($err) echo "Error: $err\n";
else echo "Response: $r\n";
