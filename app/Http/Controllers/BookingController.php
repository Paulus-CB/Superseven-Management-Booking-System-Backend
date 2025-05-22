<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddBookingRequest;
use App\Http\Requests\Booking\PaginateRequest;
use App\Http\Requests\ReschedBookingRequest;
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
use Illuminate\Http\Request;
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
            ->orderBy('created_at')
            ->get();

        return $this->sendResponse('Bookings retrieved successfully.', new BookingCollection($bookings));
    }

    public function addBooking(AddBookingRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            //create walk in customer account
            $customer = $this->bookingService->createWalkinCustomer($request->first_name, $request->last_name, $request->address, $request->email, $request->contact_no);

            // calculate discount
            $discount = $this->bookingService->getDiscountPercentage($request->booking_date);

            // create booking
            $booking = Booking::create([
                'booking_date' => $request->booking_date,
                'customer_id' => $customer->id,
                'package_id' => $request->package_id,
                'event_name' => $request->event_name,
                'booking_address' => $request->booking_address,
                'completion_date' => $request->completion_date,
                'booking_status' => Booking::STATUS_PENDING,
                'discount' => $discount,
            ]);

            $addOnIds = (array) $request->input('addon_id', []);

            $booking->addons()->sync($addOnIds);

            $this->bookingService->createBillingStatement(
                $booking->id,
                $request->package_id,
                $addOnIds,
                $discount
            );

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
        $booking = Booking::findOrFail($bookingId);

        DB::beginTransaction();
        try {
            // Update basic fields
            $booking->fill([
                'booking_date' => $request->booking_date,
                'event_name' => $request->event_name,
                'booking_address' => $request->booking_address,
            ]);

            // Check for discount changes
            $shouldUpdateBilling = false;
            if ($booking->isDirty('booking_date')) {
                $booking->discount = $this->bookingService
                    ->getDiscountPercentage($request->booking_date);
                $shouldUpdateBilling = true;
            }

            // Check for package/add-on changes
            $addOnIds = (array) $request->input('addon_id', []);
            $currentAddOns = $booking->addons()->pluck('add_on_id')->toArray();

            $packageChanged = $booking->package_id != $request->package_id;
            $addOnsChanged = count(array_diff($currentAddOns, $addOnIds)) > 0
                || count(array_diff($addOnIds, $currentAddOns)) > 0;

            if ($packageChanged || $addOnsChanged || $shouldUpdateBilling) {
                $booking->package_id = $request->package_id;;
                $booking->addons()->sync($addOnIds);

                $this->bookingService->updateBillingStatement(
                    $booking->id,
                    $request->package_id,
                    $addOnIds,
                    $booking->discount
                );
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

    public function rescheduleBooking(int $bookingId, ReschedBookingRequest $request)
    {
        $validated = $request->validated();

        $booking = Booking::where('id', $bookingId)
            ->where('booking_status', Booking::STATUS_FOR_RESCHEDULE)->first();

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->booking_date = $validated['booking_date'];
            $booking->booking_status = Booking::STATUS_PENDING;
            $booking->save();

            DB::commit();
            return $this->sendResponse('Booking rescheduled successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function getAvailablePackages()
    {
        $packages = Package::all('id', 'package_name');

        return $this->sendResponse('Packages retrieved successfully.', $packages);
    }

    public function getAvailableAddons(int $id)
    {
        $addons = Addon::all();

        return $this->sendResponse('Addons retrieved successfully.', BookingAddOnsResource::collection($addons));
    }
}
