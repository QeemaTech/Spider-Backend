<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorOrderResource;
use App\Models\VendorUser;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;

class OrderController extends Controller
{
    public function __construct(protected OrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('You are not associated with a vendor.'),
            ], 403);
        }

        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'string', 'in:in-progress,complete,cancelled,pending,processing,ready_for_pickup,shipped,delivered,refunded'],
            'search' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer'],
            'order_id' => ['nullable', 'integer'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'min_total' => ['nullable', 'numeric', 'min:0'],
            'max_total' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'sort' => ['nullable', 'string', 'in:latest,oldest,total_asc,total_desc'],
        ]);

        $perPage = (int) $request->get('per_page', 15);
        $status = (string) $request->get('status', '');

        $filters = [
            'search' => (string) $request->get('search', ''),
            'branch_id' => $request->get('branch_id', ''),
            'order_id' => $request->get('order_id', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_total' => $request->get('min_total', ''),
            'max_total' => $request->get('max_total', ''),
            'payment_status' => (string) $request->get('payment_status', ''),
            'payment_method' => (string) $request->get('payment_method', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        if ($status !== '') {
            $filters['status'] = $this->mapStatusFilter($status);
        }

        $branchId = $this->resolveBranchRestriction();
        if ($branchId !== null) {
            $filters['branch_id'] = $branchId;
        }

        $vendorOrders = $this->service->getPaginatedVendorOrdersForVendor($vendor->id, $perPage, $filters);

        $stats = $this->getVendorOrderStats($vendor->id, $branchId);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'data' => VendorOrderResource::collection($vendorOrders),
            'meta' => [
                'current_page' => $vendorOrders->currentPage(),
                'last_page' => $vendorOrders->lastPage(),
                'per_page' => $vendorOrders->perPage(),
                'total' => $vendorOrders->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('You are not associated with a vendor.'),
            ], 403);
        }

        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendor->id);

        if (! $vendorOrder) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor order not found.'),
            ], 404);
        }

        $branchId = $this->resolveBranchRestriction();
        if ($branchId !== null && (int) $vendorOrder->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to view this order.'),
            ], 403);
        }

        $vendorOrder->loadMissing(['logs.user']);

        return response()->json([
            'success' => true,
            'data' => new VendorOrderResource($vendorOrder),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,ready_for_pickup,shipped,delivered,cancelled'],
        ]);

        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('You are not associated with a vendor.'),
            ], 403);
        }

        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendor->id);

        if (! $vendorOrder) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor order not found.'),
            ], 404);
        }

        $branchId = $this->resolveBranchRestriction();
        if ($branchId !== null && (int) $vendorOrder->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to update this order.'),
            ], 403);
        }

        $updated = $this->service->updateVendorOrderStatus($vendorOrder, (string) $request->status);
        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid status transition.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Vendor order status updated successfully.'),
            'data' => new VendorOrderResource($vendorOrder->fresh()),
        ]);
    }

    public function invoice(int $id)
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('You are not associated with a vendor.'),
            ], 403);
        }

        $vendorOrder = $this->service->getVendorOrderByIdForVendor($id, $vendor->id);
        if (! $vendorOrder) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor order not found.'),
            ], 404);
        }

        $branchId = $this->resolveBranchRestriction();
        if ($branchId !== null && (int) $vendorOrder->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to view this order.'),
            ], 403);
        }

        $pdf = PDF::loadView('vendor.orders.invoice', [
            'vendorOrder' => $vendorOrder,
            'asPdf' => true,
        ]);

        return $pdf->stream('invoice-'.$vendorOrder->id.'.pdf');
    }

    private function mapStatusFilter(string $status): string
    {
        return match ($status) {
            'in-progress' => 'pending,processing,ready_for_pickup,shipped',
            'complete' => 'delivered',
            default => $status,
        };
    }

    private function getVendorOrderStats(int $vendorId, ?int $branchId): array
    {
        $query = \App\Models\VendorOrder::query()->where('vendor_id', $vendorId);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $now = now();
        $startOfWeek = Carbon::now()->startOfWeek();

        return [
            'today_orders_count' => (clone $query)->whereDate('created_at', $now->toDateString())->count(),
            'this_week_orders_count' => (clone $query)->whereDate('created_at', '>=', $startOfWeek->toDateString())->count(),
            'this_month_orders_count' => (clone $query)->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)->count(),
        ];
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

