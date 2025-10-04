<?php

/**
 * Test script to call the Purchase Reference Invoice API directly
 */

// Test data
$testData = [
    'customer_id' => 1,
    'supplier_id' => 1,
    'currency_id' => 1,
    'journal_number' => 17,
    'company_id' => 1,
    'branch_id' => 1,
    'employee_id' => 1,
    'journal_id' => 1,
    'due_date' => '2025-12-30',
    'supplier_email' => 'carolyne.luettgen@example.org',
    'licensed_operator' => 'fuudtdsufvyvddqamniihdsa',
    'cash_paid' => 16,
    'checks_paid' => 57,
    'allowed_discount' => 8,
    'total_without_tax' => 51,
    'tax_percentage' => 24,
    'tax_amount' => 45,
    'total_amount' => 40,
    'remaining_balance' => 1,
    'exchange_rate' => 89,
    'total_foreign' => 21,
    'total_local' => 26,
    'is_tax_applied_to_currency' => true,
    'discount_percentage' => 4,
    'discount_amount' => 68,
    'notes' => 'qtqxbajwbpilpmufinllw',
    'items' => [
        [
            'item_id' => 1,
            'quantity' => 2,
            'unit_price' => 10.50,
            'notes' => 'Test item'
        ]
    ]
];

// Convert to JSON
$jsonData = json_encode($testData);

echo "=== Testing Purchase Reference Invoice API ===\n\n";
echo "Test Data:\n";
echo $jsonData . "\n\n";

// You can use this data to test with Postman or curl
echo "Curl command to test:\n";
echo "curl -X POST http://localhost/al-yaseen-ERP-Back-End-2/public/api/v1/purchase/purchase-reference-invoices/debug-create \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\\n";
echo "  -d '" . $jsonData . "'\n\n";

echo "Or test the regular endpoint:\n";
echo "curl -X POST http://localhost/al-yaseen-ERP-Back-End-2/public/api/v1/purchase/purchase-reference-invoices/create \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\\n";
echo "  -d '" . $jsonData . "'\n\n";

echo "Note: Replace YOUR_TOKEN_HERE with a valid authentication token\n";
echo "You can get a token by logging in through the API first.\n";
