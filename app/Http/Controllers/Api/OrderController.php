<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(protected OrderService $service) {}

    /**
     * Display a listing of the authenticated user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'status' => (string) $request->get('status', ''),
            'payment_status' => (string) $request->get('payment_status', ''),
            'payment_method' => (string) $request->get('payment_method', ''),
            'refund_status' => (string) $request->get('refund_status', ''),
            'vendor_id' => $request->get('vendor_id', ''),
            'branch_id' => $request->get('branch_id', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_total' => $request->get('min_total', ''),
            'max_total' => $request->get('max_total', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $orders = $this->service->getPaginatedOrdersForUser($user->id, $perPage, $filters);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display the specified order for the authenticated user.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        $order = $this->service->getOrderByIdForUser($id, $user->id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Calculate shipping costs for the authenticated user's cart.
     *
     * This endpoint calculates shipping costs for all items in the cart without creating an order.
     * It returns detailed shipping information per vendor and total shipping cost.
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        try {
            $shippingData = $this->service->calculateShippingCost($user->id, $validated['address_id']);

            return response()->json([
                'success' => true,
                'data' => $shippingData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Create a new order for the authenticated user.
     *
     * This will create the order from the user's cart using the internal order cycle.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            // Inputs that fit the new server-side order cycle
            // Note: order_discount is now calculated automatically from product discounts
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
            // Boolean flags: if true, service will automatically calculate and use all available wallet/points
            'use_wallet' => ['nullable', 'boolean'],
            'use_points' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);
        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', '=', $validated['coupon_code'], 'and')->first();

            if (! $coupon) {
                return response()->json([
                    'success' => false,
                    'message' => __('Coupon not found.'),
                ], 404);
            }

            $validated['coupon_id'] = $coupon->id;
        }

        // Default status to pending if not provided
        if (! isset($validated['status']) || $validated['status'] === '') {
            $validated['status'] = 'pending';
        }

        $order = $this->service->createOrder($user->id, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Order created successfully.'),
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * Cancel an order for the authenticated user (if possible).
     */
    public function cancel(int $id): JsonResponse
    {
        $user = Auth::user();

        $order = $this->service->cancelOrderForUser($id, $user->id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        if ($order->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => __('Order cannot be cancelled at this stage.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Order cancelled successfully.'),
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Reorder items from a previous order by adding them to the user's cart.
     */
    public function reorder(int $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $result = $this->service->reorder($id, $user->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'added' => $result['added'],
                    'skipped' => $result['skipped'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Mark an order as paid immediately (used after successful payment).
     */
    public function pay(int $id, Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        $order = $this->service->payOrderImmediatelyForUser($id, $user->id, (string) $validated['payment_method']);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('Order paid successfully.'),
            'data' => new OrderResource($order),
        ]);
    }
}
