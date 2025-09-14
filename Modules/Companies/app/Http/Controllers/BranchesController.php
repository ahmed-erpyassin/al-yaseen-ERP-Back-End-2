<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Companies\Http\Requests\BranchRequest;
use Modules\Companies\Services\BranchService;
use Modules\Companies\Transformers\BranchResource;

class BranchesController extends Controller
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index(Request $request)
    {
        $branches = $this->branchService->getBranches($request->user());
        return BranchResource::collection($branches);
    }

    public function store(BranchRequest $request)
    {
        $user = Auth::user();
        $branch = $this->branchService->createBranch($request->validated(), $user);
        return new BranchResource($branch);
    }

    public function show($id)
    {
        $branch = $this->branchService->getBranchById($id);
        return new BranchResource($branch);
    }

    public function update(BranchRequest $request, $id)
    {
        $branch = $this->branchService->updateBranch($id, $request->validated());
        return new BranchResource($branch);
    }

    public function destroy($id)
    {
        $this->branchService->deleteBranch($id);
        return response()->json(['message' => 'Branch deleted successfully']);
    }
}
