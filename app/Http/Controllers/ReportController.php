<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    public function getNoOfBookings(GenerateReportRequest $request)
    {
        // Get year from request or default to current year
        $year = $request->input('year', now()->year);

        $bookings = Booking::query()
            ->selectRaw('MONTH(booking_date) as month, COUNT(*) as count')
            ->whereYear('booking_date', $year)
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->groupByRaw('MONTH(booking_date)')
            ->orderByRaw('MONTH(booking_date)')
            ->pluck('count', 'month');

        // Map month numbers to names and fill missing months with 0
        $monthlyData = collect(range(1, 12))->mapWithKeys(function ($month) use ($bookings) {
            $monthName = \Carbon\Carbon::create()->month($month)->format('F');
            return [$monthName => $bookings->get($month, 0)];
        });

        return $this->sendResponse('Report generated successfully.', $monthlyData);
    }

    public function getNoOfPackages(GenerateReportRequest $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        $packages = Package::withCount([
            'bookings as bookings_count' => function ($query) use ($year, $month) {
                $query->where('booking_status', Booking::STATUS_APPROVED);
    
                if ($year) {
                    $query->whereYear('booking_date', $year);
                }
    
                if ($month) {
                    $query->whereMonth('booking_date', $month);
                }
            }
        ])->get();

        $result = $packages->map(function ($package) {
            return [
                'package_name' => $package->package_name,
                'count' => $package->bookings_count,
            ];
        });

        return $this->sendResponse('Report generated successfully.', $result);
    }

    public function getTransactions(GenerateReportRequest $request)
    {
        $startYear = $request->input('start_year', now()->year);
        $endYear = $request->input('end_year', now()->addYear()->year);

        
    }
}
