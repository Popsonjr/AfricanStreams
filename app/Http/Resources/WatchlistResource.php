<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WatchlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->watchable instanceof \App\Models\Movie
            ? new MovieResource($this->watchable)
            : new TvShowResource($this->watchable);

        return array_merge($resource->toArray($request), [
            'media_type' => $this->watchable instanceof \App\Models\Movie ? 'movie' : 'tv',
        ]);
    }
}