<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorTimeSlots\StoreRequest;
use App\Http\Requests\Admin\VendorTimeSlots\UpdateRequest;
use App\Models\Vendor;
use App\Models\VendorTimeSlot;
use App\Services\VendorTimeSlotService;
use Illuminate\Http\RedirectResponse;

class VendorTimeSlotController extends Controller
{
    public function __construct(
        protected VendorTimeSlotService $service
    ) {}

    public function store(StoreRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->service->createForVendor($vendor, [
            'day_of_week' => $request->integer('day_of_week'),
            'opens_at' => $request->input('opens_at'),
            'closes_at' => $request->input('closes_at'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.vendors.edit', $vendor)
            ->with('success', __('Time slot added successfully.'));
    }

    public function update(UpdateRequest $request, Vendor $vendor, VendorTimeSlot $timeSlot): RedirectResponse
    {
        if ($timeSlot->vendor_id !== $vendor->id) {
            abort(404);
        }

        $this->service->update($timeSlot, [
            'day_of_week' => $request->integer('day_of_week'),
            'opens_at' => $request->input('opens_at'),
            'closes_at' => $request->input('closes_at'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.vendors.edit', $vendor)
            ->with('success', __('Time slot updated successfully.'));
    }

    public function destroy(Vendor $vendor, VendorTimeSlot $timeSlot): RedirectResponse
    {
        if ($timeSlot->vendor_id !== $vendor->id) {
            abort(404);
        }

        $this->service->delete($timeSlot);

        return redirect()->route('admin.vendors.edit', $vendor)
            ->with('success', __('Time slot deleted successfully.'));
    }
}
