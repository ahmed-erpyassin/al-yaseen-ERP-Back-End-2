<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Purchase Return Invoice Store API ===\n\n";

// Test data
$testData = [
    'supplier_id' => 1,
    'currency_id' => 1,
    'journal_number' => rand(1000, 9999),
    'exchange_rate' => 1.0,
    'total_amount' => 100.00,
    'items' => [
        [
            'item_id' => 1,
            'quantity' => 2,
            'unit_price' => 50.00,
            'total' => 100.00
        ]
    ]
];

// Create a request
$request = new \Illuminate\Http\Request();
$request->merge($testData);
$request->merge(['company_id' => 1]); // Add company_id as it's expected from request

// Mock authentication
\Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);

try {
    // Test the service directly
    $service = new \Modules\Purchases\app\Services\ReturnInvoiceService();
    
    // Create a mock request object that extends the actual request class
    $mockRequest = new class($testData) extends \Modules\Purchases\app\Http\Requests\ReturnInvoiceRequest {
        protected $data;
        
        public function __construct($data) {
            $this->data = $data;
            $this->company_id = 1;
        }
        
        public function validated($key = null, $default = null) {
            if ($key !== null) {
                return $this->data[$key] ?? $default;
            }
            return $this->data;
        }
        
        public function __get($key) {
            if ($key === 'company_id') {
                return 1;
            }
            return $this->data[$key] ?? null;
        }
    };
    
    echo "Testing service store method...\n";
    $result = $service->store($mockRequest);
    
    echo "✅ Successfully created purchase return invoice!\n";
    echo "Invoice ID: {$result->id}\n";
    echo "Type: {$result->type}\n";
    echo "Status: {$result->status}\n";
    echo "Supplier ID: {$result->supplier_id}\n";
    echo "Company ID: {$result->company_id}\n";
    echo "Branch ID: {$result->branch_id}\n";
    echo "Total Amount: {$result->total_amount}\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
