<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorTimeSlots\StoreRequest;
use App\Http\Requests\Admin\VendorTimeSlots\UpdateRequest;
use App\Models\Vendor;
use App\Models\VendorTimeSlot;
use App\Services\VendorTimeSlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VendorTimeSlotController extends Controller
{
    public function __construct(
        protected VendorTimeSlotService $service
    ) {}

    protected function getVendor(): ?Vendor
    {
        return Auth::user()->vendor();
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        $this->service->createForVendor($vendor, [
            'day_of_week' => $request->integer('day_of_week'),
            'opens_at' => $request->input('opens_at'),
            'closes_at' => $request->input('closes_at'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vendor.settings.index')
            ->with('success', __('Time slot added successfully.'));
    }

    public function update(UpdateRequest $request, VendorTimeSlot $timeSlot): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        if ($timeSlot->vendor_id !== $vendor->id) {
            abort(404);
        }

        $this->service->update($timeSlot, [
            'day_of_week' => $request->integer('day_of_week'),
            'opens_at' => $request->input('opens_at'),
            'closes_at' => $request->input('closes_at'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vendor.settings.index')
            ->with('success', __('Time slot updated successfully.'));
    }

    public function destroy(VendorTimeSlot $timeSlot): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(404, __('Vendor account not found.'));
        }

        if ($timeSlot->vendor_id !== $vendor->id) {
            abort(404);
        }

        $this->service->delete($timeSlot);

        return redirect()->route('vendor.settings.index')
            ->with('success', __('Time slot deleted successfully.'));
    }
}
