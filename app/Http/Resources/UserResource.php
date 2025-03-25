<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'status' => $this->status,
            'subscribed' => $this->subscribed,
            'last_activity' => $this->lastActivityLog
                ? ($this->lastActivityLog->activity_date . ' ' . $this->lastActivityLog->activity_time)
                : null,
        ];
    }
}