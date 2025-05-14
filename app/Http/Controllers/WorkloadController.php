<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkloadController extends BaseController
{
    public function assignWorkload(int $id, Request $request)
    {
        $request->validated();

        $booking = Booking::find($id);

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $userIds = $request->input('user_id', []);

            // $ex

        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
