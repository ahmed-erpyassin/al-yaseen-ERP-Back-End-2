<?php

namespace Modules\Companies\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Companies\app\Services\IndustryService;
use Modules\Companies\Http\Requests\IndustryRequest;
use Modules\Companies\Transformers\IndustryResource;

class IndustriesController extends Controller
{
    protected $industryService;

    public function __construct(IndustryService $industryService)
    {
        $this->industryService = $industryService;
    }

    public function index(Request $request)
    {
        $industries = $this->industryService->getIndustries($request->user());
        return IndustryResource::collection($industries);
    }

    public function store(IndustryRequest $request)
    {
        $user = Auth::user();
        $industry = $this->industryService->createIndustry($request->validated(), $user);
        return new IndustryResource($industry);
    }

    public function show($id)
    {
        $industry = $this->industryService->getIndustryById($id);
        return new IndustryResource($industry);
    }

    public function update(IndustryRequest $request, $id)
    {
        $industry = $this->industryService->updateIndustry($id, $request->validated());
        return new IndustryResource($industry);
    }

    public function destroy($id)
    {
        $this->industryService->deleteIndustry($id);
        return response()->json(['message' => 'Industry deleted successfully']);
    }
}
