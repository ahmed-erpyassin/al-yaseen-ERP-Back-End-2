<?php

// Test script for Purchase Reference Invoice Store API
$url = 'http://127.0.0.1:8000/api/v1/purchase/purchase-reference-invoices/create';

$testData = [
    'supplier_id' => 1,
    'currency_id' => 1,
    'due_date' => '2025-10-30',
    'items' => [
        [
            'item_id' => 1,
            'quantity' => 5,
            'unit_price' => 100
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer 1|test-token' // You'll need a real token
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . PHP_EOL;
echo "Response: " . $response . PHP_EOL;
