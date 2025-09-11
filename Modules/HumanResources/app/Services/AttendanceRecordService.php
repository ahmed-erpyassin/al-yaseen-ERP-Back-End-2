<?php

namespace Modules\HumanResources\app\Services;

use Modules\HumanResources\Http\Requests\AttendanceRecordRequest;
use Modules\HumanResources\Http\Requests\EmployeeRequest;
use Modules\HumanResources\Models\AttendanceRecord;
use Modules\HumanResources\Models\Employee;

class AttendanceRecordService
{
    public function list()
    {
        return AttendanceRecord::paginate(10);
    }

    public function create(AttendanceRecordRequest $request): AttendanceRecord
    {
        return AttendanceRecord::create($request->validated() + [
            'created_by' => auth()->id(),
        ]);
    }

    public function update(AttendanceRecordRequest $request, AttendanceRecord $attendanceRecord): AttendanceRecord
    {
        $attendanceRecord->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return $attendanceRecord;
    }

    public function delete(AttendanceRecord $attendanceRecord): void
    {
        $attendanceRecord->update(['deleted_by' => auth()->id()]);
        $attendanceRecord->delete();
    }
}
