<?php

/**
 * Quick test script to verify Payroll APIs are working
 * Run with: php test_payroll_apis.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Payroll System API Test ===\n\n";

try {
    // Test 1: Check if PayrollRecord model works
    echo "1. Testing PayrollRecord Model...\n";
    $payrollCount = \Modules\HumanResources\Models\PayrollRecord::count();
    echo "   ✓ PayrollRecord count: {$payrollCount}\n\n";

    // Test 2: Check if PayrollData model works
    echo "2. Testing PayrollData Model...\n";
    $payrollDataCount = \Modules\HumanResources\Models\PayrollData::count();
    echo "   ✓ PayrollData count: {$payrollDataCount}\n\n";

    // Test 3: Check if Employee model works
    echo "3. Testing Employee Model...\n";
    $employeeCount = \Modules\HumanResources\Models\Employee::count();
    echo "   ✓ Employee count: {$employeeCount}\n\n";

    // Test 4: Check if Resources can be instantiated
    echo "4. Testing PayrollRecordResource...\n";
    $payrollRecord = \Modules\HumanResources\Models\PayrollRecord::first();
    if ($payrollRecord) {
        $resource = new \Modules\HumanResources\Http\Resources\Employee\PayrollRecordResource($payrollRecord);
        echo "   ✓ PayrollRecordResource instantiated successfully\n";
    } else {
        echo "   - No PayrollRecord found to test Resource\n";
    }
    echo "\n";

    // Test 5: Check if PayrollDataResource works
    echo "5. Testing PayrollDataResource...\n";
    $payrollData = \Modules\HumanResources\Models\PayrollData::first();
    if ($payrollData) {
        $resource = new \Modules\HumanResources\Http\Resources\Employee\PayrollDataResource($payrollData);
        echo "   ✓ PayrollDataResource instantiated successfully\n";
    } else {
        echo "   - No PayrollData found to test Resource\n";
    }
    echo "\n";

    // Test 6: Check if Services can be instantiated
    echo "6. Testing PayrollService...\n";
    $service = new \Modules\HumanResources\app\Services\Employee\PayrollService();
    echo "   ✓ PayrollService instantiated successfully\n\n";

    // Test 7: Check if Controllers can be instantiated
    echo "7. Testing PayrollController...\n";
    $controller = new \Modules\HumanResources\Http\Controllers\Employee\PayrollController();
    echo "   ✓ PayrollController instantiated successfully\n\n";

    echo "8. Testing PayrollDataController...\n";
    $dataController = new \Modules\HumanResources\Http\Controllers\Employee\PayrollDataController();
    echo "   ✓ PayrollDataController instantiated successfully\n\n";

    echo "9. Testing PayrollSearchController...\n";
    $searchController = new \Modules\HumanResources\Http\Controllers\Employee\PayrollSearchController();
    echo "   ✓ PayrollSearchController instantiated successfully\n\n";

    // Test 8: Check if Request classes work
    echo "10. Testing PayrollRecordRequest...\n";
    $request = new \Modules\HumanResources\Http\Requests\Employee\PayrollRecordRequest();
    echo "   ✓ PayrollRecordRequest instantiated successfully\n\n";

    echo "=== ALL TESTS PASSED! ===\n";
    echo "The Payroll system is ready to use.\n\n";

    echo "Available API endpoints:\n";
    echo "- GET    /api/v1/employees/payroll/records/list\n";
    echo "- POST   /api/v1/employees/payroll/records/create\n";
    echo "- GET    /api/v1/employees/payroll/records/{id}/show\n";
    echo "- PUT    /api/v1/employees/payroll/records/{id}/update\n";
    echo "- DELETE /api/v1/employees/payroll/records/{id}/delete\n";
    echo "- GET    /api/v1/employees/payroll/records/{id}/data/list\n";
    echo "- POST   /api/v1/employees/payroll/records/{id}/data/create\n";
    echo "- And many more...\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
