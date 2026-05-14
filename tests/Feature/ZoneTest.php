<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ZoneTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_view_zones_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.zones.index'))
            ->assertOk()
            ->assertSee(__('Zones'));
    }

    public function test_admin_can_view_create_zone_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.zones.create'))
            ->assertOk()
            ->assertSee(__('Create Zone'));
    }

    public function test_admin_can_store_zone_with_valid_polygon(): void
    {
        $polygon = [
            ['lat' => 30.0, 'lng' => 31.0],
            ['lat' => 30.01, 'lng' => 31.0],
            ['lat' => 30.01, 'lng' => 31.01],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.zones.store'), [
                'name' => 'Downtown Zone',
                'polygon' => json_encode($polygon),
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.zones.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('zones', [
            'name' => 'Downtown Zone',
            'is_active' => true,
        ]);

        $zone = Zone::where('name', 'Downtown Zone')->first();
        $this->assertNotNull($zone);
        $this->assertCount(3, $zone->polygon);
    }

    public function test_admin_cannot_store_zone_without_polygon(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.zones.store'), [
                'name' => 'No Polygon Zone',
                'polygon' => '[]',
                'is_active' => true,
            ])
            ->assertSessionHasErrors('polygon');
    }

    public function test_admin_can_view_zone_show(): void
    {
        $zone = Zone::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.zones.show', $zone))
            ->assertOk()
            ->assertSee($zone->name);
    }

    public function test_admin_can_update_zone(): void
    {
        $zone = Zone::factory()->create(['name' => 'Old Name']);
        $polygon = [
            ['lat' => 30.1, 'lng' => 31.1],
            ['lat' => 30.11, 'lng' => 31.1],
            ['lat' => 30.11, 'lng' => 31.11],
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.zones.update', $zone), [
                'name' => 'Updated Zone Name',
                'polygon' => json_encode($polygon),
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.zones.index'))
            ->assertSessionHas('success');

        $zone->refresh();
        $this->assertSame('Updated Zone Name', $zone->name);
        $this->assertFalse($zone->is_active);
    }

    public function test_admin_can_delete_zone(): void
    {
        $zone = Zone::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.zones.destroy', $zone))
            ->assertRedirect(route('admin.zones.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
    }

    public function test_zone_contains_point_returns_true_when_point_inside(): void
    {
        $zone = Zone::factory()->create([
            'polygon' => [
                ['lat' => 30.0, 'lng' => 31.0],
                ['lat' => 30.02, 'lng' => 31.0],
                ['lat' => 30.02, 'lng' => 31.02],
                ['lat' => 30.0, 'lng' => 31.02],
            ],
        ]);

        $this->assertTrue($zone->containsPoint(['lat' => 30.01, 'lng' => 31.01]));
    }

    public function test_zone_contains_point_returns_false_when_point_outside(): void
    {
        $zone = Zone::factory()->create([
            'polygon' => [
                ['lat' => 30.0, 'lng' => 31.0],
                ['lat' => 30.02, 'lng' => 31.0],
                ['lat' => 30.02, 'lng' => 31.02],
                ['lat' => 30.0, 'lng' => 31.02],
            ],
        ]);

        $this->assertFalse($zone->containsPoint(['lat' => 35.0, 'lng' => 35.0]));
    }
}
