<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_date' => optional($this->purchase_date)->toDateTimeString(),
            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'email' => $this->customer?->email,
                'phone' => $this->customer?->phone,
            ],
            'medications' => $this->items->map(function ($item) {
                return [
                    'id' => $item->medication?->id,
                    'name' => $item->medication?->name,
                    'lot_number' => $item->medication?->lot_number,
                    'quantity' => $item->quantity,
                ];
            }),
        ];
    }
}
