<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;

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

//Route For Series
Route::prefix('series')->group(function() {
    Route::get('/', [SeriesController::class, 'index']);
    Route::get('/{series}', [SeriesController::class, 'show']);
    Route::middleware('auth:admin')->group(function () {
        Route::post('/', [SeriesController::class, 'store']);
        Route::put('/{series}', [SeriesController::class, 'update']);
        Route::delete('/{series}', [SeriesController::class, 'destroy']);
    });
});

//Route For Seasons within a Series
Route::prefix('series/{series}')->group(function() {
    Route::get('/seasons', [SeasonController::class, 'index']);
    Route::get('/seasons/{season}', [SeasonController::class, 'show']);
    Route::middleware('auth:admin')->group(function () {
        Route::post('/seasons', [SeasonController::class, 'store']);
        Route::put('/seasons/{season}', [SeasonController::class, 'update']);
        Route::delete('/seasons/{season}', [SeasonController::class, 'destroy']);
    });
});

//Route For Episodes within a Season
Route::prefix('seasons/{season}')->group(function() {
    Route::get('/episodes', [EpisodeController::class, 'index']);
    Route::get('/episodes/{episode}', [EpisodeController::class, 'show']);
    Route::middleware('auth:admin')->group(function () {
        Route::post('/episodes', [EpisodeController::class, 'store']);
        Route::put('/episodes/{episode}', [EpisodeController::class, 'update']);
        Route::delete('/episodes/{episode}', [EpisodeController::class, 'destroy']);
    });
});



// Route::options('{any}', function (Request $request) {
//     return response()->noContent(204)
//         ->withHeaders([
//             'Access-Control-Allow-Origin' => $request->header('Origin') ?? '*',
//             'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
//             'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers') ?? 'Origin, Content-Type, Accept, Authorization',
//         ]);
// })->where('any', '.*');