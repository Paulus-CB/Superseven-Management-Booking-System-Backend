<?php

namespace App\Services;

use App\Models\AddOn;
use App\Models\Billing;
use App\Models\Package;
use Carbon\Carbon;

class BookingService
{
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

    public function getDiscountPercentage(string $bookingDate)
    {
        $date = Carbon::parse($bookingDate);

        if ($date->greaterThanOrEqualTo(Carbon::now()->addYear())) {
            return 10;
        }

        return 0;
    }
}
