<?php

namespace App\Repositories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ZoneRepository
{
    /**
     * Get all zones
     */
    public function getAllZones(): Collection
    {
        return Zone::query()->orderBy('name')->get();
    }

    /**
     * Get paginated zones with filters
     *
     * @param  array{search?: string, is_active?: string|bool}  $filters
     */
    public function getPaginatedZones(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Zone::query();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getZoneById(int $id): ?Zone
    {
        return Zone::find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createZone(array $data): Zone
    {
        return Zone::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateZone(Zone $zone, array $data): bool
    {
        return $zone->update($data);
    }

    public function deleteZone(Zone $zone): bool
    {
        return $zone->delete();
    }
}
