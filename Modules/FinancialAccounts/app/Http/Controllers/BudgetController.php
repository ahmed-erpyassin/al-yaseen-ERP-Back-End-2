<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Http\Requests\BudgetRequest;
use Modules\FinancialAccounts\Services\BudgetService;
use Modules\FinancialAccounts\Transformers\BudgetResource;

class BudgetController extends Controller
{
    protected $budgetService;

    public function __construct(BudgetService $service)
    {
        $this->budgetService = $service;
    }

    public function index(Request $request)
    {
        $budgets = $this->budgetService->getBudgets($request->user());
        return BudgetResource::collection($budgets);
    }

    public function store(BudgetRequest $request)
    {
        $budget = $this->budgetService->createBudget($request->validated(), $request->user());
        return new BudgetResource($budget);
    }

    public function show($id)
    {
        $budget = $this->budgetService->getById($id);
        return new BudgetResource($budget);
    }

    public function update(BudgetRequest $request, $id)
    {
        $budget = $this->budgetService->updateBudget($id, $request->validated());
        return new BudgetResource($budget);
    }

    public function destroy(Request $request, $id)
    {
        $this->budgetService->deleteBudget($id, $request->user()->id);
        return response()->json(['message' => 'Budget deleted successfully']);
    }
}
