<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkTypeApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ReportApiController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);

Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('email/resend-verification', [AuthController::class, 'resend']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('work-types', WorkTypeApiController::class);
    Route::apiResource('events', EventApiController::class);
    Route::delete('events/{event}/destroy-series', [EventApiController::class, 'destroySeries']);
    Route::get('dashboard', [EventApiController::class, 'dashboard']);
    Route::patch('events/{event}/move-date', [EventApiController::class, 'moveDate']);
    Route::get('reports', [ReportApiController::class, 'index']);

});
