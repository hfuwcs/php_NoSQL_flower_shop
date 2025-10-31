<?php

// Test script để debug Stripe webhook

$url = 'http://127.0.0.1:8000/stripe/webhook';

// Fake webhook payload
$payload = json_encode([
    'type' => 'payment_intent.succeeded',
    'data' => [
        'object' => [
            'id' => 'pi_test_123',
            'metadata' => [
                'order_id' => '67244c8bbe37d15be6048584'
            ]
        ]
    ]
]);

echo "Testing webhook endpoint: $url\n";
echo "Payload: $payload\n\n";

// Send POST request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
