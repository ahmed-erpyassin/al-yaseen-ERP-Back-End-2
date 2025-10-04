<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Purchase Return Invoice Store ===\n\n";

// Mock authentication
\Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);

// Test data
$testData = [
    'supplier_id' => 1,
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

try {
    // Create a mock request
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
    
    // Test the service
    $service = new \Modules\Purchases\app\Services\ReturnInvoiceService();
    $result = $service->store($mockRequest);
    
    echo "✅ Successfully created purchase return invoice!\n";
    echo "Invoice ID: {$result->id}\n";
    echo "Type: {$result->type}\n";
    echo "Status: {$result->status}\n";
    echo "Supplier ID: {$result->supplier_id}\n";
    echo "Total Amount: {$result->total_amount}\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
