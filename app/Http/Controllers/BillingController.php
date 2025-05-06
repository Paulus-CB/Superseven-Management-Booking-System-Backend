<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\BillingResource;
use App\Http\Resources\Collections\BillingCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Billing;
use App\Models\Booking;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends BaseController
{
    public function getBillings(PaginateRequest $request)
    {
        $startYear = $request->start_year;
        $endYear = $request->end_year;

        $billings = Booking::with('billing.latestPayment', 'billing.payments', 'customer', 'package', 'addOns')
            ->when(isset($request->search), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $this->searchCallback($query, $request, ['event_name', 'customer.first_name', 'customer.last_name', 'package.package_name']);
                });
            })
            ->whereYear('booking_date', '>=', $startYear)
            ->whereYear('booking_date', '<=', $endYear)
            ->orderBy(Billing::select('billing_status')
                ->whereColumn('booking_id', 'bookings.id')
                ->limit(1));


        $paginated = $billings->paginate(self::PER_PAGE);

        return $this->sendResponse('Billings retrieved successfully.', new BillingCollection($paginated));
    }

    public function viewBilling(int $billingId)
    {
        $billing = Booking::with('billing.latestPayment', 'billing.payments', 'customer', 'package', 'addOns')
            ->whereHas('billing', function ($query) use ($billingId) {
                $query->where('id', $billingId);
            })->first();

        if (!$billing) {
            return $this->sendError('Billing not found.', 404);
        }

        return $this->sendResponse('Billing retrieved successfully.', new BillingResource($billing));
    }

    public function addPayment(int $billingId, PaymentRequest $request)
    {
        $request->validated();

        $billing = Billing::find($billingId);

        if (!$billing) {
            return $this->sendError('Billing not found.', 404);
        }

        DB::beginTransaction();
        try {

            $totalAmount = $billing->total_amount;
            $paidAmount = $billing->payments->sum('amount_paid');
            $currentAmount = $request->amount;
            $newBalance = max(0, $totalAmount - ($paidAmount + $currentAmount));

            $newStatus = $newBalance === 0
                ? Billing::STATUS_PAID
                : (($paidAmount + $currentAmount) === 0
                    ? Billing::STATUS_UNPAID
                    : Billing::STATUS_PARTIAL);

            Payment::create([
                'billing_id' => $billingId,
                'payment_date' => now(),
                'amount_paid' => $request->amount,
                'payment_method' => $request->payment_method,
                'balance' => $newBalance,
                'remarks' => $request->remarks ?? null,
            ]);

            $billing->update([
                'billing_status' => $newStatus,
            ]);

            DB::commit();
            return $this->sendResponse('Payment created successfully.', TransactionResource::collection(Payment::where('billing_id', $billingId)->orderBy('payment_date')->get()));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
