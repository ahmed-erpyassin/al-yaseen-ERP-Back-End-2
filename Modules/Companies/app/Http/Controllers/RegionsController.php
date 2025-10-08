<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Companies\Http\Requests\RegionRequest;
use Modules\Companies\Services\RegionService;
use Modules\Companies\Transformers\RegionResource;

class RegionsController extends Controller
{
    protected $regionService;

    public function __construct(RegionService $regionService)
    {
        $this->regionService = $regionService;
    }

    public function index(Request $request)
    {
        $regions = $this->regionService->getRegions($request->user());
        return RegionResource::collection($regions);
    }

    public function store(RegionRequest $request)
    {
        $user = auth('sanctum')->user();
        $region = $this->regionService->createRegion($request->validated(), $user);
        return new RegionResource($region);
    }

    public function show($id)
    {
        $region = $this->regionService->getRegionById($id);
        return new RegionResource($region);
    }

    public function update(RegionRequest $request, $id)
    {
        $region = $this->regionService->updateRegion($id, $request->validated());
        return new RegionResource($region);
    }

    public function destroy($id)
    {
        $this->regionService->deleteRegion($id);
        return response()->json(['message' => 'Region deleted successfully']);
    }
}
