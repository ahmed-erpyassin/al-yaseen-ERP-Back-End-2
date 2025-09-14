<?php

namespace Modules\FinancialAccounts\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\AccountGroup;

class AccountGroupService
{
    public function getAccountGroup($user)
    {
        return AccountGroup::with(['accounts'])->where('company_id', $user->company?->id)->get();
    }

    public function getById($id)
    {
        return AccountGroup::with(['accounts'])->where('id', $id)->firstOrFail();
    }

    public function createAccountGroup(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return AccountGroup::create($data);
        });
    }

    public function updateAccountGroup($id, array $data)
    {
        $accountGroup = AccountGroup::findOrFail($id);
        $accountGroup->update($data);
        return $accountGroup;
    }

    public function deleteAccountGroup($id, $userId)
    {
        $accountGroup = AccountGroup::findOrFail($id);
        $accountGroup->deleted_by = $userId;
        $accountGroup->save();
        $accountGroup->delete();
    }
}
