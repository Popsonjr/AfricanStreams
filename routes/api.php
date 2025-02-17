<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('/get-user', [AuthController::class, 'getUser']);
    Route::post('/password/reset-request', [AuthController::class, 'sendResetLink']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    Route::post('/password/change', [AuthController::class, 'changePassword'])->middleware('auth:api');



    
    
    // Route::get('facebook/redirect', AuthController::class, 'redirectToFacebook');
    // Route::get('facebook/callback', AuthController::class, 'handleFacebookCallback');
});



Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'register']);
});