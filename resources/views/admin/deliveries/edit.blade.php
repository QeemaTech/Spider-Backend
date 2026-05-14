@extends('layouts.app')

@php
    $page = 'deliveries';
@endphp

@section('title', __('Edit Delivery'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.deliveries.index') }}">{{ __('Deliveries') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Delivery') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Edit Delivery') }}</h1>
                <p class="text-muted mb-0">{{ $delivery->vehicle_number }}</p>
            </div>
            <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Delivery information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.deliveries.update', $delivery) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="vehicle_type" class="form-label">{{ __('Vehicle type') }} *</label>
                                    <select class="form-select @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type" required>
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="motorcycle" {{ old('vehicle_type', $delivery->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>{{ __('Motorcycle') }}</option>
                                        <option value="car" {{ old('vehicle_type', $delivery->vehicle_type) === 'car' ? 'selected' : '' }}>{{ __('Car') }}</option>
                                        <option value="van" {{ old('vehicle_type', $delivery->vehicle_type) === 'van' ? 'selected' : '' }}>{{ __('Van') }}</option>
                                    </select>
                                    @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="vehicle_number" class="form-label">{{ __('Vehicle number') }} *</label>
                                    <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number', $delivery->vehicle_number) }}" required maxlength="50">
                                    @error('vehicle_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="vehicle_color" class="form-label">{{ __('Vehicle color') }} *</label>
                                    <input type="text" class="form-control @error('vehicle_color') is-invalid @enderror" id="vehicle_color" name="vehicle_color" value="{{ old('vehicle_color', $delivery->vehicle_color) }}" required maxlength="50">
                                    @error('vehicle_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="user_id" class="form-label">{{ __('Link to user') }}</label>
                                <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $delivery->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="wallet" class="form-label">{{ __('Wallet balance') }}</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('wallet') is-invalid @enderror" id="wallet" name="wallet" value="{{ old('wallet', $delivery->wallet) }}">
                                @error('wallet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">{{ __('COD amounts collected by this delivery') }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">{{ __('Zones this delivery can cover') }}</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    @php $deliveryZoneIds = old('zone_ids', $delivery->zones->pluck('id')->toArray()); @endphp
                                    @forelse($zones as $zone)
                                        <div class="mt-2">
                                            <input class="form-check-input" type="checkbox" name="zone_ids[]" value="{{ $zone->id }}" id="zone_{{ $zone->id }}" {{ in_array($zone->id, $deliveryZoneIds) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="zone_{{ $zone->id }}">{{ $zone->name }}</label>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">{{ __('No zones defined yet.') }}</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $delivery->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Update Delivery') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
