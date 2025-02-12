<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
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
            'episode_number' => $this->episode_number,
            'season_number' => $this->season_number,
            'name' => $this->name,
            'overview' => $this->overview,
            'still_path' => $this->still_path,
            'air_date' => $this->air_date,
            'runtime' => $this->runtime,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'production_code' => $this->production_code,
            'crew' => $this->crew,
            'guest_stars' => $this->guest_stars,
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