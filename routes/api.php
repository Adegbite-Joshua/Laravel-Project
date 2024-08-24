<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
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

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::get('/rooms', [RoomController::class,'index']);
Route::resource('/reviews', ReviewController::class);

Route::group(['middleware'=> ['auth:sanctum']], function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::resource('/rooms', RoomController::class);
});