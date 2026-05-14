<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorOrderResource;
use App\Models\VendorOrder;
use App\Models\VendorUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $today = now()->startOfDay();
        $branchId = $this->resolveBranchRestriction();

        $baseQuery = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            });

        if ($branchId !== null) {
            $baseQuery->where('branch_id', $branchId);
        }

        $todayOrdersCount = (clone $baseQuery)->count();

        $todayEarnings = (float) (clone $baseQuery)
            ->whereHas('order', function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                    ->where('payment_status', 'paid');
            })
            ->sum('total');

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $todayOrders = (clone $baseQuery)
            ->with([
                'order.user',
                'order.address',
                'vendor',
                'branch',
                'items.product',
                'items.variant',
            ])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'today_orders_count' => $todayOrdersCount,
                'today_earnings' => round($todayEarnings, 2),
                'today_orders' => VendorOrderResource::collection($todayOrders),
            ],
            'meta' => [
                'current_page' => $todayOrders->currentPage(),
                'last_page' => $todayOrders->lastPage(),
                'per_page' => $todayOrders->perPage(),
                'total' => $todayOrders->total(),
            ],
        ]);
    }

    private function resolveBranchRestriction(): ?int
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('vendor_employee')) {
            return null;
        }

        $vendorUser = VendorUser::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $vendorUser || $vendorUser->user_type !== 'branch') {
            return null;
        }

        return (int) $vendorUser->branch_id;
    }
}

