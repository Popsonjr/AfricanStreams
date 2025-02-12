<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'title' => $this->title,
            'overview' => $this->overview,
            'poster_path' => $this->poster_path,
            'backdrop_path' => $this->backdrop_path,
            'release_date' => $this->release_date,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'adult' => $this->adult,
            'original_language' => $this->original_language,
            'original_title' => $this->original_title,
            'runtime' => $this->runtime,
            'status' => $this->status,
            'production_companies' => $this->production_companies,
            'production_countries' => $this->production_countries,
            'tagline' => $this->tagline,
            'budget' => $this->budget,
            'revenue' => $this->revenue,
            'homepage' => $this->homepage,
            'belongs_to_collection' => $this->belongs_to_collection,
            'spoken_languages' => $this->spoken_languages,
            'imdb_id' => $this->imdb_id,
            'popularity' => $this->popularity,
            'video' => $this->video,
            'genres' => $this->whenLoaded('genres', fn() => $this->genres->map(fn($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
            ])),
            'credits' => $this->whenLoaded('credits', fn() => [
                'cast' => $this->credits->where('character', '!=', null)->map(fn($credit) => [
                    'id' => $credit->person->id,
                    'name' => $credit->person->name,
                    'character' => $credit->character,
                    'order' => $credit->order,
                    'credit_id' => $credit->credit_id,
                    'profile_path' => $credit->person->profile_path,
                ]),
                'crew' => $this->credits->where('job', '!=', null)->map(fn($credit) => [
                    'id' => $credit->person->id,
                    'name' => $credit->person->name,
                    'job' => $credit->job,
                    'department' => $credit->department,
                    'credit_id' => $credit->credit_id,
                    'profile_path' => $credit->person->profile_path,
                ]),
            ]),
        ];
    }
}