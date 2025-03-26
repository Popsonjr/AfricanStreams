<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/admin/google/redirect', [AdminAuthController::class, 'redirectToGoogle']);
Route::get('/admin/google/callback', [AdminAuthController::class, 'handleGoogleCallback']);

Route::get('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback');