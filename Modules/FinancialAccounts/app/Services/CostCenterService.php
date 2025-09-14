<?php

namespace Modules\FinancialAccounts\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\CostCenter;

class CostCenterService
{
    public function createCostCenter(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return CostCenter::create($data);
        });
    }

    public function getCostCenters($user)
    {
        return CostCenter::all();
    }

    public function getCostCenterById($id)
    {
        return CostCenter::findOrFail($id);
    }

    public function updateCostCenter($id, array $data)
    {
        $cost_center = CostCenter::findOrFail($id);
        $cost_center->update($data);
        return $cost_center;
    }

    public function deleteCostCenter($id, $userId)
    {
        $cost_center = CostCenter::findOrFail($id);
        $cost_center->deleted_by = $userId;
        $cost_center->save();
        $cost_center->delete();
    }
}
