<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorRatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'is_visible' => (bool) $this->is_visible,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'image' => $this->user?->image,
            ],
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}

