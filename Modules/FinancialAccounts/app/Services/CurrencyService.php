<?php

namespace Modules\FinancialAccounts\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\Currency;

class CurrencyService
{
    public function createCurrency(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return Currency::create($data);
        });
    }

    public function getCurrencies($user)
    {
        return Currency::where('user_id', $user->id)->get();
    }

    public function getCurrencyById($id)
    {
        return Currency::findOrFail($id);
    }

    public function updateCurrency($id, array $data)
    {
        $currency = Currency::findOrFail($id);
        $currency->update($data);
        return $currency;
    }

    public function deleteCurrency($id, $userId)
    {
        $currency = Currency::findOrFail($id);
        $currency->deleted_by = $userId;
        $currency->save();
        $currency->delete();
    }
}
