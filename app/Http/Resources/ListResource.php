<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'public' => $this->public,
            'iso_639_1' => $this->iso_639_1,
            'item_count' => $this->item_count,
            'favorited' => $this->favorited,
            'created_by' => $this->user->username,
            'items' => $this->whenLoaded('items', fn() => $this->items->map(function ($item) {
                $resource = $item->itemable instanceof \App\Models\Movie
                    ? new MovieResource($item->itemable)
                    : new TvShowResource($item->itemable);
                return array_merge($resource->toArray(request()), [
                    'media_type' => $item->itemable instanceof \App\Models\Movie ? 'movie' : 'tv',
                ]);
            })),
        ];
    }
}