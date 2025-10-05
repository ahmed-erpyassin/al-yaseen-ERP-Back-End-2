<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Purchases\app\Services\OutgoingOrderService;
use Modules\Purchases\Http\Requests\OutgoingOrderRequest;

// Create a test request that extends OutgoingOrderRequest
class TestOutgoingOrderRequest extends OutgoingOrderRequest {
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

$service = new OutgoingOrderService();

// Test data for outgoing order
$requestData = [
    'company_id' => 1, // Now coming from request instead of user
    'customer_id' => 1,
    'currency_id' => 1,
    'due_date' => '2024-12-31',
    'licensed_operator' => 'Test Operator',
    'notes' => 'Test outgoing order with Auth facade',
    'items' => [
        [
            'item_id' => 1,
            'quantity' => 5,
            'unit_price' => 100,
            'discount_percentage' => 10,
            'tax_rate' => 15,
            'description' => 'Test item for outgoing order'
        ]
    ]
];

$request = new TestOutgoingOrderRequest($requestData);

try {
    echo "Testing outgoing order creation with Auth facade...\n";
    echo "Company ID from request: " . $requestData['company_id'] . "\n";
    
    $order = $service->store($request);
    echo "Success! Outgoing order created with ID: " . $order->id . "\n";
    echo "Type: " . $order->type . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Company ID: " . $order->company_id . "\n";
    echo "User ID: " . $order->user_id . "\n";
    echo "Created By: " . $order->created_by . "\n";
    echo "Outgoing Order Number: " . $order->outgoing_order_number . "\n";
    echo "Items count: " . $order->items->count() . "\n";
    
    if ($order->items->count() > 0) {
        $firstItem = $order->items->first();
        echo "First item quantity: " . $firstItem->quantity . "\n";
        echo "First item unit price: " . $firstItem->unit_price . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
