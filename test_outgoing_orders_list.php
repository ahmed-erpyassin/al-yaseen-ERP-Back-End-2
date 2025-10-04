<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Purchases\app\Services\OutgoingOrderService;
use Modules\Purchases\Http\Controllers\OutgoingOrderController;

// Simulate authentication by creating a user
$user = \Modules\Users\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
    echo "Authenticated as user ID: " . $user->id . "\n";
}

$service = new OutgoingOrderService();
$controller = new OutgoingOrderController($service);

// First, let's check if there are any outgoing orders
echo "Checking for existing outgoing orders...\n";
$orders = \Modules\Purchases\Models\Purchase::where('type', 'order')->get();
echo "Found " . $orders->count() . " outgoing orders.\n";

if ($orders->count() > 0) {
    echo "Testing outgoing orders listing...\n";
    
    try {
        // Create a mock request
        $request = new \Illuminate\Http\Request();
        
        // Test the service method directly
        echo "Testing service index method...\n";
        $serviceResult = $service->index($request);
        echo "Service success! Found " . $serviceResult->count() . " orders.\n";
        
        // Test the controller method
        echo "Testing controller index method...\n";
        $response = $controller->index($request);
        $responseData = $response->getData(true);
        
        if ($responseData['success']) {
            echo "Controller success! Retrieved outgoing orders data.\n";
            echo "Number of orders: " . count($responseData['data']) . "\n";
            
            if (!empty($responseData['data'])) {
                $firstOrder = $responseData['data'][0];
                echo "First order ID: " . $firstOrder['id'] . "\n";
                echo "First order type: " . $firstOrder['type'] . "\n";
                echo "First order status: " . $firstOrder['status'] . "\n";
                
                if (isset($firstOrder['items']) && !empty($firstOrder['items'])) {
                    echo "First order has " . count($firstOrder['items']) . " items.\n";
                    $firstItem = $firstOrder['items'][0];
                    echo "First item ID: " . $firstItem['id'] . "\n";
                    echo "First item quantity: " . $firstItem['quantity'] . "\n";
                    
                    if ($firstItem['item']) {
                        echo "Item details loaded successfully: " . $firstItem['item']['name'] . "\n";
                    } else {
                        echo "Item details: null (relationship not loaded)\n";
                    }
                }
            }
        } else {
            echo "Controller failed: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
} else {
    echo "No outgoing orders found to test with.\n";
    echo "You may need to create an outgoing order first.\n";
}
