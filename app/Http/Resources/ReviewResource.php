<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'author' => $this->author,
            'author_details' => $this->author_details,
            'content' => $this->content,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'iso_639_1' => $this->iso_639_1,
            'url' => $this->url,
            'media_type' => $this->reviewable instanceof \App\Models\Movie ? 'movie' : 'tv',
            'media_id' => $this->reviewable->id,
        ];
    }
}