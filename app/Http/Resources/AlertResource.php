<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'medication_id' => $this->medication_id,
            'lot_number' => $this->lot_number,
            'channel' => $this->channel,
            'status' => $this->status,
            'sent_at' => optional($this->sent_at)->toDateTimeString(),
            'error_message' => $this->error_message,
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
