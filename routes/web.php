<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PwaWebController;

Route::get('/', fn() => view('welcome'));

// ─── PWA Routes ───
Route::prefix('app')->name('pwa.')->group(function () {
    Route::get('/',            [PwaWebController::class, 'home'])->name('home');
    Route::get('/login',       [PwaWebController::class, 'login'])->name('login');
    Route::get('/tracking/{tripId}', [PwaWebController::class, 'tracking'])->name('tracking');
    Route::get('/subscriptions',     [PwaWebController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/long-distance',     [PwaWebController::class, 'longDistance'])->name('long-distance');
    Route::get('/account',           [PwaWebController::class, 'account'])->name('account');
    Route::get('/offline',           [PwaWebController::class, 'offline'])->name('offline');
});

// PWA Manifest & Service Worker
Route::get('/manifest.json',   [PwaWebController::class, 'manifest']);
Route::get('/sw.js',           [PwaWebController::class, 'serviceWorker']);
