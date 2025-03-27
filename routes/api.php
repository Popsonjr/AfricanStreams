<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\GenreController;
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
    Route::post('/get-user', [AuthController::class, 'getUser']);
    Route::post('/password/reset-request', [AuthController::class, 'sendResetLink']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    

    Route::middleware('auth:api')->group(function () {
        Route::post('/me', [AuthController::class, 'me']);
        Route::post('/password/change', [AuthController::class, 'changePassword']);
    });
    
    // Route::get('facebook/redirect', AuthController::class, 'redirectToFacebook');
    // Route::get('facebook/callback', AuthController::class, 'handleFacebookCallback');
});

Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::post('/email/verify', [AdminAuthController::class, 'verifyEmail']);
    Route::post('/email/resend', [AdminAuthController::class, 'resendVerificationEmail']);
    Route::post('/refresh', [AdminAuthController::class, 'refresh']);
    Route::post('/password/reset-request', [AdminAuthController::class, 'sendResetLink']);
    Route::post('/password/reset', [AdminAuthController::class, 'resetPassword']);
    
    Route::apiResource('movies', MovieController::class);
    Route::get('movies/{id}/related', [MovieController::class, 'related']);
    
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('genres', GenreController::class);

    Route::middleware('auth:admin')->group(function () {
        Route::post('/me', [AdminAuthController::class, 'me']);
        Route::post('/get-user', [AdminAuthController::class, 'getUser']);
        Route::post('/password/change', [AdminAuthController::class, 'changePassword']);
        
        Route::post('/genres', [GenreController::class, 'store']);
        Route::put('/genres', [GenreController::class, 'update']);
        Route::delete('/genre', [GenreController::class, 'delete']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories', [CategoryController::class, 'update']);
        Route::delete('/categories', [CategoryController::class, 'delete']);
        
        Route::post('/movies', [MovieController::class, 'store']);
        Route::put('/movies', [MovieController::class, 'update']);
        Route::delete('/movies', [MovieController::class, 'delete']);
    });
});



Route::middleware('auth:api')->group(function () {
    Route::get('/series/{id}/seasons', [SeasonController::class, 'index']);
    Route::get('/series/{id}/seasons/{seasonNumber}', [SeasonController::class, 'show']);
    Route::post('/series/{id}/seasons', [SeasonController::class, 'store']);
    
    Route::post('/series/{id}/seasons/{seasonNumber}/episodes', [EpisodeController::class, 'store']);

    Route::post('/uploads', [UploadController::class, 'upload']);
});