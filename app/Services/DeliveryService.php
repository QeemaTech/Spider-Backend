<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\User;
use App\Repositories\DeliveryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class DeliveryService
{
    public function __construct(
        protected DeliveryRepository $deliveryRepository
    ) {}

    /**
     * @param  array{search?: string, is_active?: string|bool, vehicle_type?: string}  $filters
     */
    public function getPaginatedDeliveries(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->deliveryRepository->getPaginatedDeliveries($perPage, $filters);
    }

    public function getDeliveryById(int $id): ?Delivery
    {
        return $this->deliveryRepository->getDeliveryById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDelivery(array $data): Delivery
    {
        $data['wallet'] = $data['wallet'] ?? 0;

        $userType = $data['user_type'] ?? 'new';

        if ($userType === 'new' && ! empty($data['new_user_email'])) {
            $user = User::create([
                'name' => $data['new_user_name'],
                'email' => $data['new_user_email'],
                'phone' => $data['new_user_phone'] ?? null,
                'password' => Hash::make($data['new_user_password']),
                'is_active' => true,
                'role' => 'delivery',
                'is_verified' => false,
            ]);

            $data['user_id'] = $user->id;
        }

        unset(
            $data['user_type'],
            $data['new_user_name'],
            $data['new_user_email'],
            $data['new_user_phone'],
            $data['new_user_password'],
            $data['new_user_password_confirmation']
        );

        return $this->deliveryRepository->createDelivery($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDelivery(Delivery $delivery, array $data): bool
    {
        return $this->deliveryRepository->updateDelivery($delivery, $data);
    }

    public function deleteDelivery(Delivery $delivery): bool
    {
        return $this->deliveryRepository->deleteDelivery($delivery);
    }
}
