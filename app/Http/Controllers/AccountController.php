<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\RatingResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WatchlistResource;
use App\Models\Favorite;
use App\Models\ListModel;
use App\Models\Movie;
use App\Models\Rating;
use App\Models\TvShow;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function details(Request $request) {
        return new UserResource(Auth::user());
    }

    public function lists(Request $request) {
        $lists = ListModel::where('user_id', Auth::id())
        ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $lists->currentPage(),
            'results' => ListResource::collection($lists),
            'total_pages' => $lists->lastPage(),
            'total_results' => $lists->total(),
        ]);
    }

    public function favoriteMovies(Request $request) {
        $favorites = Favorite::where('user_id', Auth::id())
        ->where('favoritable_type', Movie::class)
        ->with('favoritable')
        ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' =>$favorites->currentPage(),
            'results' => FavoriteResource::collection($favorites),
            'total_pages' => $favorites->lastPage(),
            'total_results' => $favorites->total(),
        ]);
    }

    public function favoriteTv(Request $request) {
        $favorites = Favorite::where('user_id', Auth::id())
        ->where('favoritable_type', TvShow::class)
        ->with('favoritable')
        ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $favorites->currentPage(),
            'results' => FavoriteResource::collection($favorites),
            'total_pages' => $favorites->lastPage(),
            'total_results' => $favorites->total(),
        ]);
    }

    public function markAsFavorite(Request $request) {
        $request->validate([
            'media_type' => 'required|in:movie,tv',
            'media_id' => 'required|integer',
            'favorite'=> 'required|boolean', 
        ]);

        $model = $request->media_type === 'movie' ? Movie::class : TvShow::class;
        $media = $model::findOrfail($request->media_id);

        if($request->favorite) {
            Favorite::updateOrCreate([
                'user_id' => Auth::id(),
                'favoritable_id' => $media->id,
                'favoritable_type' => $model,
            ]);
        } else {
            Favorite::where('user_id', Auth::id())
            ->where('favoritable_id', $media->id)
            ->where('favoritable_type', $model)
            ->delete();
        }

        return response()->json([
            'status_code' => 1,
            'status_message' => 'Success'
        ]);
    }

    public function ratedMovies(Request $request) {
        $ratings = Rating::where('user_id', Auth::id())
            ->where('rateable_type', Movie::class)
            ->with('rateable')
            ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $ratings->currentPage(),
            'results' => RatingResource::collection($ratings),
            'total_pages' => $ratings->lastPage(),
            'total_results' => $ratings->total(),
        ]);
    }

    public function ratedTv(Request $request)
    {
        $ratings = Rating::where('user_id', Auth::id())
            ->where('rateable_type', TvShow::class)
            ->with('rateable')
            ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $ratings->currentPage(),
            'results' => RatingResource::collection($ratings),
            'total_pages' => $ratings->lastPage(),
            'total_results' => $ratings->total(),
        ]);
    }

    public function ratedEpisodes(Request $request)
    {
        $ratings = Rating::where('user_id', Auth::id())
            ->where('rateable_type', \App\Models\Episode::class)
            ->with('rateable')
            ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $ratings->currentPage(),
            'results' => RatingResource::collection($ratings),
            'total_pages' => $ratings->lastPage(),
            'total_results' => $ratings->total(),
        ]);
    }

    public function watchlistMovies(Request $request){
        $watchlists = Watchlist::where('user_id', Auth::id())
        ->where('watchable_type', Movie::class)
        ->with('watchable')
        ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $watchlists->currentPage(),
            'results' => WatchlistResource::collection($watchlists),
            'total_pages' => $watchlists->lastPage(),
            'total_results' => $watchlists->total(),
        ]);
    } 

    public function watchlistTv(Request $request)
    {
        $watchlists = Watchlist::where('user_id', Auth::id())
            ->where('watchable_type', TvShow::class)
            ->with('watchable')
            ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $watchlists->currentPage(),
            'results' => WatchlistResource::collection($watchlists),
            'total_pages' => $watchlists->lastPage(),
            'total_results' => $watchlists->total(),
        ]);
    }

    public function addToWatchlist(Request $request)
    {
        $request->validate([
            'media_type' => 'required|in:movie,tv',
            'media_id' => 'required|integer',
            'watchlist' => 'required|boolean',
        ]);

        $model = $request->media_type === 'movie' ? Movie::class : TvShow::class;
        $media = $model::findOrFail($request->media_id);

        if ($request->watchlist) {
            Watchlist::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'watchable_id' => $media->id,
                    'watchable_type' => $model,
                ]
            );
        } else {
            Watchlist::where('user_id', Auth::id())
                ->where('watchable_id', $media->id)
                ->where('watchable_type', $model)
                ->delete();
        }

        return response()->json(['status_code' => 1, 'status_message' => 'Success']);
    }
}