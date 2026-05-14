<?php

namespace App\Services;

use App\Models\Zone;
use App\Repositories\ZoneRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ZoneService
{
    public function __construct(
        protected ZoneRepository $zoneRepository
    ) {}

    public function getAllZones(): Collection
    {
        return $this->zoneRepository->getAllZones();
    }

    /**
     * @param  array{search?: string, is_active?: string|bool}  $filters
     */
    public function getPaginatedZones(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->zoneRepository->getPaginatedZones($perPage, $filters);
    }

    public function getZoneById(int $id): ?Zone
    {
        return $this->zoneRepository->getZoneById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createZone(array $data): Zone
    {
        if (isset($data['polygon']) && is_string($data['polygon'])) {
            $data['polygon'] = json_decode($data['polygon'], true) ?? [];
        }

        return $this->zoneRepository->createZone($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateZone(Zone $zone, array $data): bool
    {
        if (isset($data['polygon']) && is_string($data['polygon'])) {
            $data['polygon'] = json_decode($data['polygon'], true) ?? $zone->polygon;
        }

        return $this->zoneRepository->updateZone($zone, $data);
    }

    public function deleteZone(Zone $zone): bool
    {
        return $this->zoneRepository->deleteZone($zone);
    }
}
