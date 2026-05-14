<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRequest;
use App\Services\DeliveryRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryRequestController extends Controller
{
    public function __construct(
        protected DeliveryRequestService $service
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'status' => (string) $request->get('status', ''),
        ];

        $requests = $this->service->listForAdmin($perPage, $filters);

        return view('admin.delivery_requests.index', compact('requests', 'filters'));
    }

    public function approve(DeliveryRequest $deliveryRequest): RedirectResponse
    {
        try {
            $this->service->approve($deliveryRequest);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Request accepted. Delivery profile has been created for this user.'));
    }

    public function reject(Request $request, DeliveryRequest $deliveryRequest): RedirectResponse
    {
        $reason = (string) $request->get('rejection_reason', '');

        try {
            $this->service->reject($deliveryRequest, $reason ?: null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Request rejected.'));
    }
}
