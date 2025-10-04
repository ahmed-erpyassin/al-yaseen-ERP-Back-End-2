<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Purchases\app\Services\OutgoingOrderService;
use Modules\Purchases\Http\Controllers\OutgoingOrderController;
use Modules\Purchases\Http\Requests\OutgoingOrderRequest;

// Simulate authentication by creating a user
$user = \Modules\Users\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
    echo "Authenticated as user ID: " . $user->id . "\n";
}

$service = new OutgoingOrderService();
$controller = new OutgoingOrderController($service);

echo "Testing outgoing order creation...\n";

try {
    // Create test data
    $testData = [
        "customer_id" => 1,
        "supplier_id" => 1,
        "currency_id" => 1,
        "journal_number" => 17,
        "company_id" => 1,
        "branch_id" => 1,
        "employee_id" => 1,
        "journal_id" => 1,
        "due_date" => "2106-10-30",
        "supplier_email" => "carolyne.luettgen@example.org",
        "licensed_operator" => "fuudtdsufvyvddqamniihdsa",
        "cash_paid" => 16,
        "checks_paid" => 57,
        "allowed_discount" => 8,
        "total_without_tax" => 51,
        "tax_percentage" => 24,
        "tax_amount" => 45,
        "total_amount" => 40,
        "remaining_balance" => 1,
        "exchange_rate" => 89,
        "total_foreign" => 21,
        "total_local" => 26,
        "is_tax_applied_to_currency" => true,
        "discount_percentage" => 4,
        "discount_amount" => 68,
        "notes" => "qtqxbajwbpilpmufinllw",
        "items" => [
            [
                "item_id" => 1,
                "account_id" => 1,
                "description" => "Dolores dolorum amet iste laborum eius est dolor.",
                "quantity" => 12,
                "unit_price" => 66,
                "discount_rate" => 13,
                "tax_rate" => 65,
                "total_foreign" => 72,
                "total_local" => 18,
                "total" => 74,
                "notes" => "yvddqamniihfqcoynlazg"
            ]
        ]
    ];

    // Create a mock request using the proper request class
    $request = new \Modules\Purchases\Http\Requests\OutgoingOrderRequest();
    $request->merge($testData);
    $request->setContainer(app());

    echo "Testing service store method...\n";
    $serviceResult = $service->store($request);
    echo "Service success! Created outgoing order with ID: " . $serviceResult->id . "\n";
    echo "Order type: " . $serviceResult->type . "\n";
    echo "Order status: " . $serviceResult->status . "\n";
    echo "Order number: " . ($serviceResult->outgoing_order_number ?? 'N/A') . "\n";

    if ($serviceResult->items && $serviceResult->items->count() > 0) {
        echo "Created " . $serviceResult->items->count() . " items.\n";
        $firstItem = $serviceResult->items->first();
        echo "First item quantity: " . $firstItem->quantity . "\n";
        echo "First item total: " . $firstItem->total . "\n";
    }

    echo "\nTesting controller store method...\n";
    $response = $controller->store($request);
    $responseData = $response->getData(true);

    if ($responseData['success']) {
        echo "Controller success! Created outgoing order.\n";
        echo "Response message: " . $responseData['message'] . "\n";

        if (isset($responseData['data'])) {
            echo "Order ID: " . $responseData['data']['id'] . "\n";
            echo "Order type: " . $responseData['data']['type'] . "\n";
        }
    } else {
        echo "Controller failed: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        if (isset($responseData['error'])) {
            echo "Error: " . $responseData['error'] . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
