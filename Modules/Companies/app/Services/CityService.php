<?php

namespace Modules\Companies\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\City;

class CityService
{
    public function createCity(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            return City::create($data);
        });
    }

    public function getCities($user)
    {
        return City::with(['country', 'region']) // المدينة مع الدولة + المنطقة
            ->where('user_id', $user->id)
            ->get();
    }

    public function getCityById($id)
    {
        return City::with(['country', 'region'])->findOrFail($id);
    }

    public function updateCity($id, array $data)
    {
        $city = City::findOrFail($id);
        $city->update($data);
        return $city->load(['country', 'region']);
    }

    public function deleteCity($id)
    {
        $city = City::findOrFail($id);
        $city->delete();
    }
}
