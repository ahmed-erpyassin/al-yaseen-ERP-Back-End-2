<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\app\Services\CostCenterService;
use Modules\FinancialAccounts\Http\Requests\CostCenterRequest;
use Modules\FinancialAccounts\Models\CostCenter;
use Modules\FinancialAccounts\Transformers\CostCenterResource;

class CostCenterController extends Controller
{
    protected $costCenterService;

    public function __construct(CostCenterService $service)
    {
        $this->costCenterService = $service;
    }

    public function index(Request $request)
    {
        $cost_centers = $this->costCenterService->getCostCenters($request->user());
        return CostCenterResource::collection($cost_centers);
    }

    public function store(CostCenterRequest $request)
    {
        $cost_center = $this->costCenterService->createCostCenter($request->validated(), $request->user());
        return new CostCenterResource($cost_center);
    }

    public function show($id)
    {
        $cost_center = $this->costCenterService->getCostCenterById($id);
        return new CostCenterResource($cost_center);
    }

    public function update(CostCenterRequest $request, $id)
    {
        $cost_center = $this->costCenterService->updateCostCenter($id, $request->validated());
        return new CostCenterResource($cost_center);
    }

    public function destroy(Request $request, $id)
    {
        $this->costCenterService->deleteCostCenter($id, $request->user()->id);
        return response()->json(['message' => 'Cost Center deleted successfully']);
    }
}
