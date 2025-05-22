<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DateController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\User\Customer\CustomerAccountController;
use App\Http\Controllers\User\Employee\EmployeeAccountController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\WorkloadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/users')->group(function () {
        //Authenticated
        Route::get('/current', [AuthController::class, 'user']);
        Route::post('/update', [UserController::class, 'updateCurrent']);
        Route::post('/update-password', [UserController::class, 'updatePassword']);
    });

    //Admin/Staff Routes

    //Employees
    Route::prefix('/employees')->group(function () {
        Route::get('', [EmployeeAccountController::class, 'getEmployees']);
        Route::post('/add', [EmployeeAccountController::class, 'addUser']);
        Route::post('/{userId}', [EmployeeAccountController::class, 'updateUser']);
    });

    //Customers
    Route::prefix('/customers')->group(function () {
        Route::get('', [CustomerAccountController::class, 'getCustomers']);
        Route::post('/add', [CustomerAccountController::class, 'addUser']);
        Route::post('/{userId}', [CustomerAccountController::class, 'updateUser']);
    });

    //Package
    Route::prefix('/packages')->group(function () {
        Route::get('', [PackageController::class, 'getPackages']);
        Route::post('/add', [PackageController::class, 'addPackage']);
        Route::post('/{id}', [PackageController::class, 'updatePackage']);
        Route::post('/{id}/delete', [PackageController::class, 'deletePackage']);
    });

    //Addon
    Route::prefix('/addons')->group(function () {
        Route::get('', [AddonController::class, 'getAddons']);
        Route::post('/add', [AddonController::class, 'addAddon']);
        Route::post('/{id}', [AddonController::class, 'updateAddon']);
        Route::post('/{id}/delete', [AddonController::class, 'deleteAddon']);
    });

    //Unavailable Dates
    Route::prefix('/unavailable-dates')->group(function () {
        Route::get('', [DateController::class,'getUnavailableDate']);
        Route::post('/mark', [DateController::class,'markUnavailableDate']);
        Route::post('/{id}/unmark', [DateController::class,'unmarkUnavailableDate']);
    });

    //Bookings
    Route::prefix('/bookings')->group(function () {
        Route::get('/', [BookingController::class,'getBookings']);
        Route::post('/add', [BookingController::class,'addBooking']);
        Route::get('/packages', [BookingController::class,'getAvailablePackages']);
        Route::get('/{id}/addons', [BookingController::class,'getAvailableAddons']);
        Route::post('/{id}/update', [BookingController::class,'updateBooking']);
        Route::post('/{id}/delete', [BookingController::class,'deleteBooking']);
        Route::post('/{id}/reschedule', [BookingController::class,'rescheduleBooking']);
    });

    //Billings
    Route::prefix('/billings')->group(function () {
        Route::get('/', [BillingController::class,'getBillings']);
        Route::get('/{id}', [BillingController::class,'viewBilling'])->name('billing.view');
        Route::post('/{id}/add-payment', [BillingController::class,'addPayment']);
    });

    //Workload
    Route::prefix('/workload')->group(function () {
        Route::get('/', [WorkloadController::class,'getWorkloads']);
        Route::get('/{id}', [WorkloadController::class,'viewWorkload']);
        Route::get('/{id}/employees', [WorkloadController::class,'getAvailableEmployee']);
        Route::post('/{id}/assign', [WorkloadController::class,'assignWorkload']);
    });

    //Feedback
    Route::prefix('/feedbacks')->group(function () {
        Route::get('/', [FeedbackController::class,'getFeedbacks']);
        Route::get('/{id}', [FeedbackController::class,'viewFeedback'])->name('feedback.detail');
        Route::post('/{id}/mark-as-posted', [FeedbackController::class, 'markFeedbackAsPosted']);
        Route::post('/{id}/mark-as-unposted', [FeedbackController::class, 'markFeedBackAsUnposted']);
    });
});
