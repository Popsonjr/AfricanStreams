<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'credit_id' => $this->credit_id,
            'person' => new PersonResource($this->person),
            'department' => $this->department,
            'job' => $this->job,
            'character' => $this->character,
            'order' => $this->order,
            'known_for_department' => $this->known_for_department,
        ];
    }
}