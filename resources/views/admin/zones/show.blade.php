@extends('layouts.app')

@php
    $page = 'zones';
@endphp

@section('title', $zone->name)

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.zones.index') }}">{{ __('Zones') }}</a></li>
                        <li class="breadcrumb-item active">{{ $zone->name }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ $zone->name }}</h1>
                <p class="text-muted mb-0">
                    @if($zone->is_active)
                        <span class="badge bg-success">{{ __('Active') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                    @endif
                    · {{ is_array($zone->polygon) ? count($zone->polygon) : 0 }} {{ __('points') }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.zones.edit', $zone) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.zones.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Zone Boundary') }}</h5>
            </div>
            <div class="card-body p-0">
                <div id="zone-show-map" style="height: 450px; width: 100%;"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-truck text-muted me-2"></i>{{ __('Deliveries in this zone') }}
                </h5>
                @if($zone->deliveries->count() > 0)
                    <span class="badge bg-primary">{{ $zone->deliveries->count() }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($zone->deliveries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Vehicle') }}</th>
                                    <th>{{ __('Linked user') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zone->deliveries as $delivery)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($delivery->vehicle_type) }}</span>
                                            <strong>{{ $delivery->vehicle_number }}</strong>
                                            <span class="text-muted">({{ $delivery->vehicle_color }})</span>
                                        </td>
                                        <td>
                                            @if($delivery->user)
                                                {{ $delivery->user->name }}
                                                <br><small class="text-muted">{{ $delivery->user->email }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($delivery->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0 text-center py-4">{{ __('No deliveries assigned to this zone. Assign zones in the delivery edit page.') }}</p>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var polygon = @json($zone->polygon ?? []);
    if (polygon.length < 3) return;
    var latlngs = polygon.map(function(p) { return [p.lat ?? p[0], p.lng ?? p[1]]; });
    var center = latlngs[0];
    var map = L.map('zone-show-map').setView(center, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.polygon(latlngs, { color: '#0d6efd', fillOpacity: 0.3 }).addTo(map);
});
</script>
@endpush
