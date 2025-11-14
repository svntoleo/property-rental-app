<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Use the Stay::active() scope for DRY principle
        $activeStay = null;
        $activeStayEndDate = null;
        if ($this->relationLoaded('stays')) {
            // Filter loaded stays using the active scope logic
            // If stays were pre-filtered with ->active(), this will be efficient
            $activeStay = $this->stays->first(function ($stay) {
                // Re-use the active scope logic (DRY)
                return $stay->start_date <= now() && $stay->end_date >= now();
            });
            
            if ($activeStay) {
                $activeStayEndDate = $activeStay->end_date;
            }
        }

        $activeTenantsCount = 0;
        if ($activeStay) {
            // Prefer withCount result if present, fallback to loaded relationship collection
            $activeTenantsCount = $activeStay->tenants_count ?? ($activeStay->relationLoaded('tenants') ? $activeStay->tenants->count() : 0);
        }

        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'label' => $this->label,
            'stays' => StayResource::collection($this->whenLoaded('stays')),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            // Occupancy helpers
            'is_occupied' => (bool) $activeStay,
            'active_stay_tenants' => (int) $activeTenantsCount,
            'active_stay_end_date' => $activeStayEndDate?->toDateString(),
        ];
    }
}
