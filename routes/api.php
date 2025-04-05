<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\ListItemController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TvController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WatchHistoryController;
use App\Http\Controllers\WatchlistController;
use App\Models\Rating;
use App\Models\Watchlist;
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
Route::middleware('auth:api')->group(function () {
    Route::get('/account', [AccountController::class, 'details']);
    Route::get('/account/{account_id}/lists', [AccountController::class, 'lists']);
    Route::get('/account/{account_id}/favourite/movies', [AccountController::class, 'favoriteMovies']);
    Route::get('/account/{account_id}/favourite/tv', [AccountController::class, 'favoriteTv']);
    Route::post('/account/{account_id}/favourite', [AccountController::class, 'markAsFavorite']);
    Route::get('/account/{account_id}/rated/movies', [AccountController::class, 'ratedMovies']);
    Route::get('/account/{account_id}/rated/tv', [AccountController::class, 'ratedTv']);
    Route::get('/account/{account_id}/rated/tv/episodes', [AccountController::class, 'ratedEpisodes']);
    Route::get('/account/{account_id}/watchlist/movies', [AccountController::class, 'watchlistMovies']);
    Route::get('/account/{account_id}/watchlist/tv', [AccountController::class, 'watchlistTv']);
    Route::post('/account/{account_id}/watchlist', [AccountController::class, 'addToWatchlist']);

    // Route::post('/account/{account_id}/favourite', [FavoriteController::class, 'store']);
    // Route::post('/account/{account_id}/watchlist', [WatchlistController::class, 'store']);
    Route::post('/account/{account_id}/rating', [RatingController::class, 'store']);
    Route::post('/account/{account_id}/list', [ListController::class, 'store']);
    Route::post('/account/{account_id}/list/{list}/item', [ListItemController::class, 'store']);

    Route::post('/watch-history', [WatchHistoryController::class, 'storeOrUpdate'])->name('watch-history.storeOrUpdate');
    Route::get('/watch-history', [WatchHistoryController::class, 'index'])->name('watch-history.index');
    
    Route::get('/watch-history/{watchHistory}', [WatchHistoryController::class, 'show'])->name('watch-history.show');
    Route::post('/watch-history/{watchHistory}/progress', [WatchHistoryController::class, 'updateProgress'])->name('watch-history.updateProgress');
    Route::delete('/watch-history/{watchHistory}', [WatchHistoryController::class, 'destroy'])->name('watch-history.destroy');
});

Route::prefix('')->group(function () {
    // Movie Endpoints
    Route::get('/movie/all', [MovieController::class, 'index']);
    Route::post('/movie', [MovieController::class, 'store'])->middleware('auth:admin');
    Route::put('/movie/{movie}', [MovieController::class, 'update'])->middleware('auth:admin');

    Route::get('/movie/popular', [MovieController::class, 'popular']);
    Route::get('/movie/now_playing', [MovieController::class, 'nowPlaying']);
    Route::get('/movie/upcoming', [MovieController::class, 'upcoming']);
    Route::get('/movie/top_rated', [MovieController::class, 'topRated']);
    Route::get('/movie/{id}', [MovieController::class, 'details']);
    Route::get('/movie/{id}/account_states', [MovieController::class, 'accountStates'])->middleware('auth:api');
    Route::get('/movie/{id}/credits', [MovieController::class, 'credits']);
    Route::get('/movie/{id}/reviews', [MovieController::class, 'reviews']);
    Route::post('/movie/{id}/rating', [MovieController::class, 'rate'])->middleware('auth:api');
    Route::delete('/movie/{id}/rating', [MovieController::class, 'deleteRating'])->middleware('auth:api');
    

    // TV Show Endpoints
    Route::get('/tv/popular', [TvController::class, 'popular']);
    // Route::get('/tv/airing_today', [TvController::class, 'airingToday']);
    Route::get('/tv/on_the_air', [TvController::class, 'onTheAir']);
    Route::get('/tv/top_rated', [TvController::class, 'topRated']);
    Route::get('/tv/{id}', [TvController::class, 'details']);
    Route::get('/tv/{id}/account_states', [TvController::class, 'accountStates'])->middleware('auth:api');
    Route::get('/tv/{id}/credits', [TvController::class, 'credits']);
    Route::get('/tv/{id}/reviews', [TvController::class, 'reviews']);
    Route::post('/tv/{id}/rating', [TvController::class, 'rate'])->middleware('auth:api');
    Route::delete('/tv/{id}/rating', [TvController::class, 'deleteRating'])->middleware('auth:api');
    

    // Season Endpoints
    Route::get('/tv/{series_id}/season/{season_number}', [SeasonController::class, 'details']);
    Route::get('/tv/{series_id}/season/{season_number}/account_states', [SeasonController::class, 'accountStates'])->middleware('auth:api');
    Route::get('/tv/{series_id}/season/{season_number}/credits', [SeasonController::class, 'credits']);

    // Episode Endpoints
    Route::get('/tv/{series_id}/season/{season_number}/episode/{episode_number}', [EpisodeController::class, 'details']);
    Route::get('/tv/{series_id}/season/{season_number}/episode/{episode_number}/account_states', [EpisodeController::class, 'accountStates'])->middleware('auth:api');
    Route::get('/tv/{series_id}/season/{season_number}/episode/{episode_number}/credits', [EpisodeController::class, 'credits']);
    Route::post('/tv/{series_id}/season/{season_number}/episode/{episode_number}/rating', [EpisodeController::class, 'rate'])->middleware('auth:api');
    Route::delete('/tv/{series_id}/season/{season_number}/episode/{episode_number}/rating', [EpisodeController::class, 'deleteRating'])->middleware('auth:api');

    // Review Endpoints
    Route::get('/review/{review_id}', [ReviewController::class, 'details']);
});

