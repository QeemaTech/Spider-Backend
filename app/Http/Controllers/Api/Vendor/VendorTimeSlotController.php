<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorTimeSlots\StoreRequest;
use App\Http\Requests\Admin\VendorTimeSlots\UpdateRequest;
use App\Http\Resources\VendorTimeSlotResource;
use App\Models\VendorTimeSlot;
use App\Services\VendorTimeSlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VendorTimeSlotController extends Controller
{
    public function __construct(
        protected VendorTimeSlotService $service
    ) {}

    public function index(): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $timeSlots = $this->service->getForVendor($vendor);

        return response()->json([
            'success' => true,
            'data' => VendorTimeSlotResource::collection($timeSlots),
        ]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $timeSlot = $this->service->createForVendor($vendor, [
            'day_of_week' => $request->integer('day_of_week'),
            'opens_at' => $request->input('opens_at'),
            'closes_at' => $request->input('closes_at'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Time slot added successfully.'),
            'data' => new VendorTimeSlotResource($timeSlot),
        ], 201);
    }

    public function update(UpdateRequest $request, VendorTimeSlot $timeSlot): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        if ($timeSlot->vendor_id != $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => __('Time slot not found.'),
            ], 404);
        }

        $this->service->update($timeSlot, [
            'day_of_week' => $request->integer('day_of_week'),
            'opens_at' => $request->input('opens_at'),
            'closes_at' => $request->input('closes_at'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Time slot updated successfully.'),
            'data' => new VendorTimeSlotResource($timeSlot->fresh()),
        ]);
    }

    public function destroy(VendorTimeSlot $timeSlot): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        if ($timeSlot->vendor_id != $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => __('Time slot not found.'),
            ], 404);
        }

        $this->service->delete($timeSlot);

        return response()->json([
            'success' => true,
            'message' => __('Time slot deleted successfully.'),
        ]);
    }
}

