<?php

namespace Modules\FinancialAccounts\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\TaxRate;

class TaxRateService
{
    public function createTaxRate(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return TaxRate::create($data);
        });
    }

    public function getTaxRates($user)
    {
        return TaxRate::all();
    }

    public function getTaxRateById($id)
    {
        return TaxRate::findOrFail($id);
    }

    public function updateTaxRate($id, array $data)
    {
        $taxRate = TaxRate::findOrFail($id);
        $taxRate->update($data);
        return $taxRate;
    }

    public function deleteTaxRate($id, $userId)
    {
        $taxRate = TaxRate::findOrFail($id);
        $taxRate->deleted_by = $userId;
        $taxRate->save();
        $taxRate->delete();
    }
}
