<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'address' => $this->address,
            'description' => $this->description,
            // Always include arrays to avoid undefined on the frontend
            'accommodations' => AccommodationResource::collection($this->relationLoaded('accommodations') ? $this->accommodations : collect()),
            'expenses' => ExpenseResource::collection($this->relationLoaded('expenses') ? $this->expenses : collect()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
