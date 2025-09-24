<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== STOCK MOVEMENTS SEEDED DATA VERIFICATION ===\n\n";

// Check stock movements with populated data
$movements = \Modules\Inventory\Models\StockMovement::with(['branch'])
    ->select(
        'id', 
        'company_id', 
        'branch_id', 
        'document_id', 
        'type', 
        'movement_type',
        'quantity',
        'transaction_date'
    )
    ->orderBy('id', 'desc')
    ->limit(15)
    ->get();

echo "Latest 15 Stock Movements:\n";
echo str_pad('ID', 5) . str_pad('Company', 10) . str_pad('Branch', 10) . str_pad('Document', 12) . str_pad('Type', 12) . str_pad('Movement', 12) . str_pad('Quantity', 12) . "Date\n";
echo str_repeat('-', 90) . "\n";

foreach ($movements as $movement) {
    $branchName = $movement->branch ? $movement->branch->name : 'N/A';
    echo str_pad($movement->id, 5) . 
         str_pad($movement->company_id, 10) . 
         str_pad($movement->branch_id ?? 'NULL', 10) . 
         str_pad($movement->document_id ?? 'NULL', 12) . 
         str_pad($movement->type, 12) . 
         str_pad($movement->movement_type, 12) . 
         str_pad($movement->quantity, 12) . 
         $movement->transaction_date->format('Y-m-d') . "\n";
}

// Count statistics
$totalMovements = \Modules\Inventory\Models\StockMovement::count();
$withBranch = \Modules\Inventory\Models\StockMovement::whereNotNull('branch_id')->count();
$withDocument = \Modules\Inventory\Models\StockMovement::whereNotNull('document_id')->count();

echo "\n=== STATISTICS ===\n";
echo "Total Stock Movements: {$totalMovements}\n";
echo "With Branch ID: {$withBranch}\n";
echo "With Document ID: {$withDocument}\n";
echo "Branch Coverage: " . round(($withBranch / $totalMovements) * 100, 2) . "%\n";
echo "Document Coverage: " . round(($withDocument / $totalMovements) * 100, 2) . "%\n";

echo "\n=== BRANCHES USED ===\n";
$branchesUsed = \Modules\Inventory\Models\StockMovement::select('branch_id')
    ->whereNotNull('branch_id')
    ->distinct()
    ->with('branch:id,name,code')
    ->get();

foreach ($branchesUsed as $movement) {
    if ($movement->branch) {
        echo "Branch ID: {$movement->branch_id} - {$movement->branch->name} ({$movement->branch->code})\n";
    } else {
        echo "Branch ID: {$movement->branch_id} - Branch not found\n";
    }
}

echo "\n=== DOCUMENT ID RANGE ===\n";
$minDoc = \Modules\Inventory\Models\StockMovement::whereNotNull('document_id')->min('document_id');
$maxDoc = \Modules\Inventory\Models\StockMovement::whereNotNull('document_id')->max('document_id');
echo "Document ID Range: {$minDoc} - {$maxDoc}\n";

echo "\n=== SAMPLE MOVEMENTS BY TYPE ===\n";
$types = ['purchase', 'sales', 'production', 'adjustments', 'transfer'];
foreach ($types as $type) {
    $count = \Modules\Inventory\Models\StockMovement::where('type', $type)->count();
    echo "{$type}: {$count} movements\n";
}
