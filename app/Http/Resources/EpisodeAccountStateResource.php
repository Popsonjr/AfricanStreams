<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class EpisodeAccountStateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = JWTAuth::user();
        $rating = $this->ratings()->where('user_id', $user->id)->first();

        return [
            'id' => $this->id,
            'rated' => $rating ? ['value' => $rating->value] : false,
        ];
    }
}