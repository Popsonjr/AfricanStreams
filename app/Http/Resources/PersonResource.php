<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
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
            'also_known_as' => $this->also_known_as,
            'biography' => $this->biography,
            'birthday' => $this->birthday,
            'deathday' => $this->deathday,
            'gender' => $this->gender,
            'homepage' => $this->homepage,
            'imdb_id' => $this->imdb_id,
            'known_for_department' => $this->known_for_department,
            'place_of_birth' => $this->place_of_birth,
            'popularity' => $this->popularity,
            'profile_path' => $this->profile_path,
            'adult' => $this->adult,
        ];
    }
}