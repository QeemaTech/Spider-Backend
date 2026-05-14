@extends('layouts.app')

@php
    $page = 'reports';
@endphp

@section('title', __('Reports & Analytics'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Reports & Analytics') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ __('Reports & Analytics') }}</h1>
            <p class="text-muted mb-0">{{ __('Platform performance overview') }}</p>
        </div>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
            <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
        </button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Paid Orders') }}</div>
                            <div class="fs-4 fw-semibold">{{ $report['kpis']['paid_orders_count'] }}</div>
                        </div>
                        <div class="text-primary fs-4"><i class="bi bi-cart-check"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Paid Revenue') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($report['kpis']['paid_orders_total'], 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                        </div>
                        <div class="text-success fs-4"><i class="bi bi-cash-stack"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Total Commission') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($report['kpis']['total_commission'], 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                        </div>
                        <div class="text-warning fs-4"><i class="bi bi-percent"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Refunded Orders') }}</div>
                            <div class="fs-4 fw-semibold">{{ $report['kpis']['refunded_orders_count'] }}</div>
                            <small class="text-muted">
                                {{ number_format($report['kpis']['refunded_total'], 2) }} {{ setting('currency', 'EGP') }}
                            </small>
                        </div>
                        <div class="text-danger fs-4"><i class="bi bi-arrow-counterclockwise"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Pending Refund Requests') }}</div>
                            <div class="fs-4 fw-semibold">{{ $report['kpis']['pending_refund_requests'] }}</div>
                        </div>
                        <div class="text-info fs-4"><i class="bi bi-inbox"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Pending Withdrawals') }}</div>
                            <div class="fs-4 fw-semibold">{{ $report['kpis']['pending_withdrawals_count'] }}</div>
                            <small class="text-muted">
                                {{ number_format($report['kpis']['pending_withdrawals_total'], 2) }} {{ setting('currency', 'EGP') }}
                            </small>
                        </div>
                        <div class="text-primary fs-4"><i class="bi bi-cash-coin"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Top Vendors') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('Vendor') }}</th>
                                <th class="text-end">{{ __('Gross') }}</th>
                                <th class="text-end">{{ __('Commission') }}</th>
                                <th class="text-end">{{ __('Net') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($report['top_vendors'] as $vendor)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $vendor['vendor_name'] }}</div>
                                        <small class="text-muted">ID: {{ $vendor['vendor_id'] }}</small>
                                    </td>
                                    <td class="text-end">{{ number_format($vendor['gross'], 2) }}</td>
                                    <td class="text-end">{{ number_format($vendor['commission'], 2) }}</td>
                                    <td class="text-end">{{ number_format($vendor['net'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">{{ __('No data.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Top Products') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th class="text-end">{{ __('Qty') }}</th>
                                <th class="text-end">{{ __('Revenue') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($report['top_products'] as $product)
                                @php
                                    /** @var \App\Models\Product|null $productModel */
                                    $productModel = \App\Models\Product::query()->find($product['product_id']);
                                    $productName = $productModel
                                        ? $productModel->getTranslation('name', app()->getLocale())
                                        : ($product['product_name'] ?? '');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $productName }}</div>
                                        <small class="text-muted">ID: {{ $product['product_id'] }}</small>
                                    </td>
                                    <td class="text-end">{{ $product['quantity'] }}</td>
                                    <td class="text-end">{{ number_format($product['revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">{{ __('No data.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Orders by Zone - Heatmap --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-geo-alt-fill text-muted me-2"></i>{{ __('Orders by Zone') }}
                        <small class="text-muted fw-normal">({{ __('Heatmap') }})</small>
                    </h5>
                    <div class="d-flex align-items-center gap-2 small">
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="rounded border" style="width:14px;height:14px;background:#e8f5e9;"></span>
                            {{ __('Low') }}
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="rounded border" style="width:14px;height:14px;background:#c0392b;"></span>
                            {{ __('High') }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 position-relative">
                    <div id="orders-zone-heatmap" style="height: 420px; width: 100%;"></div>
                    @php
                        $hasZonesWithPolygon = !empty($report['orders_by_zone']) && collect($report['orders_by_zone'])->filter(fn ($z) => is_array($z['polygon'] ?? null) && count($z['polygon']) >= 3)->isNotEmpty();
                    @endphp
                    @if(!$hasZonesWithPolygon)
                        <div class="position-absolute top-50 start-50 translate-middle text-muted text-center">
                            <i class="bi bi-map fs-1"></i>
                            <p class="mb-0 mt-2">{{ __('No zones defined, or zones have no polygon. Add zones in admin to see the heatmap.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Daily Paid Sales') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($report['daily_sales'] as $row)
                                <tr>
                                    <td>{{ $row['date'] }}</td>
                                    <td class="text-end">{{ number_format($row['total'], 2) }} {{ setting('currency', 'EGP') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">{{ __('No data.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @if(!empty($report['orders_by_zone']) && collect($report['orders_by_zone'])->filter(fn ($z) => is_array($z['polygon'] ?? null) && count($z['polygon']) >= 3)->isNotEmpty())
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    @endif
@endpush

@push('scripts')
    @if(!empty($report['orders_by_zone']) && collect($report['orders_by_zone'])->filter(fn ($z) => is_array($z['polygon'] ?? null) && count($z['polygon']) >= 3)->isNotEmpty())
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var data = @json($report['orders_by_zone'] ?? []);
        var zonesWithPolygon = data.filter(function(z) { return z.polygon && z.polygon.length >= 3; });
        if (zonesWithPolygon.length === 0) return;

        var maxCount = Math.max(1, Math.max.apply(null, zonesWithPolygon.map(function(z) { return z.orders_count || 0; })));

        function colorForCount(count) {
            if (maxCount <= 0) return '#e8f5e9';
            var t = count / maxCount;
            var r = Math.round(232 - (232 - 192) * t);
            var g = Math.round(245 - (245 - 57) * t);
            var b = Math.round(233 - (233 - 43) * t);
            return 'rgb(' + r + ',' + g + ',' + b + ')';
        }

        var map = L.map('orders-zone-heatmap').setView([30.0444, 31.2357], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

        zonesWithPolygon.forEach(function(z) {
            var latlngs = z.polygon.map(function(p) { return [p.lat ?? p[0], p.lng ?? p[1]]; });
            var count = z.orders_count || 0;
            var color = colorForCount(count);
            var layer = L.polygon(latlngs, {
                color: '#333',
                weight: 1,
                fillColor: color,
                fillOpacity: 0.6
            }).addTo(map);
            layer.bindTooltip(z.zone_name + ': ' + count + ' {{ __('orders') }}', { permanent: false, direction: 'top' });
        });
    });
    </script>
    @endif
@endpush

@push('modals')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel">
                <i class="bi bi-funnel me-2"></i>{{ __('Filter Reports') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" id="filterForm">
                <div class="mb-3">
                    <label class="form-label">{{ __('From Date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('To Date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>{{ __('Apply') }}</button>
                </div>
            </form>
        </div>
    </div>
@endpush

