<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserAccountController;
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
        Route::post('/update', [UserAccountController::class, 'updateCurrent']);
        Route::post('/update-password', [UserAccountController::class, 'updatePassword']);

        //Accounts
        Route::get('/', [UserAccountController::class, 'viewAccounts']);
    });


});
