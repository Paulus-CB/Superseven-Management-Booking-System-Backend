<?php

namespace App\Services;

use App\Models\AddOn;
use App\Models\Billing;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class BookingService
{
    public function getFilterBookingData()
    {
        return [
            'booked' => [
                'type' => 'or',
                'condition' => "booking_status = " . Booking::STATUS_APPROVED,
            ],
            'pending' => [
                'type' => 'or',
                'condition' => "booking_status = " . Booking::STATUS_PENDING,
            ],
            'for_resched' => [
                'type'=> 'or',
                'condition'=> "booking_status = " . Booking::STATUS_FOR_RESCHEDULE,
            ],
        ];
    }

    public function createWalkinCustomer(string $firstName, string $lastName, string $address, string $email, string $contactNo)
    {
        $user = new User();
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->address = $address;
        $user->email = $email;
        $user->password = Hash::make(strtolower($firstName . ' ' . $lastName));
        $user->contact_num = $contactNo;
        $user->save();

        Customer::create([
            'user_id' => $user->id,
            'customer_type' => User::CLIENT_TYPE,
        ]);

        return $user;
    }

    public function createBillingStatement(int $bookingId, int $packageId, array $addonIds, ?float $discount = null)
    {
        $package = Package::findOrFail($packageId);
        $addOnAmount = AddOn::whereIn('id', $addonIds)->sum('add_on_price');

        $baseTotal = $package->package_price + $addOnAmount;

        // Apply discount if any
        $discountAmount = ($discount > 0) ? ($baseTotal * ($discount / 100)) : 0;
        $totalAmount = $baseTotal - $discountAmount;

        Billing::create([
            'booking_id' => $bookingId,
            'package_amount' => $package->package_price,
            'add_on_amount' => $addOnAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    public function updateBillingStatement(
        int $bookingId,
        int $packageId,
        array $addOnIds,
        ?float $discount = null
    ) {

        $billing = Billing::firstOrCreate( // Create if missing
            ['booking_id' => $bookingId],
            ['package_amount' => 0, 'add_on_amount' => 0, 'total_amount' => 0]
        );

        $package = Package::findOrFail($packageId);
        $addOnAmount = AddOn::whereIn('id', $addOnIds)->sum('add_on_price');

        $baseTotal = $package->package_price + $addOnAmount;
        $discountAmount = ($discount > 0) ? ($baseTotal * ($discount / 100)) : 0;

        $billing->update([
            'package_amount' => $package->package_price,
            'add_on_amount' => $addOnAmount,
            'total_amount' => $baseTotal - $discountAmount
        ]);
    }

    public function getDiscountPercentage(string $bookingDate)
    {
        $date = Carbon::parse($bookingDate);

        if ($date->greaterThanOrEqualTo(Carbon::now()->addYear())) {
            return 10;
        }

        return 0;
    }

    public function getUpcomingEvents()
    {
        $startOfWeek = Carbon::today();
        $endOfWeek = Carbon::today()->addDays(6)->endOfDay();

        return Booking::with('package')
            ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->orderBy('booking_date')
            ->get()
            ->map(function ($booking) {
                return [
                    'event_name' => $booking->event_name,
                    'booking_date' => Carbon::parse($booking->booking_date)->format('F d, Y'),
                    'package' => $booking->package->package_name ?? '',
                    'booking_address' => $booking->booking_address
                ];
            });
    }
}
