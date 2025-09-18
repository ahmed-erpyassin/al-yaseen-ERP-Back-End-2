<?php

namespace Modules\FinancialAccounts\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\Budget;

class BudgetService
{
    public function getBudgets($user)
    {
        return Budget::with(['account', 'fiscalYear'])->where('company_id', $user->company?->id)->get();
    }

    public function getById($id)
    {
        return Budget::with(['account', 'fiscalYear'])->where('id', $id)->firstOrFail();
    }

    public function createBudget(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return Budget::create($data);
        });
    }

    public function updateBudget($id, array $data)
    {
        $budget = Budget::findOrFail($id);
        $budget->update($data);
        return $budget;
    }

    public function deleteBudget($id, $userId)
    {
        $budget = Budget::findOrFail($id);
        $budget->deleted_by = $userId;
        $budget->save();
        $budget->delete();
    }
}
