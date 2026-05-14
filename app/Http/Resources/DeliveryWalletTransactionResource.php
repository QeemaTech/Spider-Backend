<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryWalletTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amount = (float) $this->amount;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type === 'delivery_completed' ? __('Delivery completed') : __('Admin adjustment'),
            'amount' => $amount,
            'balance_after' => (float) $this->balance_after,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_formatted' => $this->created_at->format('M d, Y H:i'),
        ];
    }
}
