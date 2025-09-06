<?php

namespace Modules\FinancialAccounts\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\FiscalYear;

class FiscalYearService
{
    public function createFiscalYear(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return FiscalYear::create($data);
        });
    }

    public function getFiscalYears($user)
    {
        return FiscalYear::where('user_id', $user->id)->get();
    }

    public function getFiscalYearById($id)
    {
        return FiscalYear::findOrFail($id);
    }

    public function updateFiscalYear($id, array $data)
    {
        $fiscalYear = FiscalYear::findOrFail($id);
        $fiscalYear->update($data);
        return $fiscalYear;
    }

    public function deleteFiscalYear($id, $userId)
    {
        $fiscalYear = FiscalYear::findOrFail($id);
        $fiscalYear->deleted_by = $userId;
        $fiscalYear->save();
        $fiscalYear->delete();
    }
}
