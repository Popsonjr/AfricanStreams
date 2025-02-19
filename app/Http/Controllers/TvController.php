<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountStateResource;
use App\Http\Resources\CreditResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\TvShowResource;
use App\Models\Rating;
use App\Models\TvShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvController extends Controller
{
    public function details(Request $request, $id)
    {
        $tvShow = TvShow::with(['genres', 'seasons', 'credits.person'])->findOrFail($id);
        return new TvShowResource($tvShow);
    }

    public function accountStates(Request $request, $id)
    {
        $tvShow = TvShow::findOrFail($id);
        return new AccountStateResource($tvShow);
    }

    public function credits(Request $request, $id)
    {
        $tvShow = TvShow::findOrFail($id);
        $credits = $tvShow->credits()->with('person')->get();

        return response()->json([
            'id' => $tvShow->id,
            'cast' => CreditResource::collection($credits->where('character', '!=', null)),
            'crew' => CreditResource::collection($credits->where('job', '!=', null)),
        ]);
    }

    public function reviews(Request $request, $id)
    {
        $tvShow = TvShow::findOrFail($id);
        $reviews = $tvShow->reviews()->paginate(20, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'id' => $tvShow->id,
            'page' => $reviews->currentPage(),
            'results' => ReviewResource::collection($reviews),
            'total_pages' => $reviews->lastPage(),
            'total_results' => $reviews->total(),
        ]);
    }

    public function rate(Request $request, $id)
    {
        $request->validate([
            'value' => 'required|numeric|min:0.5|max:10',
        ]);

        $tvShow = TvShow::findOrFail($id);

        Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'rateable_id' => $tvShow->id,
                'rateable_type' => TvShow::class,
            ],
            ['value' => $request->value]
        );

        return response()->json(['status_code' => 1, 'status_message' => 'Success']);
    }

    public function deleteRating(Request $request, $id)
    {
        $tvShow = TvShow::findOrFail($id);

        Rating::where('user_id', Auth::id())
            ->where('rateable_id', $tvShow->id)
            ->where('rateable_type', TvShow::class)
            ->delete();

        return response()->json(['status_code' => 1, 'status_message' => 'Success']);
    }

    public function popular(Request $request)
    {
        $tvShows = TvShow::orderBy('popularity', 'desc')
            ->with(['genres'])
            ->paginate(20, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $tvShows->currentPage(),
            'results' => TvShowResource::collection($tvShows),
            'total_pages' => $tvShows->lastPage(),
            'total_results' => $tvShows->total(),
        ]);
    }

}