<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stay_id' => $this->stay_id,
            'stay' => new StayResource($this->whenLoaded('stay')),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_formatted' => $this->phone_formatted,
            'cpf' => $this->cpf,
                // Use the accessor-defined attribute name (formatted_cpf)
                // but expose it to the frontend as cpf_formatted for consistency
                'cpf_formatted' => $this->formatted_cpf,
            'is_active' => $this->is_active,
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
