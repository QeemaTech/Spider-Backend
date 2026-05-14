<?php

namespace App\Repositories;

use App\Models\Shift;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftRepository
{
    /**
     * @param  array{from_date?: string, to_date?: string}  $filters
     */
    public function getPaginatedShifts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Shift::withCount('deliveries')->with('deliveries');

        if (! empty($filters['from_date'])) {
            $query->whereDate('date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('date', '<=', $filters['to_date']);
        }

        return $query->orderBy('date')->orderBy('start_time')->paginate($perPage);
    }

    public function getShiftById(int $id): ?Shift
    {
        return Shift::with('deliveries')->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createShift(array $data): Shift
    {
        return Shift::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateShift(Shift $shift, array $data): bool
    {
        return $shift->update($data);
    }

    public function deleteShift(Shift $shift): bool
    {
        return $shift->delete();
    }
}
