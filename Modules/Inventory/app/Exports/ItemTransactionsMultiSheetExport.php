<?php

namespace Modules\Inventory\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemTransactionsMultiSheetExport implements WithMultipleSheets
{
    use Exportable;

    protected $transactions;
    protected $item;
    protected $summary;
    protected $filters;

    public function __construct(Collection $transactions, $item, $summary, $filters)
    {
        $this->transactions = $transactions;
        $this->item = $item;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    /**
     * Return array of sheets
     */
    public function sheets(): array
    {
        $sheets = [];

        // Main transactions sheet
        $sheets[] = new ItemTransactionsExport(
            $this->transactions,
            $this->item,
            $this->summary,
            $this->filters
        );

        // Summary sheet
        $sheets[] = new ItemTransactionsSummaryExport(
            $this->item,
            $this->summary,
            $this->filters
        );

        // Add separate sheets for each transaction type if there are multiple types
        if ($this->filters['transaction_type'] === 'all') {
            // Sales sheet
            $salesTransactions = $this->transactions->where('type', 'sale');
            if ($salesTransactions->count() > 0) {
                $sheets[] = new ItemTransactionsByTypeExport(
                    $salesTransactions,
                    $this->item,
                    'sales',
                    'مبيعات',
                    $this->filters
                );
            }

            // Purchases sheet
            $purchaseTransactions = $this->transactions->where('type', 'purchase');
            if ($purchaseTransactions->count() > 0) {
                $sheets[] = new ItemTransactionsByTypeExport(
                    $purchaseTransactions,
                    $this->item,
                    'purchases',
                    'مشتريات',
                    $this->filters
                );
            }

            // Stock movements sheet
            $stockMovements = $this->transactions->where('type', 'stock_movement');
            if ($stockMovements->count() > 0) {
                $sheets[] = new ItemTransactionsByTypeExport(
                    $stockMovements,
                    $this->item,
                    'stock_movements',
                    'حركات مخزون',
                    $this->filters
                );
            }
        }

        return $sheets;
    }
}
