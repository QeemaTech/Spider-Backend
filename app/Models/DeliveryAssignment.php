<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAssignment extends Model
{
    protected $fillable = [
        'order_id',
        'delivery_id',
        'status',
        'total_km',
        'shipping_cost',
        'assigned_at',
        'delivered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_km' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'assigned_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function pickups(): HasMany
    {
        return $this->hasMany(DeliveryAssignmentPickup::class)->orderBy('sequence');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopePickingUp($query)
    {
        return $query->where('status', 'picking_up');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}
