<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeliveryRequestService
{
    public function listForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = DeliveryRequest::query()
            ->with(['user', 'reviewer'])
            ->latest();

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function approve(DeliveryRequest $deliveryRequest): Delivery
    {
        return DB::transaction(function () use ($deliveryRequest) {
            if ($deliveryRequest->status !== 'pending') {
                throw new \InvalidArgumentException(__('This request has already been processed.'));
            }

            $userId = $deliveryRequest->user_id;

            if (! $userId) {
                $user = User::create([
                    'name' => $deliveryRequest->name,
                    'phone' => $deliveryRequest->phone,
                    'email' => $deliveryRequest->email ?? ('delivery-'.Str::random(8).'@temp.local'),
                    'password' => Hash::make(Str::random(32)),
                    'is_active' => true,
                    'is_verified' => false,
                ]);
                $userId = $user->id;
                $deliveryRequest->update(['user_id' => $userId]);
            }

            if (Delivery::where('user_id', $userId)->exists()) {
                throw new \InvalidArgumentException(__('This user is already a delivery person.'));
            }

            $delivery = Delivery::create([
                'user_id' => $userId,
                'vehicle_type' => $deliveryRequest->vehicle_type,
                'vehicle_number' => $deliveryRequest->vehicle_number,
                'vehicle_color' => $deliveryRequest->vehicle_color,
                'wallet' => 0,
                'is_active' => true,
            ]);

            $deliveryRequest->update([
                'status' => 'accepted',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            return $delivery;
        });
    }

    public function reject(DeliveryRequest $deliveryRequest, ?string $reason = null): void
    {
        if ($deliveryRequest->status !== 'pending') {
            throw new \InvalidArgumentException(__('This request has already been processed.'));
        }

        $deliveryRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
