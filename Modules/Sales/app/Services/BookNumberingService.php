<?php

namespace Modules\Sales\app\Services;

use Modules\Sales\Models\Sale;
use Modules\Billing\Models\Journal;
use Illuminate\Support\Facades\DB;

class BookNumberingService
{
    const INVOICES_PER_BOOK = 50;

    /**
     * Generate book code and invoice number for a new quotation
     */
    public function generateBookAndInvoiceNumber($companyId, $journalId = null): array
    {
        return DB::transaction(function () use ($companyId, $journalId) {
            // Get the current book and invoice numbers
            $currentBook = $this->getCurrentBook($companyId);
            $nextInvoiceNumber = $this->getNextInvoiceNumber($companyId);

            // Check if we need to create a new book
            if ($this->shouldCreateNewBook($companyId, $currentBook)) {
                $currentBook = $this->createNewBook($companyId);
            }

            return [
                'book_code' => $currentBook,
                'invoice_number' => $nextInvoiceNumber,
                'journal_number' => $this->getJournalNumber($journalId)
            ];
        });
    }

    /**
     * Generate book code and invoice number for a new sales invoice
     */
    public function generateInvoiceBookAndNumber($companyId, $journalId = null): array
    {
        return DB::transaction(function () use ($companyId, $journalId) {
            // Get the current book and invoice numbers for invoices
            $currentBook = $this->getCurrentInvoiceBook($companyId);
            $nextInvoiceNumber = $this->getNextSalesInvoiceNumber($companyId);

            // Check if we need to create a new book
            if ($this->shouldCreateNewInvoiceBook($companyId, $currentBook)) {
                $currentBook = $this->createNewInvoiceBook($companyId);
            }

            return [
                'book_code' => $currentBook,
                'invoice_number' => $nextInvoiceNumber,
                'journal_number' => $this->getJournalNumber($journalId)
            ];
        });
    }

    /**
     * Get the current book code for the company
     */
    private function getCurrentBook($companyId): string
    {
        // Get the latest sale to determine current book
        $latestSale = Sale::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->whereNotNull('code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestSale || !$latestSale->code) {
            // Create first book
            return $this->generateBookCode($companyId, 1);
        }

        return $latestSale->code;
    }

    /**
     * Get the next sequential invoice number
     */
    private function getNextInvoiceNumber($companyId): int
    {
        $lastInvoice = Sale::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$lastInvoice) {
            return 1;
        }

        // Extract numeric part from invoice number
        $lastNumber = (int) filter_var($lastInvoice->invoice_number, FILTER_SANITIZE_NUMBER_INT);
        return $lastNumber + 1;
    }

    /**
     * Check if we should create a new book
     */
    private function shouldCreateNewBook($companyId, $currentBook): bool
    {
        // Count invoices in current book
        $invoicesInCurrentBook = Sale::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->where('code', $currentBook)
            ->count();

        return $invoicesInCurrentBook >= self::INVOICES_PER_BOOK;
    }

    /**
     * Create a new book code
     */
    private function createNewBook($companyId): string
    {
        // Get the current book number
        $currentBookNumber = $this->getCurrentBookNumber($companyId);
        $newBookNumber = $currentBookNumber + 1;

        return $this->generateBookCode($companyId, $newBookNumber);
    }

    /**
     * Get current book number
     */
    private function getCurrentBookNumber($companyId): int
    {
        $latestSale = Sale::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->whereNotNull('code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestSale || !$latestSale->code) {
            return 0;
        }

        // Extract book number from code (e.g., "BOOK-2025-001" -> 1)
        $parts = explode('-', $latestSale->code);
        return isset($parts[2]) ? (int) $parts[2] : 0;
    }

    /**
     * Generate book code with format: BOOK-YYYY-XXX
     */
    private function generateBookCode($companyId, $bookNumber): string
    {
        $year = date('Y');
        return sprintf('BOOK-%s-%03d', $year, $bookNumber);
    }

    /**
     * Get journal number from journal
     */
    private function getJournalNumber($journalId): int
    {
        if (!$journalId) {
            return 1;
        }

        $journal = Journal::find($journalId);
        if (!$journal) {
            return 1;
        }

        // Increment and update journal current number
        $nextNumber = $journal->current_number + 1;
        $journal->update(['current_number' => $nextNumber]);

        return $nextNumber;
    }

    /**
     * Get book statistics for a company
     */
    public function getBookStatistics($companyId): array
    {
        $currentBook = $this->getCurrentBook($companyId);
        $invoicesInCurrentBook = Sale::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->where('code', $currentBook)
            ->count();

        $totalBooks = Sale::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->whereNotNull('code')
            ->distinct('code')
            ->count();

        return [
            'current_book' => $currentBook,
            'invoices_in_current_book' => $invoicesInCurrentBook,
            'remaining_in_current_book' => self::INVOICES_PER_BOOK - $invoicesInCurrentBook,
            'total_books' => $totalBooks,
            'invoices_per_book' => self::INVOICES_PER_BOOK
        ];
    }

    /**
     * Get the current book code for sales invoices
     */
    private function getCurrentInvoiceBook($companyId): string
    {
        // Get the latest sale to determine current book
        $latestSale = Sale::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotNull('book_code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestSale || !$latestSale->book_code) {
            // Create first book for invoices
            return $this->generateInvoiceBookCode($companyId, 1);
        }

        return $latestSale->book_code;
    }

    /**
     * Get the next sequential invoice number for sales invoices
     */
    private function getNextSalesInvoiceNumber($companyId): string
    {
        $lastInvoice = Sale::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$lastInvoice) {
            return 'INV-000001';
        }

        // Extract numeric part from invoice number
        $lastNumber = (int) filter_var($lastInvoice->invoice_number, FILTER_SANITIZE_NUMBER_INT);
        $newNumber = $lastNumber + 1;

        return 'INV-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if we should create a new book for invoices
     */
    private function shouldCreateNewInvoiceBook($companyId, $currentBook): bool
    {
        // Count invoices in current book
        $invoicesInCurrentBook = Sale::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->where('book_code', $currentBook)
            ->count();

        return $invoicesInCurrentBook >= self::INVOICES_PER_BOOK;
    }

    /**
     * Create a new book code for invoices
     */
    private function createNewInvoiceBook($companyId): string
    {
        // Get the current book number
        $currentBookNumber = $this->getCurrentInvoiceBookNumber($companyId);
        $newBookNumber = $currentBookNumber + 1;

        return $this->generateInvoiceBookCode($companyId, $newBookNumber);
    }

    /**
     * Get current book number for invoices
     */
    private function getCurrentInvoiceBookNumber($companyId): int
    {
        $latestSale = Sale::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotNull('book_code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestSale || !$latestSale->book_code) {
            return 0;
        }

        // Extract book number from code (e.g., "INV-BOOK-2025-001" -> 1)
        $parts = explode('-', $latestSale->book_code);
        return isset($parts[3]) ? (int) $parts[3] : 0;
    }

    /**
     * Generate book code with format: INV-BOOK-YYYY-XXX
     */
    private function generateInvoiceBookCode($companyId, $bookNumber): string
    {
        $year = date('Y');
        return sprintf('INV-BOOK-%s-%03d', $year, $bookNumber);
    }
}
