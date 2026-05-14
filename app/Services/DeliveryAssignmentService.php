<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryAssignmentPickup;
use App\Models\DeliveryWalletTransaction;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\VendorOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryAssignmentService
{
    public function __construct(
        protected GoogleMapsDirectionsService $googleMaps
    ) {}

    /**
     * Assign an order (ready_for_delivery) to a delivery driver. Creates assignment and pickup sequence from vendor orders.
     */
    public function assignOrderToDelivery(Order $order, Delivery $delivery, float $totalKm): DeliveryAssignment
    {
        if ($order->status !== 'ready_for_delivery') {
            throw new \InvalidArgumentException(__('Order must be ready for delivery to assign a driver.'));
        }

        if (DeliveryAssignment::where('order_id', $order->id)->whereIn('status', ['assigned', 'picking_up', 'in_transit'])->exists()) {
            throw new \InvalidArgumentException(__('This order is already assigned to a driver.'));
        }

        $maxWallet = (float) setting('max_delivery_wallet', 0);
        if ($maxWallet > 0 && (float) $delivery->wallet >= $maxWallet) {
            throw new \InvalidArgumentException(__('Your wallet has reached the maximum limit. Please transfer your balance and wait for admin to update it before receiving new orders.'));
        }

        $pricePerKm = (float) setting('shipping_price_per_km', 5);
        $shippingCost = round($totalKm * $pricePerKm, 2);

        return DB::transaction(function () use ($order, $delivery, $totalKm, $shippingCost) {
            $assignment = DeliveryAssignment::create([
                'order_id' => $order->id,
                'delivery_id' => $delivery->id,
                'status' => 'assigned',
                'total_km' => $totalKm,
                'shipping_cost' => $shippingCost,
                'assigned_at' => now(),
            ]);

            $vendorOrders = VendorOrder::query()
                ->where('order_id', $order->id)
                ->whereIn('status', ['ready_for_pickup', 'shipped'])
                ->orderBy('id')
                ->get();

            foreach ($vendorOrders as $index => $vendorOrder) {
                DeliveryAssignmentPickup::create([
                    'delivery_assignment_id' => $assignment->id,
                    'vendor_order_id' => $vendorOrder->id,
                    'sequence' => $index + 1,
                    'picked_at' => null,
                ]);
            }

            return $assignment->load('pickups.vendorOrder');
        });
    }

    /**
     * Calculate shipping cost from total km using settings.
     */
    public function calculateShippingCost(float $totalKm): float
    {
        $pricePerKm = (float) setting('shipping_price_per_km', 5);

        return round($totalKm * $pricePerKm, 2);
    }

    /**
     * Mark a pickup as done (driver picked up from this vendor order).
     */
    public function markPickupDone(DeliveryAssignment $assignment, int $vendorOrderId): void
    {
        $pickup = $assignment->pickups()->where('vendor_order_id', $vendorOrderId)->first();
        if (! $pickup || $pickup->picked_at) {
            throw new \InvalidArgumentException(__('Invalid or already picked up.'));
        }

        $pickup->update(['picked_at' => now()]);
        VendorOrder::where('id', $vendorOrderId)->update(['status' => 'shipped']);
    }

    /**
     * Set assignment status to picking_up (driver started collecting).
     */
    public function startPickingUp(DeliveryAssignment $assignment): void
    {
        if ($assignment->status !== 'assigned') {
            throw new \InvalidArgumentException(__('Invalid status.'));
        }
        $assignment->update(['status' => 'picking_up']);
    }

    /**
     * Set assignment status to in_transit (driver collected all, heading to customer).
     */
    public function markInTransit(DeliveryAssignment $assignment): void
    {
        if (! in_array($assignment->status, ['assigned', 'picking_up'], true)) {
            throw new \InvalidArgumentException(__('Invalid status.'));
        }
        $assignment->update(['status' => 'in_transit']);
    }

    /**
     * Mark assignment as delivered: all vendor orders and order become delivered.
     */
    public function markDelivered(DeliveryAssignment $assignment): void
    {
        if ($assignment->status === 'delivered') {
            throw new \InvalidArgumentException(__('Already delivered.'));
        }

        DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            $order = $assignment->order;
            $previousOrderStatus = $order->status;

            foreach ($order->vendorOrders as $vendorOrder) {
                if ($vendorOrder->status !== 'delivered') {
                    $vendorOrder->update(['status' => 'delivered']);
                }
            }

            $order->update(['status' => 'delivered']);

            OrderLog::create([
                'order_id' => $order->id,
                'vendor_order_id' => null,
                'user_id' => Auth::id(),
                'type' => 'order_status_change',
                'from_status' => $previousOrderStatus,
                'to_status' => 'delivered',
                'payload' => ['delivery_assignment_id' => $assignment->id],
            ]);

            $delivery = $assignment->delivery;
            $shippingCost = (float) $assignment->shipping_cost;
            if ($delivery && $shippingCost > 0) {
                $balanceBefore = (float) $delivery->wallet;
                $balanceAfter = round($balanceBefore + $shippingCost, 2);
                $delivery->update(['wallet' => $balanceAfter]);
                DeliveryWalletTransaction::create([
                    'delivery_id' => $delivery->id,
                    'type' => DeliveryWalletTransaction::TYPE_DELIVERY_COMPLETED,
                    'amount' => $shippingCost,
                    'balance_after' => $balanceAfter,
                    'reference_type' => DeliveryAssignment::class,
                    'reference_id' => $assignment->id,
                    'notes' => __('Order #:id delivery completed', ['id' => $order->id]),
                ]);
            }
        });
    }

    /**
     * Update total_km and recalculate shipping_cost on assignment (e.g. from API after route calculation).
     */
    public function updateAssignmentDistance(DeliveryAssignment $assignment, float $totalKm): void
    {
        $shippingCost = $this->calculateShippingCost($totalKm);
        $assignment->update(['total_km' => $totalKm, 'shipping_cost' => $shippingCost]);
    }

    /**
     * Get estimated distance (km) and optional route polyline. Uses Google Directions API when configured, else haversine.
     *
     * @return array{distance_km: float, polyline: string|null}
     */
    public function getEstimatedDistanceAndPolyline(Order $order): array
    {
        $fromGoogle = $this->googleMaps->getRouteDistanceAndPolyline($order);
        if ($fromGoogle !== null) {
            return [
                'distance_km' => $fromGoogle['distance_km'],
                'polyline' => $fromGoogle['polyline'],
            ];
        }
        $distanceKm = $this->estimateTotalKm($order);

        return [
            'distance_km' => $distanceKm,
            'polyline' => null,
        ];
    }

    /**
     * Estimate total delivery distance in km: branch1 -> branch2 -> ... -> customer address.
     * Uses order of vendor_orders by id. Returns 0 if any branch or address has no coordinates.
     */
    public function estimateTotalKm(Order $order): float
    {
        $address = $order->address;
        if (! $address || $address->latitude === null || $address->longitude === null) {
            return 0.0;
        }

        $vendorOrders = VendorOrder::query()
            ->where('order_id', $order->id)
            ->whereIn('status', ['ready_for_pickup', 'shipped'])
            ->with('branch')
            ->orderBy('id')
            ->get();

        $totalKm = 0.0;
        $prevLat = null;
        $prevLon = null;

        foreach ($vendorOrders as $vo) {
            $branch = $vo->branch;
            if (! $branch || $branch->latitude === null || $branch->longitude === null) {
                return 0.0;
            }
            $lat = (float) $branch->latitude;
            $lon = (float) $branch->longitude;
            if ($prevLat !== null && $prevLon !== null) {
                $totalKm += $this->distanceKm($prevLat, $prevLon, $lat, $lon);
            }
            $prevLat = $lat;
            $prevLon = $lon;
        }

        if ($prevLat !== null && $prevLon !== null) {
            $totalKm += $this->distanceKm($prevLat, $prevLon, (float) $address->latitude, (float) $address->longitude);
        }

        return round($totalKm, 2);
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        if (($lat1 === 0.0 && $lon1 === 0.0) || ($lat2 === 0.0 && $lon2 === 0.0)) {
            return 0.0;
        }
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
