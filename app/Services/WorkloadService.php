<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WorkloadService
{
    public function getFilterWorkloadData($filter = [])
    {
        return [
            'deliverable_status' => [
                'type' => 'raw',
                'condition' => 'deliverable_status = ' . $filter['deliverable_status'],
            ]
        ];
    }

    public function updateBookingStatus(Booking $booking, int $newWorkloadStatus, User $employee)
    {
        $currentStatus = $booking->deliverable_status;
        $newBookingStatus = $currentStatus;

        $employeeType = $employee->employee->employee_type;

        if ($employeeType === User::PHOTOGRAPHER_TYPE) {
            if ($newWorkloadStatus === Booking::STATUS_UPLOADED) {
                $newBookingStatus = Booking::STATUS_UPLOADED;
            }
        }

        if ($employeeType === User::EDITOR_TYPE) {
            if ($newWorkloadStatus === Booking::STATUS_EDITING) {
                $newBookingStatus = Booking::STATUS_EDITING;
            } elseif ($newWorkloadStatus === Booking::STATUS_FOR_RELEASE) {

                $editorStatuses = $this->getEditorStatuses($booking);

                if ($editorStatuses->every(fn($status) => $status == Booking::STATUS_FOR_RELEASE)) {
                    $newBookingStatus = Booking::STATUS_FOR_RELEASE;
                } else {
                    $newBookingStatus = Booking::STATUS_EDITING;
                }
            }
        }

        if ($newBookingStatus !== $currentStatus) {
            $booking->deliverable_status = $newBookingStatus;
            $booking->save();

            $this->notifyStatusChange($booking, $currentStatus, $newBookingStatus);
        }
    }

    private function getEditorStatuses(Booking $booking)
    {
        return $booking->employees()
            ->whereHas('employee', function ($query) {
                $query->where('employee_type', User::EDITOR_TYPE);
            })
            ->withPivot('workload_status')
            ->get()
            ->pluck('pivot.workload_status');
    }

    private function notifyStatusChange(Booking $booking, int $oldStatus, int $newStatus)
    {
        Log::info("Booking status updated", [
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        // Future notification implementation will go here
    }
}
