<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CitiesController extends Controller
{
    protected $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function index(Request $request)
    {
        $cities = $this->cityService->getCities($request->user());
        return CityResource::collection($cities);
    }

    public function store(CityRequest $request)
    {
        $city = $this->cityService->createCity($request->validated(), auth()->user());
        return new CityResource($city);
    }

    public function show($id)
    {
        $city = $this->cityService->getCityById($id);
        return new CityResource($city);
    }

    public function update(CityRequest $request, $id)
    {
        $city = $this->cityService->updateCity($id, $request->validated());
        return new CityResource($city);
    }

    public function destroy($id)
    {
        $this->cityService->deleteCity($id);
        return response()->json(['message' => 'City deleted successfully']);
    }
}
