<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\Customer\CustomerAccountController;
use App\Http\Controllers\User\Employee\EmployeeAccountController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    // User
    Route::prefix('/users')->group(function () {
        //Authenticated
        Route::get('/current', [AuthController::class, 'user']);
        Route::post('/update', [UserController::class, 'updateCurrent']);
        Route::post('/update-password', [UserController::class, 'updatePassword']);
    });

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
});
