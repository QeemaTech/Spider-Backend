<?php

namespace App\Services;

use App\Models\Shift;
use App\Repositories\ShiftRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftService
{
    public function __construct(
        protected ShiftRepository $shiftRepository
    ) {}

    /**
     * @param  array{from_date?: string, to_date?: string}  $filters
     */
    public function getPaginatedShifts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->shiftRepository->getPaginatedShifts($perPage, $filters);
    }

    public function getShiftById(int $id): ?Shift
    {
        return $this->shiftRepository->getShiftById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createShift(array $data): Shift
    {
        return $this->shiftRepository->createShift($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateShift(Shift $shift, array $data): bool
    {
        return $this->shiftRepository->updateShift($shift, $data);
    }

    public function deleteShift(Shift $shift): bool
    {
        return $this->shiftRepository->deleteShift($shift);
    }
}
