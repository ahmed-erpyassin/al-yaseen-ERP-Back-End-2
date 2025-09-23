<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the first available branch for each company
        $branches = DB::table('branches')
            ->select('id', 'company_id')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->get()
            ->groupBy('company_id')
            ->map(function ($companyBranches) {
                return $companyBranches->first()->id;
            });

        // Update stock movements with branch_id based on company_id
        foreach ($branches as $companyId => $branchId) {
            DB::table('stock_movements')
                ->where('company_id', $companyId)
                ->whereNull('branch_id')
                ->update(['branch_id' => $branchId]);
        }

        // For document_id, let's use a simple sequential numbering approach
        // since the documents table doesn't exist yet
        $stockMovements = DB::table('stock_movements')
            ->whereNull('document_id')
            ->orderBy('id')
            ->get();

        $documentCounter = 1;
        foreach ($stockMovements as $movement) {
            DB::table('stock_movements')
                ->where('id', $movement->id)
                ->update(['document_id' => $documentCounter]);
            $documentCounter++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the populated data
        DB::table('stock_movements')->update([
            'branch_id' => null,
            'document_id' => null
        ]);
    }
};
