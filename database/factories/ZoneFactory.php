<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $centerLat = 30.0444;
        $centerLng = 31.2357;
        $offset = 0.01;

        return [
            'name' => fake()->city().' Zone',
            'polygon' => [
                ['lat' => $centerLat - $offset, 'lng' => $centerLng - $offset],
                ['lat' => $centerLat + $offset, 'lng' => $centerLng - $offset],
                ['lat' => $centerLat + $offset, 'lng' => $centerLng + $offset],
                ['lat' => $centerLat - $offset, 'lng' => $centerLng + $offset],
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
