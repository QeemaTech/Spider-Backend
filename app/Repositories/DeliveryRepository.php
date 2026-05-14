<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Models\DeliveryWalletTransaction;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliveryRepository
{
    /**
     * @param  array{search?: string, is_active?: string|bool, vehicle_type?: string}  $filters
     */
    public function getPaginatedDeliveries(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Delivery::with('user', 'zones');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_color', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        if (! empty($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getDeliveryById(int $id): ?Delivery
    {
        return Delivery::with('user', 'zones', 'shifts')->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDelivery(array $data): Delivery
    {
        $zoneIds = $data['zone_ids'] ?? [];
        unset($data['zone_ids']);

        $delivery = Delivery::create($data);
        $delivery->zones()->sync($zoneIds);

        return $delivery;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDelivery(Delivery $delivery, array $data): bool
    {
        $zoneIds = $data['zone_ids'] ?? null;
        unset($data['zone_ids']);

        if (array_key_exists('wallet', $data)) {
            $newWallet = (float) $data['wallet'];
            $oldWallet = (float) $delivery->wallet;
            if ($newWallet != $oldWallet) {
                DeliveryWalletTransaction::create([
                    'delivery_id' => $delivery->id,
                    'type' => DeliveryWalletTransaction::TYPE_ADMIN_ADJUSTMENT,
                    'amount' => $newWallet - $oldWallet,
                    'balance_after' => $newWallet,
                    'reference_type' => null,
                    'reference_id' => null,
                    'notes' => __('Admin wallet adjustment'),
                ]);
            }
        }

        $delivery->update($data);

        if ($zoneIds !== null) {
            $delivery->zones()->sync($zoneIds);
        }

        return true;
    }

    public function deleteDelivery(Delivery $delivery): bool
    {
        $delivery->zones()->detach();
        $delivery->shifts()->detach();

        return $delivery->delete();
    }
}
