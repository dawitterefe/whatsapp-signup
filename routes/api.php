<?php

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
