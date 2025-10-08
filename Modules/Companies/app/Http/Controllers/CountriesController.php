<?php

namespace Modules\Companies\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Companies\Http\Requests\CountryRequest;
use Modules\Companies\Services\CountryService;
use Modules\Companies\Transformers\CountryResource;

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
        $user = auth('sanctum')->user();
        $country = $this->countryService->createCountry($request->validated(), $user);
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
