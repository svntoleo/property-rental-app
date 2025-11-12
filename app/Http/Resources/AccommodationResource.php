<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'label' => $this->label,
            'stays' => StayResource::collection($this->whenLoaded('stays')),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
        ];
    }
}
