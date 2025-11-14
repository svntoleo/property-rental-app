<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Occupancy metadata now via accessors; still expose active stay if relation loaded.
        $activeStay = $this->relationLoaded('activeStay') ? $this->activeStay : null;

        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'label' => $this->label,
            'stays' => StayResource::collection($this->whenLoaded('stays')),
            'active_stay' => $activeStay ? new StayResource($activeStay) : null,
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            // Occupancy helpers
            'is_occupied' => $this->is_occupied,
            'active_stay_tenants' => $this->active_stay_tenants,
            'active_stay_category' => $this->active_stay_category,
            'active_stay_end_date' => $this->active_stay_end_date,
        ];
    }
}
