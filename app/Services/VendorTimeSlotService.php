<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorTimeSlot;
use App\Repositories\VendorTimeSlotRepository;
use Illuminate\Database\Eloquent\Collection;

class VendorTimeSlotService
{
    public function __construct(
        protected VendorTimeSlotRepository $repository
    ) {}

    public function getForVendor(Vendor $vendor): Collection
    {
        return $this->repository->getForVendor($vendor);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForVendor(Vendor $vendor, array $data): VendorTimeSlot
    {
        return $this->repository->createForVendor($vendor, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VendorTimeSlot $timeSlot, array $data): bool
    {
        return $this->repository->update($timeSlot, $data);
    }

    public function delete(VendorTimeSlot $timeSlot): bool
    {
        return $this->repository->delete($timeSlot);
    }
}
