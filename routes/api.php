<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ProfileController;

use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SubscriptionController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/customer', [RegisterController::class, 'registerCustomer']);
Route::post('/register/driver', [RegisterController::class, 'registerDriver']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/location', [ProfileController::class, 'updateLocation']);
    Route::post('/profile/document', [ProfileController::class, 'uploadDocument']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    // Trips
    Route::post('/trips', [TripController::class, 'createTrip']);
    Route::get('/trips/my-trips', [TripController::class, 'myTrips']);
    Route::post('/trips/{id}/accept', [TripController::class, 'acceptTrip']);
    Route::post('/trips/{id}/start', [TripController::class, 'startTrip']);
    Route::post('/trips/{id}/complete', [TripController::class, 'completeTrip']);

    // Driver Location
    Route::post('/driver/location', [\App\Http\Controllers\Api\DriverLocationController::class, 'update']);

    // Ratings
    Route::post('/trips/{id}/rate', [RatingController::class, 'rateTrip']);

    // Coupons
    Route::post('/coupons/apply', [CouponController::class, 'applyCoupon']);

    // Subscriptions
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions/plans/{id}/subscribe', [SubscriptionController::class, 'subscribe']);

    // Wallet
    Route::get('/wallet/balance', [\App\Http\Controllers\Api\WalletController::class, 'balance']);
    Route::post('/wallet/deposit', [\App\Http\Controllers\Api\WalletController::class, 'deposit']);
    Route::get('/wallet/transactions', [\App\Http\Controllers\Api\WalletController::class, 'transactions']);

    // Driver GPS Location (كل 10 ثواني من تطبيق السائق)
    Route::post('/driver/location', [\App\Http\Controllers\Api\DriverLocationController::class, 'update']);

    // PWA: رحلات المستخدم
    Route::get('/pwa/my-trips', [\App\Http\Controllers\Api\PwaController::class, 'myTrips']);

    // تتبع الرحلة الحية
    Route::get('/trips/{tripId}/driver-location', [\App\Http\Controllers\Api\DriverLocationController::class, 'getForTrip']);
});

// ─── PWA Public Routes (بدون مصادقة) ───
Route::prefix('pwa')->group(function () {
    Route::get('/banners',              [\App\Http\Controllers\Api\PwaController::class, 'banners']);
    Route::get('/popup-ad',             [\App\Http\Controllers\Api\PwaController::class, 'popupAd']);
    Route::get('/pricing',              [\App\Http\Controllers\Api\PwaController::class, 'pricing']);
    Route::get('/long-distance-trips',  [\App\Http\Controllers\Api\PwaController::class, 'longDistanceTrips']);
});
