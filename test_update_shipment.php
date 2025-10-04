<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Purchases\app\Services\IncomingShipmentService;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;

// Create a test request that extends IncomingShipmentRequest
class TestIncomingShipmentRequest extends IncomingShipmentRequest {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
        parent::__construct();
    }
    
    public function get(string $key, mixed $default = null): mixed {
        return $this->data[$key] ?? $default;
    }
    
    public function __get($key) {
        return $this->data[$key] ?? null;
    }
    
    public function has($key) {
        return isset($this->data[$key]);
    }
    
    public function filled($key) {
        return !empty($this->data[$key]);
    }
    
    public function all($keys = null) {
        return $this->data;
    }
    
    public function validated(): array {
        return $this->data;
    }
    
    public function rules(): array {
        return [];
    }
    
    public function authorize(): bool {
        return true;
    }
}

// Simulate authentication by creating a user
$user = \Modules\Users\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
    echo "Authenticated as user ID: " . $user->id . "\n";
}

$service = new IncomingShipmentService();

// First, let's check if there are any incoming shipments to update
echo "Checking for existing incoming shipments...\n";
$shipments = \Modules\Purchases\Models\Purchase::where('type', 'shipment')->get();
echo "Found " . $shipments->count() . " incoming shipments.\n";

if ($shipments->count() > 0) {
    $shipmentId = $shipments->first()->id;
    echo "Testing update method with shipment ID: " . $shipmentId . "\n";
    
    // Test data for updating the shipment
    $updateData = [
        "customer_id" => 1,
        "supplier_id" => 1,
        "currency_id" => 1,
        "journal_number" => 18, // Changed from 17
        "company_id" => 1,
        "branch_id" => 1,
        "employee_id" => 1,
        "journal_id" => 1,
        "due_date" => "2106-11-30", // Changed date
        "supplier_email" => "updated.email@example.org", // Changed email
        "licensed_operator" => "updated_operator",
        "cash_paid" => 20, // Changed from 18
        "checks_paid" => 60, // Changed from 57
        "allowed_discount" => 10, // Changed from 8
        "total_without_tax" => 55, // Changed from 51
        "tax_percentage" => 25, // Changed from 24
        "tax_amount" => 50, // Changed from 45
        "total_amount" => 45, // Changed from 40
        "remaining_balance" => 2, // Changed from 1
        "exchange_rate" => 90, // Changed from 89
        "total_foreign" => 25, // Changed from 21
        "total_local" => 30, // Changed from 26
        "is_tax_applied_to_currency" => false, // Changed from true
        "discount_percentage" => 5, // Changed from 4
        "discount_amount" => 70, // Changed from 68
        "notes" => "Updated shipment notes",
        "items" => [
            [
                "item_id" => 1,
                "account_id" => 1,
                "description" => "Updated item description.",
                "quantity" => 15, // Changed from 12
                "unit_price" => 70, // Changed from 66
                "discount_rate" => 15, // Changed from 13
                "tax_rate" => 70, // Changed from 65
                "total_foreign" => 80, // Changed from 72
                "total_local" => 25, // Changed from 18
                "total" => 85, // Changed from 74
                "notes" => "Updated item notes"
            ]
        ]
    ];

    $request = new TestIncomingShipmentRequest($updateData);
    
    try {
        echo "Testing update method...\n";
        $updatedShipment = $service->update($request, $shipmentId);
        echo "Success! Shipment updated with ID: " . $updatedShipment->id . "\n";
        echo "Updated notes: " . $updatedShipment->notes . "\n";
        echo "Updated cash_paid: " . $updatedShipment->cash_paid . "\n";
        echo "Items count: " . $updatedShipment->items->count() . "\n";
        if ($updatedShipment->items->count() > 0) {
            echo "First item quantity: " . $updatedShipment->items->first()->quantity . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
} else {
    echo "No incoming shipments found to test with.\n";
    echo "You may need to create a shipment first using the store method.\n";
}
