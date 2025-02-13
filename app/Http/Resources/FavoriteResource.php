<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->favoritable instanceof \App\Models\Movie
        ? new MovieResource($this->favoritable)
        : new TvShowResource($this->favoritable);

        return array_merge($resource->toArray($request), [
            'media_type' => $this->favoritable instanceof \App\Models\Movie ? 'movie' : 'tv',
        ]);
    }
}