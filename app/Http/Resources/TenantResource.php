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
            'cpf_formatted' => $this->cpf_formatted,
        ];
    }
}
