<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'capacity',
        'name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Deliveries assigned to this shift.
     */
    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(Delivery::class, 'delivery_shift')
            ->withTimestamps();
    }

    /**
     * Number of deliveries currently in this shift (uses deliveries_count when loaded).
     */
    public function deliveriesCount(): int
    {
        return (int) ($this->deliveries_count ?? $this->deliveries()->count());
    }

    /**
     * Number of delivery slots still available.
     */
    public function availableSlots(): int
    {
        return max(0, $this->capacity - $this->deliveriesCount());
    }

    /**
     * Whether this shift is full (no more deliveries can take it).
     */
    public function isFull(): bool
    {
        return $this->availableSlots() <= 0;
    }

    /**
     * Whether the shift date is today or in the future (still relevant for taking).
     */
    public function isUpcoming(): bool
    {
        return $this->date->startOfDay()->gte(now()->startOfDay());
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('date', '>=', now()->toDateString());
    }
}
