<?php

namespace App\Http\Controllers;

use App\Http\Resources\CreditResource;
use App\Http\Resources\EpisodeAccountStateResource;
use App\Http\Resources\SeasonResource;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function details(Request $request, $seriesId, $seasonNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->with(['episodes', 'credits.person'])
            ->firstOrFail();

        return new SeasonResource($season);
    }

    public function accountStates(Request $request, $seriesId, $seasonNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();

        $episodes = $season->episodes()->get();

        return response()->json([
            'id' => $season->id,
            'results' => EpisodeAccountStateResource::collection($episodes),
        ]);
    }

    public function credits(Request $request, $seriesId, $seasonNumber)
    {
        $tvShow = TvShow::findOrFail($seriesId);
        $season = Season::where('tv_show_id', $tvShow->id)
            ->where('season_number', $seasonNumber)
            ->firstOrFail();

        $credits = $season->credits()->with('person')->get();

        return response()->json([
            'id' => $season->id,
            'cast' => CreditResource::collection($credits->where('character', '!=', null)),
            'crew' => CreditResource::collection($credits->where('job', '!=', null)),
        ]);
    }
}