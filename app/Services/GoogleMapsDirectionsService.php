<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsDirectionsService
{
    private const DIRECTIONS_URL = 'https://maps.googleapis.com/maps/api/directions/json';

    /**
     * Get route distance (km) and encoded polyline from Google Directions API.
     * Route: first branch -> ... -> last branch -> delivery address.
     *
     * @return array{distance_km: float, polyline: string|null}|null Null if API key missing, request fails, or order has no valid waypoints.
     */
    public function getRouteDistanceAndPolyline(Order $order): ?array
    {
        $apiKey = config('services.google.maps_api_key');
        if (empty($apiKey)) {
            return null;
        }

        $order->loadMissing(['address', 'vendorOrders.branch']);
        $address = $order->address;
        if (! $address || $address->latitude === null || $address->longitude === null) {
            return null;
        }

        $vendorOrders = $order->vendorOrders()
            ->whereIn('status', ['ready_for_pickup', 'shipped'])
            ->with('branch')
            ->orderBy('id')
            ->get();

        $waypoints = [];
        foreach ($vendorOrders as $vo) {
            $branch = $vo->branch;
            if (! $branch || $branch->latitude === null || $branch->longitude === null) {
                return null;
            }
            $waypoints[] = ['lat' => (float) $branch->latitude, 'lng' => (float) $branch->longitude];
        }

        if ($waypoints === []) {
            return null;
        }

        $origin = $waypoints[0]['lat'].','.$waypoints[0]['lng'];
        $destination = (float) $address->latitude.','.(float) $address->longitude;
        $middle = array_slice($waypoints, 1);
        $waypointsParam = $middle === [] ? null : implode('|', array_map(fn ($w) => $w['lat'].','.$w['lng'], $middle));

        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'mode' => 'driving',
            'key' => $apiKey,
        ];
        if ($waypointsParam !== null) {
            $params['waypoints'] = $waypointsParam;
        }

        try {
            $response = Http::timeout(10)->get(self::DIRECTIONS_URL, $params);
        } catch (\Throwable $e) {
            Log::warning('Google Directions API request failed: '.$e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (($data['status'] ?? '') !== 'OK' || empty($data['routes'][0])) {
            return null;
        }

        $route = $data['routes'][0];
        $totalMeters = 0;
        foreach ($route['legs'] ?? [] as $leg) {
            $totalMeters += (float) ($leg['distance']['value'] ?? 0);
        }
        $distanceKm = round($totalMeters / 1000, 2);
        $polyline = $route['overview_polyline']['points'] ?? null;

        return [
            'distance_km' => $distanceKm,
            'polyline' => $polyline,
        ];
    }
}
