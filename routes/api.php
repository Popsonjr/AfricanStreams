<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\UploadController;
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
    Route::apiResource('movies', MovieController::class);
    Route::get('movies/{id}/related', [MovieController::class, 'related']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/series/{id}/seasons', [SeasonController::class, 'index']);
    Route::get('/series/{id}/seasons/{seasonNumber}', [SeasonController::class, 'show']);
    Route::post('/series/{id}/seasons', [SeasonController::class, 'store']);
    
    Route::post('/series/{id}/seasons/{seasonNumber}/episodes', [EpisodeController::class, 'store']);

    Route::post('/uploads', [UploadController::class, 'upload']);
});