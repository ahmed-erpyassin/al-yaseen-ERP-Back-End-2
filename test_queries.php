<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TESTING DATABASE QUERIES ===\n\n";

try {
    // Test sales query
    echo "1. Testing sales query...\n";
    $salesQuery = DB::table('sales_items')
        ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
        ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
        ->select([
            'sales.id as transaction_id',
            'sales.invoice_number as document_number',
            'sales.created_at as transaction_date',
            'sales_items.quantity',
            'sales_items.unit_price',
            'sales_items.total',
            'sales_items.discount_rate',
            'customers.company_name as customer_name',
            'sales.notes',
            'sales.status'
        ])
        ->limit(1);
    
    $salesResult = $salesQuery->get();
    echo "   ✓ Sales query executed successfully. Found " . $salesResult->count() . " records.\n\n";
    
    // Test purchase query
    echo "2. Testing purchase query...\n";
    $purchaseQuery = DB::table('purchase_items')
        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
        ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
        ->select([
            'purchases.id as transaction_id',
            'purchases.id as document_number',
            'purchases.created_at as transaction_date',
            'purchase_items.quantity',
            'purchase_items.unit_price',
            'purchase_items.total',
            'purchase_items.discount_rate',
            'suppliers.supplier_name_ar as supplier_name',
            'purchases.notes',
            'purchases.status'
        ])
        ->limit(1);
    
    $purchaseResult = $purchaseQuery->get();
    echo "   ✓ Purchase query executed successfully. Found " . $purchaseResult->count() . " records.\n\n";
    
    // Test stock movements query
    echo "3. Testing stock movements query...\n";
    $stockQuery = DB::table('stock_movements')
        ->leftJoin('warehouses', 'stock_movements.warehouse_id', '=', 'warehouses.id')
        ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
        ->select([
            'stock_movements.id as transaction_id',
            'stock_movements.id as document_number',
            'stock_movements.transaction_date',
            'stock_movements.quantity',
            'stock_movements.movement_type',
            'stock_movements.type',
            'stock_movements.notes',
            'warehouses.name as warehouse_name',
            DB::raw("CONCAT(users.first_name, ' ', users.second_name) as created_by_name")
        ])
        ->limit(1);
    
    $stockResult = $stockQuery->get();
    echo "   ✓ Stock movements query executed successfully. Found " . $stockResult->count() . " records.\n\n";
    
    echo "=== ALL QUERIES EXECUTED SUCCESSFULLY! ===\n";
    echo "The database column issues have been resolved.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
