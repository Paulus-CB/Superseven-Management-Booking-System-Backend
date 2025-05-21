<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddBookingRequest;
use App\Http\Requests\Booking\PaginateRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingAddOnsResource;
use App\Http\Resources\Collections\BookingCollection;
use App\Models\Booking;
use \App\Models\Package;
use \App\Models\AddOn;
use App\Services\BookingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getBookings(PaginateRequest $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $bookings = Booking::with('customer', 'package', 'addOns')
            ->when(isset($request->filters), function ($query) use ($request) {
                $query->where(function ($subquery) use ($request) {
                    $this->filterCallback($subquery, $request, $this->bookingService->getFilterBookingData());
                });
            })
            ->whereMonth('booking_date', $month)
            ->whereYear('booking_date', $year)
            ->orderBy('booking_date')
            ->get();

            return $this->sendResponse('Bookings retrieved successfully.', new BookingCollection($bookings));
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

    public function deleteBooking(int $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return $this->sendError('Booking not found.');
        }

        DB::beginTransaction();
        try {
            $booking->delete();

            DB::commit();
            return $this->sendResponse('Booking deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function getAvailablePackages()
    {
        $packages = Package::all('id','package_name');

        return $this->sendResponse('Packages retrieved successfully.', $packages);
    }

    public function getAvailableAddons(int $id)
    {
        $addons = Addon::all();

        return $this->sendResponse('Addons retrieved successfully.', BookingAddOnsResource::collection($addons));
    }
}
