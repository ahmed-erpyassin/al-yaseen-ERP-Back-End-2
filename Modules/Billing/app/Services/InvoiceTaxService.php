<?php

namespace Modules\Billing\Services;

use App\Support\AppContext;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceTax;

class InvoiceTaxService
{
    public function getInvoiceTaxes($user)
    {
        return InvoiceTax::with(['invoice', 'taxRate', 'company'])->where('company_id', $user->company?->id)->get();
    }

    public function createInvoiceTax(array $data, $user): InvoiceTax
    {
        return DB::transaction(function () use ($data, $user) {
            $baseData = AppContext::all();
            $data = array_merge($baseData, $data, [
                'created_by' => $user->id,
            ]);

            return InvoiceTax::create($data);
        });
    }

    public function getInvoiceTaxById($id): InvoiceTax
    {
        return InvoiceTax::with(['invoice', 'taxRate', 'company'])->where('id', $id)->firstOrFail();
    }

    public function updateInvoiceTax($id, array $data): InvoiceTax
    {
        return DB::transaction(function () use ($id, $data) {
            $invoiceTax = InvoiceTax::findOrFail($id);

            $baseData = AppContext::all();
            $data = array_merge($baseData, $data, [
                'updated_by' => $baseData['user_id'],
            ]);

            $invoiceTax->update($data);

            return $invoiceTax;
        });
    }

    public function deleteInvoiceTax($id): void
    {
        DB::transaction(function () use ($id) {
            $invoiceTax = InvoiceTax::findOrFail($id);
            $invoiceTax->delete();
        });
    }
}
