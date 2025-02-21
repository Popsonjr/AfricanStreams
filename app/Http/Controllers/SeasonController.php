<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index($seriesId) {
        $seasons = Season::where('series_id', $seriesId)->with('episodes')->get();
        return response()->json($seasons);
    }

    public function show($seriesId, $seasonNumber) {
        $season = Season::where('series_id', $seriesId)->where('season_number', $seasonNumber)->with('episodes')->firstOrFail();
        return response()->json($season);
    }

    public function store(Request $request, $seriesId) {
        $request->validate([
            'season_number' => 'required|integer'
        ]);

        $season = Season::create([
            'series_id' => $seriesId,
            'season_number' => $request->season_number
        ]);

        return response()->json($season, 201);
    }
}