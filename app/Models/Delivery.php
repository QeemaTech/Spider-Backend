<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type',
        'vehicle_number',
        'vehicle_color',
        'user_id',
        'wallet',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wallet' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Zones this delivery can cover orders in.
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'delivery_zone')
            ->withTimestamps();
    }

    /**
     * Shifts this delivery has taken.
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'delivery_shift')
            ->withTimestamps();
    }

    public function deliveryAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(DeliveryWalletTransaction::class)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
