<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryWalletBalanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_wallet_balance_endpoint_returns_balance_and_pending_balance(): void
    {
        $user = User::factory()->create();
        $delivery = Delivery::factory()->forUser($user)->create([
            'wallet' => 1500.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'sub_total' => 100.00,
            'order_discount' => 0.00,
            'coupon_discount' => 0.00,
            'total_shipping' => 0.00,
            'points_discount' => 0.00,
            'total' => 100.00,
            'wallet_used' => 0.00,
            'status' => 'ready_for_delivery',
            'payment_status' => 'pending',
            'total_commission' => 0.00,
        ]);

        DeliveryAssignment::create([
            'order_id' => $order->id,
            'delivery_id' => $delivery->id,
            'status' => 'assigned',
            'total_km' => 12.5,
            'shipping_cost' => 250.00,
            'assigned_at' => now(),
        ]);

        DeliveryAssignment::create([
            'order_id' => $order->id,
            'delivery_id' => $delivery->id,
            'status' => 'delivered',
            'total_km' => 10.0,
            'shipping_cost' => 300.00,
            'assigned_at' => now()->subDay(),
            'delivered_at' => now(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/delivery/wallet/balance');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'balance' => 1500.00,
                    'currency' => 'EGP',
                    'pending_balance' => 250.00,
                ],
            ]);
    }
}

