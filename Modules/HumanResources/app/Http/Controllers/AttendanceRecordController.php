<?php

namespace Modules\HumanResources\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HumanResources\app\Services\AttendanceRecordService;
use Modules\HumanResources\Http\Requests\AttendanceRecordRequest;
use Modules\HumanResources\Models\AttendanceRecord;
use Modules\HumanResources\Transformers\AttendanceRecordResource;

class AttendanceRecordController extends Controller
{
    protected AttendanceRecordService $service;

    public function __construct(AttendanceRecordService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return AttendanceRecordResource::collection($this->service->list());
    }

    public function store(AttendanceRecordRequest $request)
    {
        $attendanceRecord = $this->service->create($request);
        return new AttendanceRecordResource($attendanceRecord);
    }

    public function update(AttendanceRecordRequest $request, AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord = $this->service->update($request, $attendanceRecord);
        return new AttendanceRecordResource($attendanceRecord);
    }

    public function destroy(AttendanceRecord $attendanceRecord)
    {
        $this->service->delete($attendanceRecord);
        return response()->noContent();
    }
}
