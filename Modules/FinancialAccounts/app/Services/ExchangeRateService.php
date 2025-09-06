<?php

namespace Modules\FinancialAccounts\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\ExchangeRate;

class ExchangeRateService
{
    public function createExchangeRate(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return ExchangeRate::create($data);
        });
    }

    public function getExchangeRates($user)
    {
        return ExchangeRate::where('user_id', $user->id)->get();
    }

    public function getExchangeRateById($id)
    {
        return ExchangeRate::findOrFail($id);
    }

    public function updateExchangeRate($id, array $data)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        $exchangeRate->update($data);
        return $exchangeRate;
    }

    public function deleteExchangeRate($id, $userId)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        $exchangeRate->deleted_by = $userId;
        $exchangeRate->save();
        $exchangeRate->delete();
    }
}
