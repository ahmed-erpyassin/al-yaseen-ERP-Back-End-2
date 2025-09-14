<?php

namespace Modules\HumanResources\app\Services;

use Illuminate\Http\Request;
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
            'created_by' => auth()->id(),
        ]);
    }

    public function update(LeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequest
    {
        $leaveRequest->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return $leaveRequest;
    }

    public function delete(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->update(['deleted_by' => auth()->id()]);
        $leaveRequest->delete();
    }
}
