<?php

namespace Modules\Billing\Services;

use App\Support\AppContext;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Journal;

class InvoiceService
{
    public function getInvoices($user)
    {
        return Invoice::with(['items', 'payments', 'currency', 'company', 'lines.item', 'lines.unit'])->where('company_id', $user->company?->id)->get();
    }

    public function createInvoice(array $data, $user): Invoice
    {
        return DB::transaction(function () use ($data, $user) {
            $journal = Journal::findOrFail($data['journal_id']);

            $invoiceNumber = $journal->current_number + 1;

            $baseData = AppContext::all();
            $data = array_merge($baseData, $data, [
                'created_by' => $user->id,
            ]);

            $invoice = Invoice::create($data);

            $linesData = [];
            foreach ($data['lines'] as $line) {
                $linesData[] = array_merge($baseData, $line, [
                    'invoice_id'   => $invoice->id,
                    'created_by'   => $user->id,
                ]);
            }
            $invoice->lines()->createMany($linesData);

            $journal->update(['current_number' => $invoiceNumber]);

            return $invoice->load('lines');
        });
    }

    public function getInvoiceById($id): Invoice
    {
        return Invoice::with(['items', 'payments'])->where('id', $id)->firstOrFail();
    }

    public function updateInvoice($id, array $data): Invoice
    {
        return DB::transaction(function () use ($id, $data) {
            $invoice = Invoice::findOrFail($id);

            $baseData = AppContext::all();
            $data = array_merge($baseData, $data, [
                'updated_by' => $baseData['user_id'],
            ]);

            $invoice->update($data);

            if (isset($data['lines'])) {
                $invoice->lines()->delete();

                $linesData = [];
                foreach ($data['lines'] as $line) {
                    $linesData[] = array_merge(AppContext::all(), $line, [
                        'invoice_id' => $invoice->id,
                        'created_by' => $invoice->created_by,
                    ]);
                }
                $invoice->lines()->createMany($linesData);
            }

            return $invoice->load('lines');
        });
    }

    public function deleteInvoice($id, $userId): void
    {
        DB::transaction(function () use ($id, $userId) {
            $invoice = Invoice::findOrFail($id);
            $invoice->deleted_by = $userId;
            $invoice->save();
            $invoice->lines()->delete();
            $invoice->delete();
        });
    }
}
