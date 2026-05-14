<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\AssignDeliveryRequest;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Zone;
use App\Services\DeliveryAssignmentService;
use App\Services\OrderService;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;

class OrderController extends Controller
{
    protected OrderService $service;

    protected VendorService $vendorService;

    public function __construct(
        OrderService $service,
        VendorService $vendorService,
        protected DeliveryAssignmentService $assignmentService
    ) {
        $this->service = $service;
        $this->vendorService = $vendorService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'status' => (string) $request->get('status', ''),
            'payment_status' => (string) $request->get('payment_status', ''),
            'payment_method' => (string) $request->get('payment_method', ''),
            'refund_status' => (string) $request->get('refund_status', ''),
            'user_id' => $request->get('user_id', ''),
            'vendor_id' => $request->get('vendor_id', ''),
            'branch_id' => $request->get('branch_id', ''),
            'coupon_id' => $request->get('coupon_id', ''),
            'address_id' => $request->get('address_id', ''),
            'vendor_order_status' => (string) $request->get('vendor_order_status', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_total' => $request->get('min_total', ''),
            'max_total' => $request->get('max_total', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $orders = $this->service->getPaginatedOrders($perPage, $filters);
        $vendors = $this->vendorService->getActiveVendors();

        return view('admin.orders.index', compact('orders', 'vendors'));
    }

    /**
     * Display the specified order
     */
    public function show(int $id): View
    {
        $order = $this->service->getOrderById($id);

        if (! $order) {
            abort(404, __('Order not found.'));
        }

        $order->loadMissing([
            'logs.user',
            'address',
            'deliveryAssignments.delivery.user',
        ]);

        $activeDeliveries = collect([]);
        $zonesContainingAddress = [];
        $orderHasActiveAssignment = false;

        if ($order->status === 'ready_for_delivery') {
            $orderHasActiveAssignment = $order->deliveryAssignments()
                ->whereIn('status', ['assigned', 'picking_up', 'in_transit'])
                ->exists();
            $activeDeliveries = Delivery::query()
                ->where('is_active', true)
                ->with('user')
                ->orderBy('id')
                ->get();
            if ($order->address && $order->address->latitude !== null && $order->address->longitude !== null) {
                $point = [
                    'lat' => (float) $order->address->latitude,
                    'lng' => (float) $order->address->longitude,
                ];
                foreach (Zone::active()->get() as $zone) {
                    if ($zone->containsPoint($point)) {
                        $zonesContainingAddress[] = $zone->name;
                    }
                }
            }
        }

        return view('admin.orders.show', compact(
            'order',
            'activeDeliveries',
            'zonesContainingAddress',
            'orderHasActiveAssignment'
        ));
    }

    /**
     * Download or view full order invoice as PDF.
     */
    public function invoice(int $id)
    {
        $order = $this->service->getOrderById($id);

        if (! $order) {
            abort(404, __('Order not found.'));
        }

        $order->loadMissing([
            'user',
            'address',
            'items.product',
            'items.variant',
            'vendorOrders.vendor',
        ]);

        $pdf = PDF::loadView('admin.orders.invoice', [
            'order' => $order,
            'asPdf' => true,
        ]);

        return $pdf->stream('order-invoice-'.$order->id.'.pdf');
    }

    /**
     * Update order
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $order = $this->service->getOrderById($id);

        if (! $order) {
            return redirect()->route('admin.orders.index')
                ->with('error', __('Order not found.'));
        }

        $request->validate([
            'status' => ['nullable', 'string', 'in:pending,processing,ready_for_delivery,shipped,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = $request->only(['status', 'notes']);

        $this->service->updateOrder($order, $data);

        return redirect()->route('admin.orders.show', $id)
            ->with('success', __('Order updated successfully.'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,ready_for_delivery,shipped,delivered,cancelled'],
        ]);

        $order = $this->service->getOrderById($id);

        if (! $order) {
            return redirect()->route('admin.orders.index')
                ->with('error', __('Order not found.'));
        }

        $this->service->updateStatus($order, $request->status);

        return redirect()->route('admin.orders.show', $id)
            ->with('success', __('Order status updated successfully.'));
    }

    /**
     * Refund a delivered order (wallet + points).
     */
    public function refund(int $id): RedirectResponse
    {
        $order = $this->service->getOrderById($id);

        if (! $order) {
            return redirect()->route('admin.orders.index')
                ->with('error', __('Order not found.'));
        }

        if ($order->status !== 'delivered') {
            return redirect()->route('admin.orders.show', $id)
                ->with('error', __('Only delivered orders can be refunded.'));
        }

        if ($order->refund_status === 'refunded') {
            return redirect()->route('admin.orders.show', $id)
                ->with('error', __('This order has already been refunded.'));
        }

        try {
            $this->service->refundOrder($order, (int) $order->user_id);
        } catch (\Throwable $e) {
            return redirect()->route('admin.orders.show', $id)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $id)
            ->with('success', __('Order refunded successfully.'));
    }

    /**
     * Remove the specified order (soft delete)
     */
    public function destroy(int $id): RedirectResponse
    {
        $order = $this->service->getOrderById($id);

        if (! $order) {
            return redirect()->route('admin.orders.index')
                ->with('error', __('Order not found.'));
        }

        $this->service->deleteOrder($order);

        return redirect()->route('admin.orders.index')
            ->with('success', __('Order deleted successfully.'));
    }

    /**
     * Assign a delivery driver to an order (ready_for_delivery). Used when the order is outside all zones or admin chooses manually.
     */
    public function assignDelivery(AssignDeliveryRequest $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'ready_for_delivery') {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', __('Only orders that are ready for delivery can be assigned to a driver.'));
        }

        if ($order->deliveryAssignments()->whereIn('status', ['assigned', 'picking_up', 'in_transit'])->exists()) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', __('This order is already assigned to a driver.'));
        }

        $delivery = Delivery::find($request->validated('delivery_id'));
        if (! $delivery) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', __('Selected delivery driver not found.'));
        }

        $totalKm = $request->input('total_km');
        if ($totalKm === null || $totalKm === '') {
            $totalKm = $this->assignmentService->estimateTotalKm($order);
        } else {
            $totalKm = (float) $totalKm;
        }

        try {
            $this->assignmentService->assignOrderToDelivery($order, $delivery, $totalKm);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', __('Order assigned to delivery driver successfully.'));
    }
}
