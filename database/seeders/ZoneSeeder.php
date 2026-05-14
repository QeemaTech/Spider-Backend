<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Seed zones in Cairo and Giza with approximate polygon boundaries.
     * Coordinates are lat/lng (WGS84). Polygons are closed shapes for delivery areas.
     */
    public function run(): void
    {
        $zones = [
            // —— Cairo ——
            [
                'name' => 'Downtown Cairo (وسط البلد)',
                'polygon' => [
                    ['lat' => 30.038, 'lng' => 31.228],
                    ['lat' => 30.038, 'lng' => 31.242],
                    ['lat' => 30.050, 'lng' => 31.242],
                    ['lat' => 30.050, 'lng' => 31.228],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Zamalek',
                'polygon' => [
                    ['lat' => 30.058, 'lng' => 31.212],
                    ['lat' => 30.058, 'lng' => 31.226],
                    ['lat' => 30.068, 'lng' => 31.226],
                    ['lat' => 30.068, 'lng' => 31.212],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Heliopolis (مصر الجديدة)',
                'polygon' => [
                    ['lat' => 30.082, 'lng' => 31.318],
                    ['lat' => 30.082, 'lng' => 31.336],
                    ['lat' => 30.096, 'lng' => 31.336],
                    ['lat' => 30.096, 'lng' => 31.318],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Nasr City (مدينة نصر)',
                'polygon' => [
                    ['lat' => 30.066, 'lng' => 31.338],
                    ['lat' => 30.066, 'lng' => 31.354],
                    ['lat' => 30.080, 'lng' => 31.354],
                    ['lat' => 30.080, 'lng' => 31.338],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Maadi (المعادي)',
                'polygon' => [
                    ['lat' => 29.954, 'lng' => 31.252],
                    ['lat' => 29.954, 'lng' => 31.268],
                    ['lat' => 29.966, 'lng' => 31.268],
                    ['lat' => 29.966, 'lng' => 31.252],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Abbassia (العباسية)',
                'polygon' => [
                    ['lat' => 30.068, 'lng' => 31.268],
                    ['lat' => 30.068, 'lng' => 31.282],
                    ['lat' => 30.078, 'lng' => 31.282],
                    ['lat' => 30.078, 'lng' => 31.268],
                ],
                'is_active' => true,
            ],
            // —— Giza ——
            [
                'name' => 'Giza - Haram (الهرم)',
                'polygon' => [
                    ['lat' => 29.968, 'lng' => 31.122],
                    ['lat' => 29.968, 'lng' => 31.142],
                    ['lat' => 29.982, 'lng' => 31.142],
                    ['lat' => 29.982, 'lng' => 31.122],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Dokki & Mohandessin (الدقي - المهندسين)',
                'polygon' => [
                    ['lat' => 30.042, 'lng' => 31.198],
                    ['lat' => 30.042, 'lng' => 31.218],
                    ['lat' => 30.054, 'lng' => 31.218],
                    ['lat' => 30.054, 'lng' => 31.198],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Agouza (العجوزة)',
                'polygon' => [
                    ['lat' => 30.050, 'lng' => 31.206],
                    ['lat' => 30.050, 'lng' => 31.224],
                    ['lat' => 30.060, 'lng' => 31.224],
                    ['lat' => 30.060, 'lng' => 31.206],
                ],
                'is_active' => true,
            ],
            [
                'name' => '6th October City (مدينة 6 أكتوبر)',
                'polygon' => [
                    ['lat' => 29.958, 'lng' => 30.922],
                    ['lat' => 29.958, 'lng' => 30.945],
                    ['lat' => 29.975, 'lng' => 30.945],
                    ['lat' => 29.975, 'lng' => 30.922],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Faisal (فيصل)',
                'polygon' => [
                    ['lat' => 29.990, 'lng' => 31.138],
                    ['lat' => 29.990, 'lng' => 31.158],
                    ['lat' => 30.004, 'lng' => 31.158],
                    ['lat' => 30.004, 'lng' => 31.138],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($zones as $data) {
            Zone::updateOrCreate(
                ['name' => $data['name']],
                [
                    'polygon' => $data['polygon'],
                    'is_active' => $data['is_active'],
                ]
            );
        }
    }
}
