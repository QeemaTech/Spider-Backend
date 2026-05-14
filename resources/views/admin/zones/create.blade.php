@extends('layouts.app')

@php
    $page = 'zones';
@endphp

@section('title', __('Create Zone'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.zones.index') }}">{{ __('Zones') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Create Zone') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Create Zone') }}</h1>
                <p class="text-muted mb-0">{{ __('Add a delivery zone by drawing its boundary on the map') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.zones.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Zone Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.zones.store') }}" method="POST" id="zoneForm">
                            @csrf

                            <div class="mb-4">
                                <label for="name" class="form-label">{{ __('Zone Name') }} *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="{{ __('e.g. Downtown, North District') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">{{ __('Zone Boundary') }} *</label>
                                <p class="text-muted small mb-2">{{ __('Click on the map to add points. Add at least 3 points to form a polygon. Use "Clear" to start over.') }}</p>
                                <div id="zone-map" style="height: 400px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                                <input type="hidden" name="polygon" id="polygon-input" value="{{ old('polygon', '[]') }}">
                                @error('polygon')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <button type="button" id="clear-polygon-btn" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-eraser me-1"></i>{{ __('Clear polygon') }}
                                    </button>
                                    <span id="point-count" class="ms-2 text-muted small">0 {{ __('points') }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.zones.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Create Zone') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('Tips') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>{{ __('Name') }}:</strong> {{ __('Use a clear name (e.g. area or district name).') }}</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>{{ __('Polygon') }}:</strong> {{ __('Draw the delivery area. Orders inside this area will use this zone.') }}</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i><strong>{{ __('Active') }}:</strong> {{ __('Only active zones are used for orders.') }}</li>
                        </ul>
                    </div>
                </div>
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
    var defaultCenter = [30.0444, 31.2357];
    var map = L.map('zone-map').setView(defaultCenter, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

    var points = [];
    var polygonLayer = null;
    var markersLayer = L.layerGroup().addTo(map);

    var polygonInput = document.getElementById('polygon-input');
    var pointCountEl = document.getElementById('point-count');

    function updatePolygonInput() {
        polygonInput.value = JSON.stringify(points);
        pointCountEl.textContent = points.length + ' {{ __('points') }}';
    }

    function redrawPolygon() {
        if (polygonLayer) map.removeLayer(polygonLayer);
        markersLayer.clearLayers();
        if (points.length >= 3) {
            var latlngs = points.map(function(p) { return [p.lat, p.lng]; });
            polygonLayer = L.polygon(latlngs, { color: '#0d6efd', fillOpacity: 0.2 }).addTo(map);
        }
        points.forEach(function(p) {
            L.marker([p.lat, p.lng]).addTo(markersLayer);
        });
        updatePolygonInput();
    }

    map.on('click', function(e) {
        points.push({ lat: e.latlng.lat, lng: e.latlng.lng });
        redrawPolygon();
    });

    document.getElementById('clear-polygon-btn').addEventListener('click', function() {
        points = [];
        redrawPolygon();
    });

    var oldPolygon = polygonInput.value;
    if (oldPolygon) {
        try {
            var parsed = JSON.parse(oldPolygon);
            if (Array.isArray(parsed) && parsed.length >= 3) {
                points = parsed.map(function(p) {
                    return { lat: parseFloat(p.lat ?? p[0]), lng: parseFloat(p.lng ?? p[1]) };
                });
                redrawPolygon();
            }
        } catch (err) {}
    }
    updatePolygonInput();
});
</script>
@endpush
