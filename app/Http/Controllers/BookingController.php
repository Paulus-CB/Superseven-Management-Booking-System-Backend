<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function addBooking(AddBookingRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            $discount = $this->bookingService->getDiscountPercentage($request->booking_date);

            $booking = Booking::create([
                'booking_date' => $request->booking_date,
                'customer_id' => $request->customer_id,
                'package_id' => $request->package_id,
                'event_name' => $request->event_name,
                'booking_address' => $request->booking_address,
                'completion_date' => $request->completion_date,
                'booking_status' => Booking::STATUS_APPROVED,
                'discount' => $discount,
            ]);

            $addOnIds = $request->input('addon_id', []);

            if (!empty($addOnIds)) {
                $booking->addons()->attach($addOnIds);

                $this->bookingService->createBillingStatement($booking->id, $request->package_id, $addOnIds, $discount);
            }

            DB::commit();
            return $this->sendResponse('Booking created successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function updateBooking(int $bookingId, UpdateBookingRequest $request)
    {
        $request->validated();

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return $this->sendError('Booking not found.');
        }

        DB::beginTransaction();
        try {

            $booking->booking_date = $request->booking_date;
            $booking->event_name = $request->event_name;
            $booking->booking_address = $request->booking_address;

            $addOnIds = $request->input('addon_id', []);

            $packageChanged = $booking->package_id != $request->package_id;
            $addOnsChanged = array_diff($booking->addons->pluck('id')->toArray(), $addOnIds)
                        || array_diff($addOnIds, $booking->addons->pluck('id')->toArray());

            if ($packageChanged || $addOnsChanged) {
                $booking->addons()->sync($addOnIds);

                $this->bookingService->updateBillingStatement($booking->id, $request->package_id, $addOnIds, $booking->discount);
                $booking->package_id = $request->package_id;
            }

            $booking->save();

            DB::commit();
            return $this->sendResponse('Booking updated successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