// Genre Endpoints (Public)
Route::get('/genre/movie/list', [GenreController::class, 'movieGenres']);
Route::get('/genre/tv/list', [GenreController::class, 'tvGenres']);


// Genre Endpoints (Authenticated, Admin-Only)
Route::middleware('auth:admin')->group(function () {
    Route::post('/genre', [GenreController::class, 'store']);
    Route::put('/genre/{id}', [GenreController::class, 'update']);
    Route::delete('/genre/{id}', [GenreController::class, 'destroy']);

    Route::get('/watch-history/all', [WatchHistoryController::class, 'indexAll']);
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
    Route::get('/get/all', [AdminAuthController::class, 'getAllAdmins']);
    

    
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('genres', GenreController::class);

    Route::middleware('auth:admin')->group(function () {
        Route::post('/me', [AdminAuthController::class, 'me']);
        Route::post('/get-user', [AdminAuthController::class, 'getUser']);
        Route::post('/password/change', [AdminAuthController::class, 'changePassword']);
        Route::put('/update', [AdminAuthController::class, 'update']);
        Route::delete('/profile', [AdminAuthController::class, 'delete']);
        Route::delete('/batch', [AdminAuthController::class, 'batchDestroy']);
        
        Route::post('/genres', [GenreController::class, 'store']);
        Route::put('/genres', [GenreController::class, 'update']);
        Route::delete('/genre', [GenreController::class, 'delete']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories', [CategoryController::class, 'update']);
        Route::delete('/categories', [CategoryController::class, 'delete']);
        
        Route::post('/movies', [MovieController::class, 'store']);
        Route::put('/movies', [MovieController::class, 'update']);
        Route::delete('/movies/batch', [MovieController::class, 'batchDestroy']);
        Route::delete('/movies', [MovieController::class, 'delete']);

    });

    Route::apiResource('movies', MovieController::class);
    Route::get('movies/{id}/related', [MovieController::class, 'related']);
});

Route::post('/pay', [PaymentController::class, 'redirectToGateway'])->name('pay')->middleware('auth:api');
// Route::get('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback');

// Plan routes
Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');

Route::middleware('auth:admin')->group(function () {
    Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
});

// Subscription routes
Route::middleware('auth:admin')->group(function () {
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::get('subscriptions/verify', [SubscriptionController::class, 'verify'])->name('subscriptions.verify');
});

// Webhook route (no auth)
Route::post('subscriptions/webhook', [SubscriptionController::class, 'handleWebhook'])->name('subscriptions.webhook');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::get('/newsletter/subscribers', [NewsletterController::class, 'index']);

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/contact/messages', [ContactController::class, 'index'])->name('contact.index');

//Route For Series
// Route::prefix('series')->group(function() {
//     Route::get('/', [SeriesController::class, 'index']);
//     Route::get('/{series}', [SeriesController::class, 'show']);
//     Route::middleware('auth:admin')->group(function () {
//         Route::post('/', [SeriesController::class, 'store']);
//         Route::put('/{series}', [SeriesController::class, 'update']);
//         Route::delete('/{series}', [SeriesController::class, 'destroy']);
//     });
// });

// //Route For Seasons within a Series
// Route::prefix('series/{series}')->group(function() {
//     Route::get('/seasons', [SeasonController::class, 'index']);
//     Route::get('/seasons/{season}', [SeasonController::class, 'show']);
//     Route::middleware('auth:admin')->group(function () {
//         Route::post('/seasons', [SeasonController::class, 'store']);
//         Route::put('/seasons/{season}', [SeasonController::class, 'update']);
//         Route::delete('/seasons/{season}', [SeasonController::class, 'destroy']);
//     });
// });

// //Route For Episodes within a Season
// Route::prefix('seasons/{season}')->group(function() {
//     Route::get('/episodes', [EpisodeController::class, 'index']);
//     Route::get('/episodes/{episode}', [EpisodeController::class, 'show']);
//     Route::middleware('auth:admin')->group(function () {
//         Route::post('/episodes', [EpisodeController::class, 'store']);
//         Route::put('/episodes/{episode}', [EpisodeController::class, 'update']);
//         Route::delete('/episodes/{episode}', [EpisodeController::class, 'destroy']);
//     });
// });



Route::options('{any}', function (Request $request) {
    return response()->noContent(204)
        ->withHeaders([
            'Access-Control-Allow-Origin' => $request->header('Origin') ?? '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers') ?? 'Origin, Content-Type, Accept, Authorization',
        ]);
})->where('any', '.*');