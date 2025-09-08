<?php

namespace Modules\Companies\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\BusinessType;

class BusinessTypeService
{
    public function createBusinessType(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return BusinessType::create($data);
        });
    }

    public function getBusinessTypes()
    {
        return BusinessType::all();
    }

    public function getBusinessTypeById($id)
    {
        return BusinessType::findOrFail($id);
    }

    public function updateBusinessType($id, array $data)
    {
        $businessType = BusinessType::findOrFail($id);
        $businessType->update($data);
        return $businessType;
    }

    public function deleteBusinessType($id)
    {
        $businessType = BusinessType::findOrFail($id);
        $businessType->delete();
    }
}
