<?php

namespace Modules\HumanResources\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HumanResources\app\Services\LeaveRequestService;
use Modules\HumanResources\Http\Requests\LeaveRequestRequest;
use Modules\HumanResources\Models\LeaveRequest;
use Modules\HumanResources\Transformers\LeaveRequestResource;

class LeaveRequestController extends Controller
{
    protected LeaveRequestService $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }

    /**
     * Display a listing of leave requests.
     */
    public function index()
    {
        return LeaveRequestResource::collection($this->leaveRequestService->list());
    }

    /**
     * Store a newly created leave request.
     */
    public function store(LeaveRequestRequest $request)
    {
        return new LeaveRequestResource($this->leaveRequestService->create($request->validated()));
    }

    /**
     * Update the specified leave request.
     */
    public function update(LeaveRequestRequest $request, LeaveRequest $leaveRequest)
    {
        return new LeaveRequestResource($this->leaveRequestService->update($request->validated(), $leaveRequest));
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->leaveRequestService->delete($leaveRequest);
        return response()->json(['message' => 'Leave request deleted successfully.']);
    }
}
