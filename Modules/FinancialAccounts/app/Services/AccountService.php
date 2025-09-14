<?php

namespace Modules\FinancialAccounts\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\Account;

class AccountService
{
    public function getAccounts($user)
    {
        return Account::with(['group', 'currency', 'fiscalYear'])->where('company_id', $user->company?->id)->get();
    }

    public function getAccountById($id)
    {
        return Account::with(['group', 'currency', 'fiscalYear'])->where('id', $id)->firstOrFail();
    }

    public function createAccount(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return Account::create($data);
        });
    }

    public function updateAccount($id, array $data)
    {
        $account = Account::findOrFail($id);
        $account->update($data);
        return $account;
    }

    public function deleteAccount($id, $userId)
    {
        $account = Account::findOrFail($id);
        $account->deleted_by = $userId;
        $account->save();
        $account->delete();
    }
}
