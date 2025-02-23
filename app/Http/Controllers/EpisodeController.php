<?php

namespace App\Http\Controllers;

use App\Http\Resources\CreditResource;
use App\Http\Resources\EpisodeAccountStateResource;
use App\Http\Resources\EpisodeResource;
use App\Models\Episode;
use App\Models\Rating;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EpisodeController extends Controller
{
    public function details(Request $request, $seriesId, $seasonNumber, $episodeNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();
        $episode = Episode::where('season_id', $season->id)
            ->where('episode_number', $episodeNumber)
            ->with(['credits.person'])
            ->firstOrFail();

        return new EpisodeResource($episode);
    }

    public function accountStates(Request $request, $seriesId, $seasonNumber, $episodeNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();
        $episode = Episode::where('season_id', $season->id)
            ->where('episode_number', $episodeNumber)
            ->firstOrFail();

        return new EpisodeAccountStateResource($episode);
    }

    public function credits(Request $request, $seriesId, $seasonNumber, $episodeNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();
        $episode = Episode::where('season_id', $season->id)
            ->where('episode_number', $episodeNumber)
            ->firstOrFail();

        $credits = $episode->credits()->with('person')->get();

        return response()->json([
            'id' => $episode->id,
            'cast' => CreditResource::collection($credits->where('character', '!=', null)),
            'crew' => CreditResource::collection($credits->where('job', '!=', null)),
        ]);
    }

    public function rate(Request $request, $seriesId, $seasonNumber, $episodeNumber)
    {
        $request->validate([
            'value' => 'required|numeric|min:0.5|max:10',
        ]);

        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();
        $episode = Episode::where('season_id', $season->id)
            ->where('episode_number', $episodeNumber)
            ->firstOrFail();

        Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'rateable_id' => $episode->id,
                'rateable_type' => Episode::class,
            ],
            ['value' => $request->value]
        );

        return response()->json(['status_code' => 1, 'status_message' => 'Success']);
    }

    public function deleteRating(Request $request, $seriesId, $seasonNumber, $episodeNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();
        $episode = Episode::where('season_id', $season->id)
            ->where('episode_number', $episodeNumber)
            ->firstOrFail();

        Rating::where('user_id', Auth::id())
            ->where('rateable_id', $episode->id)
            ->where('rateable_type', Episode::class)
            ->delete();

        return response()->json(['status_code' => 1, 'status_message' => 'Success']);
    }
}