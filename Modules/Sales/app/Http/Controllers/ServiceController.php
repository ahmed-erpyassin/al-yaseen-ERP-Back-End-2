<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\ServiceService;
use Modules\Sales\Http\Requests\ServiceRequest;
use Modules\Sales\Transformers\ServiceResource;

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
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
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
            return response()->json(['error' => 'An error occurred while fetching services'], 500);
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
            $this->serviceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting service.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
