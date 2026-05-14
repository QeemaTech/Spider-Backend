<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Deliveries\CreateRequest;
use App\Http\Requests\Admin\Deliveries\UpdateRequest;
use App\Models\Delivery;
use App\Models\Shift;
use App\Models\Zone;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryService $service
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'is_active' => $request->get('is_active', ''),
            'vehicle_type' => (string) $request->get('vehicle_type', ''),
        ];

        $deliveries = $this->service->getPaginatedDeliveries($perPage, $filters);

        return view('admin.deliveries.index', compact('deliveries', 'filters'));
    }

    public function create(): View
    {
        $zones = Zone::active()->orderBy('name')->get();
        $users = \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.deliveries.create', compact('zones', 'users'));
    }

    public function store(CreateRequest $request): RedirectResponse
    {
        $this->service->createDelivery($request->validated());

        return redirect()->route('admin.deliveries.index')
            ->with('success', __('Delivery created successfully.'));
    }

    public function show(Delivery $delivery): View
    {
        $delivery->load([
            'user',
            'zones',
            'shifts' => fn ($q) => $q->orderBy('date')->orderBy('start_time'),
            'walletTransactions' => fn ($q) => $q->latest()->limit(100),
        ]);

        $availableShifts = Shift::upcoming()
            ->withCount('deliveries')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->filter(function (Shift $shift) use ($delivery) {
                if ($shift->isFull()) {
                    return false;
                }
                if ($delivery->shifts->contains('id', $shift->id)) {
                    return false;
                }

                return true;
            });

        return view('admin.deliveries.show', compact('delivery', 'availableShifts'));
    }

    public function updateWallet(Request $request, Delivery $delivery): RedirectResponse
    {
        $request->validate([
            'wallet' => ['required', 'numeric', 'min:0'],
        ]);

        $this->service->updateDelivery($delivery, [
            'wallet' => (float) $request->input('wallet'),
        ]);

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', __('Wallet updated successfully.'));
    }

    public function assignShift(Request $request, Delivery $delivery): RedirectResponse
    {
        $request->validate([
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
        ]);

        $shift = Shift::withCount('deliveries')->findOrFail($request->shift_id);

        if ($shift->isFull()) {
            return redirect()->route('admin.deliveries.show', $delivery)
                ->with('error', __('This shift is already full.'));
        }

        if (! $shift->isUpcoming()) {
            return redirect()->route('admin.deliveries.show', $delivery)
                ->with('error', __('You can only assign upcoming shifts.'));
        }

        if ($delivery->shifts()->where('shift_id', $shift->id)->exists()) {
            return redirect()->route('admin.deliveries.show', $delivery)
                ->with('error', __('This delivery is already assigned to this shift.'));
        }

        $delivery->shifts()->attach($shift->id);

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', __('Delivery assigned to shift successfully.'))
            ->with('scroll_to', 'assign-shift');
    }

    public function unassignShift(Request $request, Delivery $delivery, Shift $shift): RedirectResponse
    {
        $delivery->shifts()->detach($shift->id);

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', __('Delivery removed from shift.'));
    }

    public function edit(Delivery $delivery): View
    {
        $zones = Zone::active()->orderBy('name')->get();
        $users = \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.deliveries.edit', compact('delivery', 'zones', 'users'));
    }

    public function update(UpdateRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->service->updateDelivery($delivery, $request->validated());

        return redirect()->route('admin.deliveries.index')
            ->with('success', __('Delivery updated successfully.'));
    }

    public function destroy(Delivery $delivery): RedirectResponse
    {
        $this->service->deleteDelivery($delivery);

        return redirect()->route('admin.deliveries.index')
            ->with('success', __('Delivery deleted successfully.'));
    }
}
