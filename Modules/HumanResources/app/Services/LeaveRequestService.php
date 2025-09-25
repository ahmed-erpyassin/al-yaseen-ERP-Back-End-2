<?php

namespace Modules\HumanResources\app\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\HumanResources\Http\Requests\LeaveRequestRequest;
use Modules\HumanResources\Models\LeaveRequest;

class LeaveRequestService
{

    public function list()
    {
        return LeaveRequest::paginate(10);
    }

    public function create(LeaveRequestRequest $request): LeaveRequest
    {
        return LeaveRequest::create($request->validated() + [
            'created_by' => Auth::id(),
        ]);
    }

    public function update(LeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequest
    {
        $leaveRequest->update($request->validated() + [
            'updated_by' => Auth::id(),
        ]);

        return $leaveRequest;
    }

    public function delete(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->update(['deleted_by' => Auth::id()]);
        $leaveRequest->delete();
    }
}
