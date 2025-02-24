<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function details(Request $request, $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        return new ReviewResource($review);
    }
}