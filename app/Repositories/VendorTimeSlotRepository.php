<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Models\VendorTimeSlot;
use Illuminate\Database\Eloquent\Collection;

class VendorTimeSlotRepository
{
    public function getForVendor(Vendor $vendor): Collection
    {
        return $vendor->timeSlots()->orderBy('day_of_week')->orderBy('opens_at')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForVendor(Vendor $vendor, array $data): VendorTimeSlot
    {
        $data['vendor_id'] = $vendor->id;

        return VendorTimeSlot::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VendorTimeSlot $timeSlot, array $data): bool
    {
        return $timeSlot->update($data);
    }

    public function delete(VendorTimeSlot $timeSlot): bool
    {
        return (bool) $timeSlot->delete();
    }
}
