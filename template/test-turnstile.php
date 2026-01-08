<?php
// TEST DE TURNSTILE - Verificar credenciales
header('Content-Type: application/json');

$secret = '0x4AAAAAACCRj1iC8bSsrxnSFf7Bds';
$token = $_POST['token'] ?? '';

if (empty($token)) {
    die(json_encode(['error' => 'No token provided']));
}

$ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo json_encode([
    'http_code' => $http_code,
    'curl_error' => $error,
    'raw_response' => $response,
    'parsed' => json_decode($response, true),
    'secret_used' => substr($secret, 0, 15) . '...',
    'token_length' => strlen($token)
]);
