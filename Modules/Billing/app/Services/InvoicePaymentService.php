<?php

namespace Modules\Billing\Services;

use App\Support\AppContext;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoicePayment;

class InvoicePaymentService
{
    public function getInvoicePayments($user)
    {
        return InvoicePayment::with(['invoice', 'currency', 'company'])->where('company_id', $user->company?->id)->get();
    }

    public function createInvoicePayment(array $data, $user): InvoicePayment
    {
        return DB::transaction(function () use ($data, $user) {
            $baseData = AppContext::all();
            $data = array_merge($baseData, $data, [
                'created_by' => $user->id,
            ]);

            $invoicePayment = InvoicePayment::create($data);

            // Update the related invoice's paid amount
            $invoice = Invoice::findOrFail($data['invoice_id']);
            $invoice->paid_amount += $data['amount'];
            $invoice->save();

            return $invoicePayment;
        });
    }

    public function getInvoicePaymentById($id): InvoicePayment
    {
        return InvoicePayment::with(['invoice', 'currency', 'company'])->where('id', $id)->firstOrFail();
    }

    public function updateInvoicePayment($id, array $data): InvoicePayment
    {
        return DB::transaction(function () use ($id, $data) {
            $invoicePayment = InvoicePayment::findOrFail($id);

            // If the amount is being updated, adjust the related invoice's paid amount accordingly
            if (isset($data['amount']) && $data['amount'] != $invoicePayment->amount) {
                $invoice = Invoice::findOrFail($invoicePayment->invoice_id);
                $invoice->paid_amount -= $invoicePayment->amount; // Subtract old amount
                $invoice->paid_amount += $data['amount']; // Add new amount
                $invoice->save();
            }

            $baseData = AppContext::all();
            $data = array_merge($baseData, $data, [
                'updated_by' => $baseData['user_id'],
            ]);

            $invoicePayment->update($data);

            return $invoicePayment;
        });
    }

    public function deleteInvoicePayment($id): void
    {
        DB::transaction(function () use ($id) {
            $invoicePayment = InvoicePayment::findOrFail($id);

            // Adjust the related invoice's paid amount before deleting the payment
            $invoice = Invoice::findOrFail($invoicePayment->invoice_id);
            $invoice->paid_amount -= $invoicePayment->amount;
            $invoice->save();

            $invoicePayment->delete();
        });
    }
}
