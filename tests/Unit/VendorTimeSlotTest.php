<?php

namespace Tests\Unit;

use App\Models\Vendor;
use App\Models\VendorTimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTimeSlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_without_time_slots_is_always_open(): void
    {
        $vendor = Vendor::factory()->create();

        $this->assertTrue($vendor->isOpenAt(now()));
    }

    public function test_vendor_is_open_within_defined_slot_and_closed_outside(): void
    {
        $vendor = Vendor::factory()->create();

        VendorTimeSlot::create([
            'vendor_id' => $vendor->id,
            'day_of_week' => now()->dayOfWeek,
            'opens_at' => '09:00:00',
            'closes_at' => '17:00:00',
            'is_active' => true,
        ]);

        // 10:00 should be open
        $openTime = now()->setTime(10, 0);
        $this->assertTrue($vendor->isOpenAt($openTime));

        // 18:00 should be closed
        $closedTime = now()->setTime(18, 0);
        $this->assertFalse($vendor->isOpenAt($closedTime));
    }
}
