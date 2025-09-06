<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index(Request $request)
    {
        $countries = $this->countryService->getCountries($request->user());
        return CountryResource::collection($countries);
    }

    public function store(CountryRequest $request)
    {
        $country = $this->countryService->createCountry($request->validated(), auth()->user());
        return new CountryResource($country);
    }

    public function show($id)
    {
        $country = $this->countryService->getCountryById($id);
        return new CountryResource($country);
    }

    public function update(CountryRequest $request, $id)
    {
        $country = $this->countryService->updateCountry($id, $request->validated());
        return new CountryResource($country);
    }

    public function destroy($id)
    {
        $this->countryService->deleteCountry($id);
        return response()->json(['message' => 'Country deleted successfully']);
    }
}
