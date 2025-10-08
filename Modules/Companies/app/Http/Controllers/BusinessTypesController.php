<?php

namespace Modules\Companies\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Companies\app\Services\BusinessTypeService;
use Modules\Companies\Http\Requests\BusinessTypeRequest;
use Modules\Companies\Transformers\BusinessTypeResource;

class BusinessTypesController extends Controller
{
    protected $businessTypeService;

    public function __construct(BusinessTypeService $businessTypeService)
    {
        $this->businessTypeService = $businessTypeService;
    }

    public function index(Request $request)
    {
        $businessTypes = $this->businessTypeService->getBusinessTypes($request->user());
        return BusinessTypeResource::collection($businessTypes);
    }

    public function store(BusinessTypeRequest $request)
    {
        $user = auth('sanctum')->user();
        $businessType = $this->businessTypeService->createBusinessType($request->validated(), $user);
        return new BusinessTypeResource($businessType);
    }

    public function show($id)
    {
        $businessType = $this->businessTypeService->getBusinessTypeById($id);
        return new BusinessTypeResource($businessType);
    }

    public function update(BusinessTypeRequest $request, $id)
    {
        $businessType = $this->businessTypeService->updateBusinessType($id, $request->validated());
        return new BusinessTypeResource($businessType);
    }

    public function destroy($id)
    {
        $this->businessTypeService->deleteBusinessType($id);
        return response()->json(['message' => 'Business type deleted successfully']);
    }
}
