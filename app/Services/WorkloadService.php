<?php

namespace App\Services;

use App\Mail\Admin\WorkloadUploaded;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

            $this->notifyStatusChange($booking, $currentStatus, $newBookingStatus, $employee->full_name);
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

    private function notifyStatusChange(Booking $booking, int $oldStatus, int $newStatus, string $employeeName)
    {
        $formatStatus = Booking::STATUS[$newStatus];

        if ($newStatus === Booking::STATUS_UPLOADED) {
            $recipients = $this->fetchOwners();

            foreach ($recipients as $recipient) {
                $toSend = new WorkloadUploaded($booking, $formatStatus, $recipient->first_name, $employeeName);

                Mail::to($recipient->email)->queue($toSend);
            }
        }

        if ($newStatus === Booking::STATUS_EDITING) {
            $recipients = $this->fetchOwners();

            foreach ($recipients as $recipient) {
                
            }
        }

        if ($newStatus === Booking::STATUS_FOR_RELEASE) {

        }

    }

    private function fetchOwners()
    {
        $owners = User::has('employee')
            ->whereHas('employee', function ($query) {
                $query->where('employee_type', User::OWNER_TYPE);
            })->get();

        return $owners ?? [];
    }
}
