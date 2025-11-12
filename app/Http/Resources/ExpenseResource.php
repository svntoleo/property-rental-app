<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'accommodation_id' => $this->accommodation_id,
            'accommodation' => new AccommodationResource($this->whenLoaded('accommodation')),
            'expense_category_id' => $this->expense_category_id,
            'category' => new ExpenseCategoryResource($this->whenLoaded('category')),
            'label' => $this->label,
            'price' => $this->price,
            'description' => $this->description,
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
