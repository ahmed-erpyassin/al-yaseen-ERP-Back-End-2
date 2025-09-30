<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\ExpenseService;
use Modules\Purchases\Http\Requests\ExpenseRequest;
use Modules\Purchases\Http\Resources\ExpenseResource;

/**
 * @group Purchase Management / Expenses
 *
 * APIs for managing purchase expenses, including expense tracking, categorization, and financial reporting.
 */
class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display a listing of expenses with search and sorting
     */
    public function index(Request $request)
    {
        try {
            $expenses = $this->expenseService->index($request);
            return response()->json([
                'success' => true,
                'data' => ExpenseResource::collection($expenses->items()),
                'pagination' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'per_page' => $expenses->perPage(),
                    'total' => $expenses->total(),
                    'from' => $expenses->firstItem(),
                    'to' => $expenses->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching expenses.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created expense
     */
    public function store(ExpenseRequest $request)
    {
        try {
            $expense = $this->expenseService->store($request);
            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully.',
                'data' => new ExpenseResource($expense)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating expense.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified expense with all related data
     */
    public function show(Request $request, $id)
    {
        try {
            $expense = $this->expenseService->show($id);
            return response()->json([
                'success' => true,
                'data' => new ExpenseResource($expense)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching expense.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified expense
     */
    public function update(ExpenseRequest $request, $id)
    {
        try {
            $expense = $this->expenseService->update($request, $id);
            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully.',
                'data' => new ExpenseResource($expense)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating expense.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified expense
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->expenseService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting expense.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted expense
     */
    public function restore(Request $request, $id)
    {
        try {
            $expense = $this->expenseService->restore($id);
            return response()->json([
                'success' => true,
                'message' => 'Expense restored successfully.',
                'data' => new ExpenseResource($expense)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring expense.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get soft deleted expenses
     */
    public function getDeleted(Request $request)
    {
        try {
            $expenses = $this->expenseService->getDeleted($request);
            return response()->json([
                'success' => true,
                'data' => ExpenseResource::collection($expenses->items()),
                'pagination' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'per_page' => $expenses->perPage(),
                    'total' => $expenses->total(),
                    'from' => $expenses->firstItem(),
                    'to' => $expenses->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted expenses.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers for dropdown (with search)
     */
    public function getSuppliers(Request $request)
    {
        try {
            $suppliers = $this->expenseService->getSuppliers($request);
            return response()->json([
                'success' => true,
                'data' => $suppliers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching suppliers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get accounts for dropdown (with search)
     */
    public function getAccounts(Request $request)
    {
        try {
            $accounts = $this->expenseService->getAccounts($request);
            return response()->json([
                'success' => true,
                'data' => $accounts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching accounts.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies(Request $request)
    {
        try {
            $currencies = $this->expenseService->getCurrencies($request);
            return response()->json([
                'success' => true,
                'data' => $currencies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching currencies.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax rates for dropdown
     */
    public function getTaxRates(Request $request)
    {
        try {
            $taxRates = $this->expenseService->getTaxRates($request);
            return response()->json([
                'success' => true,
                'data' => $taxRates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching tax rates.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live exchange rate for currency
     */
    public function getLiveExchangeRate(Request $request)
    {
        try {
            $currencyId = $request->get('currency_id');
            if (!$currencyId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency ID is required.'
                ], 400);
            }

            $exchangeRate = $this->expenseService->getLiveExchangeRate($currencyId);
            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $currencyId,
                    'exchange_rate' => $exchangeRate,
                    'updated_at' => now()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching exchange rate.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get complete form data for expense creation
     */
    public function getFormData(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'suppliers' => $this->expenseService->getSuppliers($request),
                    'accounts' => $this->expenseService->getAccounts($request),
                    'currencies' => $this->expenseService->getCurrencies($request),
                    'tax_rates' => $this->expenseService->getTaxRates($request),
                    'next_expense_number' => \Modules\Purchases\Models\Purchase::generateExpenseNumber(),
                    'current_date' => now()->toDateString(),
                    'current_time' => now()->toTimeString(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for expenses
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $searchData = $this->expenseService->getSearchFormData($request);
            return response()->json([
                'success' => true,
                'data' => $searchData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching search form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for expenses
     */
    public function getSortableFields(Request $request)
    {
        try {
            $sortableFields = $this->expenseService->getSortableFields();
            return response()->json([
                'success' => true,
                'data' => $sortableFields
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching sortable fields.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
