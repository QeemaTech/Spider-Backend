@extends('layouts.app')

@php
    $page = 'shifts';
@endphp

@section('title', __('Shift') . ' – ' . $shift->date->format('M d, Y'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.shifts.index') }}">{{ __('Work Shifts') }}</a></li>
                        <li class="breadcrumb-item active">{{ $shift->date->format('M d, Y') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ $shift->name ?? __('Shift') }} – {{ $shift->date->format('l, M d, Y') }}</h1>
                <p class="text-muted mb-0">
                    {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                    · {{ __('Capacity') }}: {{ $shift->deliveries_count ?? $shift->deliveries->count() }}/{{ $shift->capacity }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.shifts.edit', $shift) }}" class="btn btn-primary"><i class="bi bi-pencil me-2"></i>{{ __('Edit') }}</a>
                <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Deliveries in this shift') }}</h5></div>
            <div class="card-body">
                @if($shift->deliveries->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($shift->deliveries as $delivery)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $delivery->vehicle_type }} – {{ $delivery->vehicle_number }} ({{ $delivery->vehicle_color }})</span>
                                @if($delivery->user)
                                    <small class="text-muted">{{ $delivery->user->name ?? $delivery->user->email }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">{{ __('No deliveries assigned yet.') }}</p>
                @endif
            </div>
        </div>

    </div>

@endsection
