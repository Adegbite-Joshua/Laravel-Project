<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OthersController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully']);
})->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification email sent successfully']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/rooms', [RoomController::class, 'index']);
Route::resource('/reviews', ReviewController::class);
Route::get('/paystack/verify', [TransactionController::class, 'verifyPayment'])->name('payment.verify');

Route::post('/admin/register', [AdminController::class, 'register']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/verify-otp', [AdminController::class, 'verifyOtp']);

Route::post('/contact_us', [OthersController::class, 'contact_us']);

Route::resource('/rooms', RoomController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware(['role:user'])->group(function () {
        Route::get('/user/details', [AuthController::class, 'user']);
        Route::resource('/bookings', BookingController::class)->only(['store', 'show']);
        Route::post('/payment/initialize', [BookingController::class, 'initializePayment']);
    });
});

// Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/details', [AdminController::class, 'user']);
        Route::resource('/rooms', RoomController::class);
        Route::resource('/bookings', BookingController::class);
        Route::post('/create-booking', [BookingController::class, 'adminCreateBooking']);
        Route::post('/guests', [BookingController::class, 'getGuestsBooking']);
        Route::get('/metrics', [AdminController::class, 'getMetrics']);
    });
// });
// Route::resource('/admin/bookings', BookingController::class);

