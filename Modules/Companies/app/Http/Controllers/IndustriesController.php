<?php

namespace Modules\Companies\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $industry = $this->industryService->createIndustry($request->validated(), $user);
            DB::commit();
            return new IndustryResource($industry);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $industry = $this->industryService->getIndustryById($id);
        return new IndustryResource($industry);
    }

    public function update(IndustryRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $industry = $this->industryService->updateIndustry($id, $request->validated());
            DB::commit();
            return new IndustryResource($industry);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->industryService->deleteIndustry($id);
            DB::commit();
            return response()->json(['message' => 'Industry deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
