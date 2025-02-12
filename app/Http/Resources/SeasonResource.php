<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
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
            '_id' => $this->_id,
            'season_number' => $this->season_number,
            'name' => $this->name,
            'overview' => $this->overview,
            'poster_path' => $this->poster_path,
            'air_date' => $this->air_date,
            'episode_count' => $this->episode_count,
            'vote_average' => $this->vote_average,
            'episodes' => $this->whenLoaded('episodes', fn() => $this->episodes->map(fn($episode) => [
                'id' => $episode->id,
                'episode_number' => $episode->episode_number,
                'season_number' => $episode->season_number,
                'name' => $episode->name,
                'overview' => $episode->overview,
                'still_path' => $episode->still_path,
                'air_date' => $episode->air_date,
                'runtime' => $episode->runtime,
                'vote_average' => $episode->vote_average,
                'vote_count' => $episode->vote_count,
                'production_code' => $episode->production_code,
                'crew' => $episode->crew,
                'guest_stars' => $episode->guest_stars,
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
