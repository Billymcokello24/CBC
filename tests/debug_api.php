<?php
/**
 * Quick API Debug Script
 */

$baseUrl = 'http://localhost:8000/api/v1';

// Login first
echo "=== LOGGING IN ===\n";
$ch = curl_init("$baseUrl/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'admin@cbcplatform.com',
    'password' => 'password'
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Code: $httpCode\n";
$loginData = json_decode($response, true);
echo "Login Response: " . json_encode($loginData, JSON_PRETTY_PRINT) . "\n\n";

if (!isset($loginData['data']['token'])) {
    echo "ERROR: No token in login response\n";
    exit(1);
}

$token = $loginData['data']['token'];
echo "Token obtained: " . substr($token, 0, 20) . "...\n\n";

// Try to create school
echo "=== CREATING SCHOOL ===\n";
$ch = curl_init("$baseUrl/schools");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    "Authorization: Bearer $token"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'Test Primary School',
    'code' => 'TPS-' . time(),
    'school_type_id' => 1,
    'school_level_id' => 1,
    'email' => 'test@school.com',
    'phone' => '+254700000000',
    'county' => 'Nairobi',
    'gender_type' => 'mixed',
    'boarding_type' => 'day'
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Create School HTTP Code: $httpCode\n";
echo "Response: " . $response . "\n";
