<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = null;
        $media_type = null;

        if($this->rateable instanceof \App\Models\Movie) {
            $resource = new MovieResource($this->rateable);
            $media_type = 'movie';
        } elseif ($this->rateable instanceof \App\Models\TvShow) {
            $resource = new TvShowResource($this->rateable);
            $media_type = 'tv';
        } else {
            $resource = new EpisodeResource($this->rateable);
            $media_type = 'episode';
        }

        return array_merge($resource->toArray($request), [
            'media_type' => $media_type,
            'rated' => ['value' => $this->value]
        ]);
    }
}