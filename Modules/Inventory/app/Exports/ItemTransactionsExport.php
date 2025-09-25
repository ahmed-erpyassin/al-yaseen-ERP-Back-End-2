<?php

namespace Modules\Inventory\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ItemTransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
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
     * Return collection of transactions
     */
    public function collection()
    {
        return $this->transactions;
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'نوع الحركة',
            'رقم المستند',
            'التاريخ',
            'الكمية',
            'سعر الوحدة',
            'المبلغ الإجمالي',
            'مبلغ الخصم',
            'صافي المبلغ',
            'المرجع',
            'الاتجاه',
            'الحالة',
            'نوع الحركة التفصيلي',
            'السبب',
            'المنشئ',
            'ملاحظات'
        ];
    }

    /**
     * Map each transaction to Excel row
     */
    public function map($transaction): array
    {
        return [
            $transaction['type_ar'] ?? '',
            $transaction['document_number'] ?? '',
            $transaction['transaction_date'] ?? '',
            $transaction['quantity'] ?? 0,
            $transaction['unit_price'] ? number_format($transaction['unit_price'], 2) : '',
            $transaction['total_amount'] ? number_format($transaction['total_amount'], 2) : '',
            $transaction['discount_amount'] ? number_format($transaction['discount_amount'], 2) : '',
            $transaction['net_amount'] ? number_format($transaction['net_amount'], 2) : '',
            $transaction['reference'] ?? '',
            $transaction['direction_ar'] ?? '',
            $transaction['status_ar'] ?? '',
            $transaction['movement_type_ar'] ?? '',
            $transaction['reason'] ?? '',
            $transaction['created_by'] ?? '',
            $transaction['notes'] ?? ''
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Set RTL direction for Arabic content
        $sheet->setRightToLeft(true);

        // Style the header row (row 1)
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style data rows (starting from row 2)
        $lastRow = $this->transactions->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle("A2:O{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }

        // Add item information after the data
        $infoStartRow = $lastRow + 3;

        $sheet->setCellValue("A{$infoStartRow}", 'معلومات الصنف:');
        $sheet->getStyle("A{$infoStartRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14]
        ]);

        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'اسم الصنف: ' . $this->item['name']);
        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'كود الصنف: ' . $this->item['code']);
        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'رقم الصنف: ' . $this->item['item_number']);
        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'الرصيد الحالي: ' . $this->item['current_balance']);

        // Filter information
        $filterStartRow = $lastRow + 3;
        $sheet->setCellValue("H{$filterStartRow}", 'معلومات التقرير:');
        $sheet->getStyle("H{$filterStartRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14]
        ]);

        $filterStartRow++;
        $sheet->setCellValue("H{$filterStartRow}", 'من تاريخ: ' . ($this->filters['date_from'] ?? 'غير محدد'));
        $filterStartRow++;
        $sheet->setCellValue("H{$filterStartRow}", 'إلى تاريخ: ' . ($this->filters['date_to'] ?? 'غير محدد'));
        $filterStartRow++;
        $sheet->setCellValue("H{$filterStartRow}", 'نوع الحركة: ' . $this->getTransactionTypeArabic($this->filters['transaction_type'] ?? 'all'));
        $filterStartRow++;
        $sheet->setCellValue("H{$filterStartRow}", 'تاريخ التقرير: ' . now()->format('Y-m-d H:i:s'));

        return $sheet;
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // نوع الحركة
            'B' => 20, // رقم المستند
            'C' => 15, // التاريخ
            'D' => 12, // الكمية
            'E' => 15, // سعر الوحدة
            'F' => 18, // المبلغ الإجمالي
            'G' => 15, // مبلغ الخصم
            'H' => 18, // صافي المبلغ
            'I' => 25, // المرجع
            'J' => 12, // الاتجاه
            'K' => 12, // الحالة
            'L' => 20, // نوع الحركة التفصيلي
            'M' => 20, // السبب
            'N' => 15, // المنشئ
            'O' => 30, // ملاحظات
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'حركات الصنف - ' . $this->item['code'];
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
}
