<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|in:movie,tvshow',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Map favoritable_type to model class
        $typeMap = [
            'movie' => \App\Models\Movie::class,
            'tvshow' => \App\Models\TvShow::class,
        ];
        $favoritable_type = $typeMap[strtolower($request->favoritable_type)];

        // Validate favoritable_id exists in the correct table
        $modelClass = $favoritable_type;
        if (!$modelClass::find($request->favoritable_id)) {
            return response()->json(['errors' => ['favoritable_id' => 'The selected favoritable id is invalid.']], 422);
        }

        // Check if favorite already exists
        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('favoritable_id', $request->favoritable_id)
            ->where('favoritable_type', $favoritable_type)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(null, 204); // Unfavorited
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'favoritable_id' => $request->favoritable_id,
            'favoritable_type' => $favoritable_type,
        ]);

        return response()->json($favorite, 201);
    }
}