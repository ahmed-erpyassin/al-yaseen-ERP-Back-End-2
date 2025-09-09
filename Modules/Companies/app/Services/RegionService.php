<?php

namespace Modules\Companies\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Region;

class RegionService
{
    public function createRegion(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            return Region::create($data);
        });
    }

    public function getRegions()
    {
        return Region::with(['country', 'cities'])->all();
    }

    public function getRegionById($id)
    {
        return Region::with(['country', 'cities'])->findOrFail($id);
    }

    public function updateRegion($id, array $data)
    {
        $region = Region::findOrFail($id);
        $region->update($data);
        return $region->load(['country', 'cities']);
    }

    public function deleteRegion($id)
    {
        $region = Region::findOrFail($id);
        $region->delete();
    }
}
