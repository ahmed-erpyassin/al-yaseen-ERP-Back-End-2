<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\ServiceService;
use Modules\Sales\Http\Requests\ServiceRequest;
use Modules\Sales\Transformers\ServiceResource;

/**
 * @group Sales Management / Services
 *
 * APIs for managing sales services, including service creation, tracking, and customer service management.
 */
class ServiceController extends Controller
{
    protected ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->serviceService->index($request);
            return response()->json([
                'success' => true,
                'data'    => ServiceResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching services.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        try {
            $service = $this->serviceService->store($request);
            return response()->json([
                'success' => true,
                'data' => new ServiceResource($service)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for customers
     */
    public function searchCustomers(Request $request)
    {
        try {
            $customers = $this->serviceService->searchCustomers($request);
            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching customers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for accounts with advanced filtering
     */
    public function searchAccounts(Request $request)
    {
        try {
            $accounts = $this->serviceService->searchAccounts($request);
            return response()->json([
                'success' => true,
                'data' => $accounts,
                'message' => 'Accounts retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching accounts.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all account numbers for dropdown (read-only)
     */
    public function getAllAccountNumbers(Request $request)
    {
        try {
            $accountNumbers = $this->serviceService->getAllAccountNumbers($request);
            return response()->json([
                'success' => true,
                'data' => $accountNumbers,
                'message' => 'Account numbers retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching account numbers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account details by account number
     */
    public function getAccountByNumber(Request $request)
    {
        try {
            $account = $this->serviceService->getAccountByNumber($request);
            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Account details retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching account details.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account details by account name
     */
    public function getAccountByName(Request $request)
    {
        try {
            $account = $this->serviceService->getAccountByName($request);
            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Account details retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching account details.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating/editing services
     */
    public function getFormData(Request $request)
    {
        try {
            $formData = $this->serviceService->getFormData($request);
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => 'Form data retrieved successfully'
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
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $service = $this->serviceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new ServiceResource($service),
                'message' => 'Service retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, $id)
    {
        try {
            $service = $this->serviceService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new ServiceResource($service),
                'message' => 'Service updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $result = $this->serviceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for services
     */
    public function search(Request $request)
    {
        try {
            $services = $this->serviceService->search($request);
            return response()->json([
                'success' => true,
                'data' => ServiceResource::collection($services->items()),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem()
                ],
                'message' => 'Services retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching services.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for services
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $formData = $this->serviceService->getSearchFormData($request);
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => 'Search form data retrieved successfully'
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
     * Get sortable fields for services
     */
    public function getSortableFields()
    {
        try {
            $fields = $this->serviceService->getSortableFields();
            return response()->json([
                'success' => true,
                'data' => $fields,
                'message' => 'Sortable fields retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching sortable fields.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deleted services (soft deleted)
     */
    public function getDeleted(Request $request)
    {
        try {
            $services = $this->serviceService->getDeleted($request);
            return response()->json([
                'success' => true,
                'data' => ServiceResource::collection($services->items()),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem()
                ],
                'message' => 'Deleted services retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted services.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted service
     */
    public function restore($id)
    {
        try {
            $result = $this->serviceService->restore($id);
            return response()->json([
                'success' => true,
                'message' => 'Service restored successfully',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a service (permanent deletion)
     */
    public function forceDelete($id)
    {
        try {
            $result = $this->serviceService->forceDelete($id);
            return response()->json([
                'success' => true,
                'message' => 'Service permanently deleted',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while permanently deleting service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
