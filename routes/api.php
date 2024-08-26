<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/rooms', [RoomController::class, 'index']);
Route::resource('/reviews', ReviewController::class);
Route::get('/paystack/verify', [TransactionController::class, 'verifyPayment'])->name('payment.verify');

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:user')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::resource('/rooms', RoomController::class);
        Route::resource('/bookings', BookingController::class);
        Route::post('/paystack/initialize', [TransactionController::class, 'initializePayment']);

    });

    // Route::middleware('role:admin')->group(function () {
    //     Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    //     // Other admin routes
    // });
});

