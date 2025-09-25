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

class ItemTransactionsByTypeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $transactions;
    protected $item;
    protected $type;
    protected $typeArabic;
    protected $filters;

    public function __construct(Collection $transactions, $item, $type, $typeArabic, $filters)
    {
        $this->transactions = $transactions;
        $this->item = $item;
        $this->type = $type;
        $this->typeArabic = $typeArabic;
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
     * Define the headings based on transaction type
     */
    public function headings(): array
    {
        $commonHeadings = [
            'رقم المستند',
            'التاريخ',
            'الكمية',
            'المرجع',
            'الاتجاه',
            'الحالة',
            'ملاحظات'
        ];

        if ($this->type === 'sales' || $this->type === 'purchases') {
            return array_merge([
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
                'ملاحظات'
            ]);
        } else {
            return array_merge([
                'رقم المستند',
                'التاريخ',
                'الكمية',
                'نوع الحركة',
                'السبب',
                'المرجع',
                'الاتجاه',
                'المنشئ',
                'ملاحظات'
            ]);
        }
    }

    /**
     * Map each transaction to Excel row
     */
    public function map($transaction): array
    {
        if ($this->type === 'sales' || $this->type === 'purchases') {
            return [
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
                $transaction['notes'] ?? ''
            ];
        } else {
            return [
                $transaction['document_number'] ?? '',
                $transaction['transaction_date'] ?? '',
                $transaction['quantity'] ?? 0,
                $transaction['movement_type_ar'] ?? '',
                $transaction['reason'] ?? '',
                $transaction['reference'] ?? '',
                $transaction['direction_ar'] ?? '',
                $transaction['created_by'] ?? '',
                $transaction['notes'] ?? ''
            ];
        }
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Set RTL direction for Arabic content
        $sheet->setRightToLeft(true);

        // Get the number of columns based on transaction type
        $lastColumn = $this->type === 'stock_movements' ? 'I' : 'K';

        // Style the header row
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $this->getTypeColor()]
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

        // Style data rows
        $lastRow = $this->transactions->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray([
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

        // Add title and item information after the data
        $infoStartRow = $lastRow + 3;

        $sheet->setCellValue("A{$infoStartRow}", $this->typeArabic . ' - ' . $this->item['name']);
        $sheet->mergeCells("A{$infoStartRow}:{$lastColumn}{$infoStartRow}");
        $sheet->getStyle("A{$infoStartRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'كود الصنف: ' . $this->item['code']);
        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'رقم الصنف: ' . $this->item['item_number']);
        $infoStartRow++;
        $sheet->setCellValue("A{$infoStartRow}", 'عدد الحركات: ' . $this->transactions->count());

        return $sheet;
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        if ($this->type === 'sales' || $this->type === 'purchases') {
            return [
                'A' => 20, // رقم المستند
                'B' => 15, // التاريخ
                'C' => 12, // الكمية
                'D' => 15, // سعر الوحدة
                'E' => 18, // المبلغ الإجمالي
                'F' => 15, // مبلغ الخصم
                'G' => 18, // صافي المبلغ
                'H' => 25, // المرجع
                'I' => 12, // الاتجاه
                'J' => 12, // الحالة
                'K' => 30, // ملاحظات
            ];
        } else {
            return [
                'A' => 20, // رقم المستند
                'B' => 15, // التاريخ
                'C' => 12, // الكمية
                'D' => 20, // نوع الحركة
                'E' => 20, // السبب
                'F' => 25, // المرجع
                'G' => 12, // الاتجاه
                'H' => 15, // المنشئ
                'I' => 30, // ملاحظات
            ];
        }
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return $this->typeArabic;
    }

    /**
     * Get color based on transaction type
     */
    private function getTypeColor(): string
    {
        switch ($this->type) {
            case 'sales':
                return '70AD47'; // Green
            case 'purchases':
                return '4472C4'; // Blue
            case 'stock_movements':
                return 'FFC000'; // Orange
            default:
                return '7030A0'; // Purple
        }
    }
}
