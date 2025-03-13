<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'watchable_id' => 'required|integer',
            'watchable_type' => 'required|in:movie,tvshow',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Map watchable_type to model class
        $typeMap = [
            'movie' => \App\Models\Movie::class,
            'tvshow' => \App\Models\TvShow::class,
        ];
        $watchable_type = $typeMap[strtolower($request->watchable_type)];

        // Validate watchable_id exists
        $modelClass = $watchable_type;
        if (!$modelClass::find($request->watchable_id)) {
            return response()->json(['errors' => ['watchable_id' => 'The selected watchable id is invalid.']], 422);
        }

        // Check if already in watchlist
        $existing = Watchlist::where('user_id', $request->user()->id)
            ->where('watchable_id', $request->watchable_id)
            ->where('watchable_type', $watchable_type)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(null, 204);
        }

        $watchlist = Watchlist::create([
            'user_id' => $request->user()->id,
            'watchable_id' => $request->watchable_id,
            'watchable_type' => $watchable_type,
        ]);

        return response()->json($watchlist, 201);
    }
}