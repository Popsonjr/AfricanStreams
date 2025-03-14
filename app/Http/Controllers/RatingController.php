<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rateable_id' => 'required|integer',
            'rateable_type' => 'required|in:movie,tvshow,episode',
            'value' => 'required|integer|between:1,10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Map rateable_type to model class
        $typeMap = [
            'movie' => \App\Models\Movie::class,
            'tvshow' => \App\Models\TvShow::class,
            'episode' => \App\Models\Episode::class,
        ];
        $rateable_type = $typeMap[strtolower($request->rateable_type)];

        // Validate rateable_id exists
        $modelClass = $rateable_type;
        if (!$modelClass::find($request->rateable_id)) {
            return response()->json(['errors' => ['rateable_id' => 'The selected rateable id is invalid.']], 422);
        }

        // Check if user already rated this item
        $existing = Rating::where('user_id', $request->user()->id)
            ->where('rateable_id', $request->rateable_id)
            ->where('rateable_type', $rateable_type)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(null, 204);
        }

        $rating = Rating::create([
            'user_id' => $request->user()->id,
            'rateable_id' => $request->rateable_id,
            'rateable_type' => $rateable_type,
            'value' => $request->value,
        ]);

        return response()->json($rating, 201);
    }
}