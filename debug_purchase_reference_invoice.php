<?php

/**
 * Debug script for Purchase Reference Invoice creation
 * This script helps identify why the validation is failing
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Company;
use Modules\Suppliers\Models\Supplier;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Inventory\Models\Item;
use Modules\Companies\Models\Branch;
use Modules\Billing\Models\Journal;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Purchase Reference Invoice Debug Script ===\n\n";

// Test data from the user's request
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

echo "1. Checking database connections...\n";
try {
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "2. Checking required records exist...\n";

// Check Company
echo "Checking Company ID: {$testData['company_id']}\n";
$company = Company::find($testData['company_id']);
if ($company) {
    echo "✓ Company found: {$company->title}\n";
} else {
    echo "✗ Company not found\n";
    echo "Available companies:\n";
    $companies = Company::select('id', 'title')->limit(5)->get();
    foreach ($companies as $comp) {
        echo "  - ID: {$comp->id}, Title: {$comp->title}\n";
    }
}

// Check Supplier
echo "\nChecking Supplier ID: {$testData['supplier_id']}\n";
$supplier = Supplier::find($testData['supplier_id']);
if ($supplier) {
    echo "✓ Supplier found: {$supplier->supplier_name_ar} / {$supplier->supplier_name_en}\n";
} else {
    echo "✗ Supplier not found\n";
    echo "Available suppliers:\n";
    $suppliers = Supplier::select('id', 'supplier_name_ar', 'supplier_name_en')->limit(5)->get();
    foreach ($suppliers as $supp) {
        echo "  - ID: {$supp->id}, Name: {$supp->supplier_name_ar} / {$supp->supplier_name_en}\n";
    }
}

// Check Currency
echo "\nChecking Currency ID: {$testData['currency_id']}\n";
$currency = Currency::find($testData['currency_id']);
if ($currency) {
    echo "✓ Currency found: {$currency->code} - {$currency->name}\n";
} else {
    echo "✗ Currency not found\n";
    echo "Available currencies:\n";
    $currencies = Currency::select('id', 'code', 'name')->limit(5)->get();
    foreach ($currencies as $curr) {
        echo "  - ID: {$curr->id}, Code: {$curr->code}, Name: {$curr->name}\n";
    }
}

// Check Branch
echo "\nChecking Branch ID: {$testData['branch_id']}\n";
$branch = Branch::find($testData['branch_id']);
if ($branch) {
    echo "✓ Branch found: {$branch->name}\n";
} else {
    echo "✗ Branch not found\n";
    echo "Available branches:\n";
    $branches = Branch::select('id', 'name')->limit(5)->get();
    foreach ($branches as $br) {
        echo "  - ID: {$br->id}, Name: {$br->name}\n";
    }
}

// Check Journal
echo "\nChecking Journal ID: {$testData['journal_id']}\n";
try {
    $journal = Journal::find($testData['journal_id']);
    if ($journal) {
        echo "✓ Journal found: {$journal->name}\n";
    } else {
        echo "✗ Journal not found\n";
        echo "Available journals:\n";
        $journals = Journal::select('id', 'name')->limit(5)->get();
        foreach ($journals as $j) {
            echo "  - ID: {$j->id}, Name: {$j->name}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking journal: " . $e->getMessage() . "\n";
}

// Check Items
echo "\nChecking Items:\n";
foreach ($testData['items'] as $index => $itemData) {
    echo "Item {$index}: ID {$itemData['item_id']}\n";
    $item = Item::find($itemData['item_id']);
    if ($item) {
        echo "✓ Item found: {$item->item_name_ar} / {$item->item_name_en}\n";
    } else {
        echo "✗ Item not found\n";
        echo "Available items:\n";
        $items = Item::select('id', 'item_name_ar', 'item_name_en', 'item_number')->limit(5)->get();
        foreach ($items as $it) {
            echo "  - ID: {$it->id}, Number: {$it->item_number}, Name: {$it->item_name_ar} / {$it->item_name_en}\n";
        }
        break;
    }
}

echo "\n3. Testing validation rules...\n";

// Create a mock request to test validation
$validator = \Illuminate\Support\Facades\Validator::make($testData, [
    'company_id' => 'required|exists:companies,id',
    'supplier_id' => 'required|exists:suppliers,id',
    'currency_id' => 'required|exists:currencies,id',
    'branch_id' => 'nullable|exists:branches,id',
    'journal_id' => 'nullable|exists:journals,id',
    'due_date' => 'required|date',
    'items' => 'required|array|min:1',
    'items.*.item_id' => 'required|exists:items,id',
    'items.*.quantity' => 'required|numeric|min:0.01',
]);

if ($validator->fails()) {
    echo "✗ Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - {$error}\n";
    }
} else {
    echo "✓ All validation rules passed\n";
}

echo "\n=== Debug Complete ===\n";
