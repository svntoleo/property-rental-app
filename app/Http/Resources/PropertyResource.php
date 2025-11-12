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
            'accommodations' => AccommodationResource::collection($this->whenLoaded('accommodations')),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
