<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Purchases\app\Services\IncomingShipmentService;
use Modules\Purchases\Http\Controllers\IncomingShipmentController;

// Simulate authentication by creating a user
$user = \Modules\Users\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
    echo "Authenticated as user ID: " . $user->id . "\n";
}

$service = new IncomingShipmentService();
$controller = new IncomingShipmentController($service);

// First, let's check if there are any incoming shipments
echo "Checking for existing incoming shipments...\n";
$shipments = \Modules\Purchases\Models\Purchase::where('type', 'shipment')->get();
echo "Found " . $shipments->count() . " incoming shipments.\n";

if ($shipments->count() > 0) {
    $shipmentId = $shipments->first()->id;
    echo "Testing show method with shipment ID: " . $shipmentId . "\n";
    
    try {
        // Test the service method directly
        echo "Testing service show method...\n";
        $shipment = $service->show($shipmentId);
        echo "Service success! Shipment ID: " . $shipment->id . "\n";
        echo "Type: " . $shipment->type . "\n";
        echo "Status: " . $shipment->status . "\n";
        
        // Test the controller method
        echo "Testing controller show method...\n";
        $response = $controller->show($shipmentId);
        $responseData = $response->getData(true);
        
        if ($responseData['success']) {
            echo "Controller success! Retrieved shipment data.\n";
            echo "Shipment ID: " . $responseData['data']['id'] . "\n";
            echo "Type: " . $responseData['data']['type'] . "\n";
            echo "Status: " . $responseData['data']['status'] . "\n";
        } else {
            echo "Controller failed: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }
} else {
    echo "No incoming shipments found to test with.\n";
    echo "You may need to create a shipment first using the store method.\n";
}
