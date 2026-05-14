@extends('layouts.app')

@php
    $page = 'shifts';
@endphp

@section('title', __('Edit Shift'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.shifts.index') }}">{{ __('Work Shifts') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Shift') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Edit Shift') }}</h1>
            </div>
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}</a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">{{ __('Shift details') }}</h5></div>
                    <div class="card-body">
                        <form action="{{ route('admin.shifts.update', $shift) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <label for="date" class="form-label">{{ __('Date') }} *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $shift->date->format('Y-m-d')) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $shift->name) }}" maxlength="100">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="start_time" class="form-label">{{ __('Start time') }} *</label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i')) }}" required>
                                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="end_time" class="form-label">{{ __('End time') }} *</label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i')) }}" required>
                                    @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            @php $taken = $shift->deliveries()->count(); @endphp
                            <div class="mb-4">
                                <label for="capacity" class="form-label">{{ __('Max deliveries in this shift') }} *</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $shift->capacity) }}" min="{{ $taken }}" max="100" required>
                                @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">{{ __('Already assigned: :count. Capacity cannot be less than that.', ['count' => $taken]) }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>{{ __('Update Shift') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
