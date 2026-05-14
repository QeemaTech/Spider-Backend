<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'polygon',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'polygon' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Deliveries that can cover orders in this zone.
     */
    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(Delivery::class, 'delivery_zone')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a point (lat, lng) is inside this zone's polygon.
     * Uses ray-casting algorithm for point-in-polygon test (lng = x, lat = y).
     *
     * @param  array{lat: float, lng: float}  $point
     */
    public function containsPoint(array $point): bool
    {
        $polygon = $this->polygon;
        if (! is_array($polygon) || count($polygon) < 3) {
            return false;
        }

        $lat = (float) ($point['lat'] ?? $point[0] ?? 0);
        $lng = (float) ($point['lng'] ?? $point[1] ?? 0);
        $n = count($polygon);
        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $vi = $polygon[$i];
            $vj = $polygon[$j];
            $latI = (float) ($vi['lat'] ?? $vi[0] ?? 0);
            $lngI = (float) ($vi['lng'] ?? $vi[1] ?? 0);
            $latJ = (float) ($vj['lat'] ?? $vj[0] ?? 0);
            $lngJ = (float) ($vj['lng'] ?? $vj[1] ?? 0);

            if ((($latI > $lat) !== ($latJ > $lat))
                && ($lng < ($lngJ - $lngI) * ($lat - $latI) / ($latJ - $latI + 1e-10) + $lngI)) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
