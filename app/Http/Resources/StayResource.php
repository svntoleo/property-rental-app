<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accommodation_id' => $this->accommodation_id,
            'accommodation' => new AccommodationResource($this->whenLoaded('accommodation')),
            'stay_category_id' => $this->stay_category_id,
            'category' => new StayCategoryResource($this->whenLoaded('category')),
            'price' => $this->price,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'active' => $this->active,
            'tenants' => TenantResource::collection($this->whenLoaded('tenants')),
        ];
    }
}
