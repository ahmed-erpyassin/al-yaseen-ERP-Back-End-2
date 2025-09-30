<?php

namespace Modules\Purchases\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Purchases\Services\PurchaseReferenceInvoiceService;
use Modules\Purchases\Http\Requests\PurchaseReferenceInvoiceRequest;
use Modules\Purchases\Http\Resources\PurchaseReferenceInvoiceResource;

/**
 * @group Purchase Management / Reference Invoices
 *
 * APIs for managing purchase reference invoices, including invoice referencing, tracking, and reconciliation.
 */
class PurchaseReferenceInvoiceController extends Controller
{
    protected PurchaseReferenceInvoiceService $invoiceService;

    public function __construct(PurchaseReferenceInvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of purchase reference invoices
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->index($request);
            return response()->json([
                'success' => true,
                'data' => PurchaseReferenceInvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching purchase reference invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created purchase reference invoice
     */
    public function store(PurchaseReferenceInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->store($request);
            return response()->json([
                'success' => true,
                'message' => 'Purchase reference invoice created successfully.',
                'data' => new PurchaseReferenceInvoiceResource($invoice)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating purchase reference invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase reference invoice
     */
    public function show($id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new PurchaseReferenceInvoiceResource($invoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching purchase reference invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers for dropdown (with search)
     */
    public function getSuppliers(Request $request): JsonResponse
    {
        try {
            $suppliers = $this->invoiceService->getSuppliers($request);
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
     * Get items for dropdown (with search)
     */
    public function getItems(Request $request): JsonResponse
    {
        try {
            $items = $this->invoiceService->getItems($request);
            return response()->json([
                'success' => true,
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching items.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies(Request $request): JsonResponse
    {
        try {
            $currencies = $this->invoiceService->getCurrencies($request);
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
    public function getTaxRates(Request $request): JsonResponse
    {
        try {
            $taxRates = $this->invoiceService->getTaxRates($request);
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
    public function getLiveExchangeRate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'currency_id' => 'required|exists:currencies,id'
            ]);

            $rate = $this->invoiceService->getLiveExchangeRate($request->currency_id);
            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $request->currency_id,
                    'exchange_rate' => $rate
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
     * Get complete form data for creating purchase reference invoice
     */
    public function getFormData(Request $request): JsonResponse
    {
        try {
            $formData = $this->invoiceService->getFormData($request);
            return response()->json([
                'success' => true,
                'data' => $formData
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
     * Update the specified purchase reference invoice
     */
    public function update(PurchaseReferenceInvoiceRequest $request, $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->update($id, $request);
            return response()->json([
                'success' => true,
                'message' => 'Purchase reference invoice updated successfully.',
                'data' => new PurchaseReferenceInvoiceResource($invoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating purchase reference invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified purchase reference invoice
     */
    public function destroy($id): JsonResponse
    {
        try {
            $result = $this->invoiceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting purchase reference invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deleted purchase reference invoices
     */
    public function getDeleted(Request $request): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getDeleted($request);
            return response()->json([
                'success' => true,
                'data' => PurchaseReferenceInvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted purchase reference invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore deleted purchase reference invoice
     */
    public function restore($id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->restore($id);
            return response()->json([
                'success' => true,
                'message' => 'Purchase reference invoice restored successfully.',
                'data' => new PurchaseReferenceInvoiceResource($invoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring purchase reference invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for purchase reference invoices
     */
    public function getSearchFormData(Request $request): JsonResponse
    {
        try {
            $formData = $this->invoiceService->getSearchFormData($request);
            return response()->json([
                'success' => true,
                'data' => $formData
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
     * Get sortable fields for purchase reference invoices
     */
    public function getSortableFields(): JsonResponse
    {
        try {
            $sortableFields = $this->invoiceService->getSortableFields();
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
