<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Http\Resources\ListResource;
use App\Http\Resources\UserResource;
use App\Models\Favorite;
use App\Models\ListModel;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function details(Request $request) {
        return new UserResource(Auth::user());
    }

    public function lists(Request $request) {
        $lists = ListModel::where('user_id', Auth::id())
        ->paginate(20, ['*'], 'page', $request->query('page', 1));

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
        ->paginate(20, ['*'], 'page', $request->query('page', 1));

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
        ->paginate(20, ['*'], 'page', $request->query('page', 1));

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
}