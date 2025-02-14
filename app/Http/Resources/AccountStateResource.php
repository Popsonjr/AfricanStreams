<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountStateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = JWTAuth::user();
        $favorite = $this->favorites()->where('user_id', $user->id)->exists();
        $watchlist = $this->watchlists()->where('user_id', $user->id)->exists();
        $rating = $this->ratings()->where('user_id', $user->id)->first();

        return [
            'id' => $this->id,
            'favorite' => $favorite,
            'watchlist' => $watchlist,
            'rated' => $rating ? ['value' => $rating->value] : false,
        ];
    }
}