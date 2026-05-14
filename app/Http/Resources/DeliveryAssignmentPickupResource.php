<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryAssignmentPickupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_order_id' => $this->vendor_order_id,
            'sequence' => $this->sequence,
            'picked_at' => $this->picked_at?->toIso8601String(),
            'vendor_order' => new VendorOrderResource($this->whenLoaded('vendorOrder')),
        ];
    }
}
