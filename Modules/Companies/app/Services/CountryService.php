<?php

namespace Modules\Companies\Services;

use Illuminate\Support\Facades\DB;
use Modules\Companies\Models\Country;

class CountryService
{
    public function createCountry(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            return Country::create($data);
        });
    }

    public function getCountries($user)
    {
        return Country::with([
            'regions.cities' // الدولة ترجع معها المناطق + المدن
        ])->where('user_id', $user->id)->get();
    }

    public function getCountryById($id)
    {
        return Country::with([
            'regions.cities'
        ])->findOrFail($id);
    }

    public function updateCountry($id, array $data)
    {
        $country = Country::findOrFail($id);
        $country->update($data);
        return $country->load(['regions.cities']);
    }

    public function deleteCountry($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();
    }
}
