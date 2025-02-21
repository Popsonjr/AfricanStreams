<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    public function store(Request $request, $seriesId, $seasonNUmber) {
        $season = Season::where('series_id', $seriesId)->where('season_number', $seasonNUmber)->findOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'release_date' => 'nullable|date',
            'episode_number' => 'required|integer',
            'cover_image' => 'nullable|string',
            'standard_image' => 'nullable|string',
            'thumbnail_image' => 'nullable|string',
        ]);

        $episode = $season->episodes()->create($request->all());
        return response()->json($episode, 201);
    }
}