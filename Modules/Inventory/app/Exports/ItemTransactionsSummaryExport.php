<?php

namespace Modules\Inventory\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ItemTransactionsSummaryExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $item;
    protected $summary;
    protected $filters;

    public function __construct($item, $summary, $filters)
    {
        $this->item = $item;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    /**
     * Return array of summary data
     */
    public function array(): array
    {
        return [
            // Header
            ['ملخص حركات الصنف', '', '', ''],
            ['', '', '', ''],
            
            // Item Information
            ['معلومات الصنف', '', '', ''],
            ['اسم الصنف', $this->item['name'], '', ''],
            ['كود الصنف', $this->item['code'], '', ''],
            ['رقم الصنف', $this->item['item_number'], '', ''],
            ['الوحدة', $this->item['unit'] ?? 'غير محدد', '', ''],
            ['الرصيد الحالي', $this->item['current_balance'], '', ''],
            ['', '', '', ''],
            
            // Filter Information
            ['معلومات التصفية', '', '', ''],
            ['من تاريخ', $this->filters['date_from'] ?? 'غير محدد', '', ''],
            ['إلى تاريخ', $this->filters['date_to'] ?? 'غير محدد', '', ''],
            ['نوع الحركة', $this->getTransactionTypeArabic($this->filters['transaction_type'] ?? 'all'), '', ''],
            ['تاريخ التقرير', now()->format('Y-m-d H:i:s'), '', ''],
            ['', '', '', ''],
            
            // Quantity Summary
            ['ملخص الكميات', '', '', ''],
            ['إجمالي الوارد', $this->summary['quantity_summary']['total_in'], '', ''],
            ['إجمالي الصادر', $this->summary['quantity_summary']['total_out'], '', ''],
            ['صافي الحركة', $this->summary['quantity_summary']['net_movement'], '', ''],
            ['', '', '', ''],
            
            // Amount Summary
            ['ملخص المبالغ', '', '', ''],
            ['إجمالي مبيعات', number_format($this->summary['amount_summary']['total_sales_amount'], 2), 'ريال', ''],
            ['إجمالي مشتريات', number_format($this->summary['amount_summary']['total_purchases_amount'], 2), 'ريال', ''],
            ['صافي المبلغ', number_format($this->summary['amount_summary']['net_amount'], 2), 'ريال', ''],
            ['', '', '', ''],
            
            // Transaction Counts
            ['عدد الحركات', '', '', ''],
            ['إجمالي الحركات', $this->summary['total_transactions'], '', ''],
            ['عدد المبيعات', $this->summary['transaction_counts']['sales'], '', ''],
            ['عدد المشتريات', $this->summary['transaction_counts']['purchases'], '', ''],
            ['عدد حركات المخزون', $this->summary['transaction_counts']['stock_movements'], '', ''],
            ['', '', '', ''],
            
            // Performance Metrics
            ['مؤشرات الأداء', '', '', ''],
            ['متوسط سعر البيع', $this->calculateAverageSalePrice(), 'ريال', ''],
            ['متوسط سعر الشراء', $this->calculateAveragePurchasePrice(), 'ريال', ''],
            ['معدل دوران المخزون', $this->calculateTurnoverRate(), '', ''],
            ['', '', '', ''],
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Set RTL direction for Arabic content
        $sheet->setRightToLeft(true);

        // Style the main header
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Style section headers
        $sectionRows = [3, 10, 16, 21, 26, 32];
        foreach ($sectionRows as $row) {
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
        }

        // Style data rows
        $sheet->getStyle('A1:D40')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Merge header cell
        $sheet->mergeCells('A1:D1');

        return $sheet;
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
            'D' => 15,
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'ملخص الحركات - ' . $this->item['code'];
    }

    /**
     * Get transaction type in Arabic
     */
    private function getTransactionTypeArabic($type)
    {
        $types = [
            'all' => 'جميع الحركات',
            'sales' => 'مبيعات',
            'purchases' => 'مشتريات',
            'stock_movements' => 'حركات مخزون'
        ];

        return $types[$type] ?? $type;
    }

    /**
     * Calculate average sale price
     */
    private function calculateAverageSalePrice()
    {
        $salesAmount = $this->summary['amount_summary']['total_sales_amount'];
        $salesCount = $this->summary['transaction_counts']['sales'];
        
        return $salesCount > 0 ? number_format($salesAmount / $salesCount, 2) : '0.00';
    }

    /**
     * Calculate average purchase price
     */
    private function calculateAveragePurchasePrice()
    {
        $purchasesAmount = $this->summary['amount_summary']['total_purchases_amount'];
        $purchasesCount = $this->summary['transaction_counts']['purchases'];
        
        return $purchasesCount > 0 ? number_format($purchasesAmount / $purchasesCount, 2) : '0.00';
    }

    /**
     * Calculate turnover rate
     */
    private function calculateTurnoverRate()
    {
        $totalOut = $this->summary['quantity_summary']['total_out'];
        $currentBalance = $this->item['current_balance'];
        
        return $currentBalance > 0 ? number_format($totalOut / $currentBalance, 2) : '0.00';
    }
}
