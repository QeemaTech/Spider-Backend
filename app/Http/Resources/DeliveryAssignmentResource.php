<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryAssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'delivery_id' => $this->delivery_id,
            'status' => $this->status,
            'total_km' => (float) $this->total_km,
            'shipping_cost' => (float) $this->shipping_cost,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'order' => new OrderResource($this->whenLoaded('order')),
            'pickups' => DeliveryAssignmentPickupResource::collection($this->whenLoaded('pickups')),
        ];
    }
}
