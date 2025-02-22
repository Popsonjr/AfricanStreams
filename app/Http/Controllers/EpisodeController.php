<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;

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
}