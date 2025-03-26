<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'benefits' => $this->benefits,
            'amount' => $this->amount,
            'interval' => $this->interval,
            'active' => $this->active,
            'paystack_plan_code' => $this->paystack_plan_code,
        ];
    }
}