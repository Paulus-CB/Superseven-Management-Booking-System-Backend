<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\User\Customer\CustomerAccountController;
use App\Http\Controllers\User\Employee\EmployeeAccountController;
use App\Http\Controllers\User\UserController;
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

    //Bookings
    Route::prefix('/bookings')->group(function () {
        Route::post('/add', [BookingController::class,'addBooking']);
    });
});
