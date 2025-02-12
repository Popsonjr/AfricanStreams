<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TvShowResource extends JsonResource
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
            'overview' => $this->overview,
            'poster_path' => $this->poster_path,
            'backdrop_path' => $this->backdrop_path,
            'first_air_date' => $this->first_air_date,
            'last_air_date' => $this->last_air_date,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'adult' => $this->adult,
            'original_language' => $this->original_language,
            'original_name' => $this->original_name,
            'number_of_seasons' => $this->number_of_seasons,
            'number_of_episodes' => $this->number_of_episodes,
            'status' => $this->status,
            'type' => $this->type,
            'tagline' => $this->tagline,
            'homepage' => $this->homepage,
            'in_production' => $this->in_production,
            'created_by' => $this->created_by,
            'episode_run_time' => $this->episode_run_time,
            'languages' => $this->languages,
            'networks' => $this->networks,
            'origin_country' => $this->origin_country,
            'production_companies' => $this->production_companies,
            'production_countries' => $this->production_countries,
            'spoken_languages' => $this->spoken_languages,
            'last_episode_to_air' => $this->last_episode_to_air,
            'next_episode_to_air' => $this->next_episode_to_air,
            'popularity' => $this->popularity,
            'genres' => $this->whenLoaded('genres', fn() => $this->genres->map(fn($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
            ])),
            'seasons' => $this->whenLoaded('seasons', fn() => $this->seasons->map(fn($season) => [
                'id' => $season->id,
                '_id' => $season->_id,
                'season_number' => $season->season_number,
                'name' => $season->name,
                'overview' => $season->overview,
                'poster_path' => $season->poster_path,
                'air_date' => $season->air_date,
                'episode_count' => $season->episode_count,
                'vote_average' => $season->vote_average,
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