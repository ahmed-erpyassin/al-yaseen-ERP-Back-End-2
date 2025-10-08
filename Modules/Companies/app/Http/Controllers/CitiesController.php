<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Companies\Http\Requests\CityRequest;
use Modules\Companies\Services\CityService;
use Modules\Companies\Transformers\CityResource;

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
        $user = auth('sanctum')->user();
        $city = $this->cityService->createCity($request->validated(), $user);
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
