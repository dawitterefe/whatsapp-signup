<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

// Define the API rate limiter
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

Route::get('/ping', function () {
    return response()->json(['message' => 'API route is working Baby!']);
});

Route::post('/whatsapp-signup', [AuthController::class, 'sendWhatsAppOTP']);
Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
