@extends('layouts.app')

@php
    $page = 'deliveries';
@endphp

@section('title', __('Deliveries'))

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Deliveries') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Deliveries') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage delivery drivers and their zones') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.deliveries.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Delivery') }}
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.deliveries.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Vehicle number, color...') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="vehicle_type" class="form-label">{{ __('Vehicle type') }}</label>
                        <select class="form-select" id="vehicle_type" name="vehicle_type">
                            <option value="">{{ __('All') }}</option>
                            <option value="motorcycle" {{ ($filters['vehicle_type'] ?? '') === 'motorcycle' ? 'selected' : '' }}>{{ __('Motorcycle') }}</option>
                            <option value="car" {{ ($filters['vehicle_type'] ?? '') === 'car' ? 'selected' : '' }}>{{ __('Car') }}</option>
                            <option value="van" {{ ($filters['vehicle_type'] ?? '') === 'van' ? 'selected' : '' }}>{{ __('Van') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="is_active" class="form-label">{{ __('Status') }}</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="">{{ __('All') }}</option>
                            <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2 d-flex">
                            <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @include('admin.deliveries.partials.table', ['deliveries' => $deliveries])
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const label = this.getAttribute('data-label');
            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                text: '{{ __('You are about to delete delivery') }}: ' + label + '. {{ __('This action cannot be undone!') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __('Yes, delete it!') }}',
                cancelButtonText: '{{ __('Cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('admin.deliveries.destroy', ':id') }}'.replace(':id', id);
                    form.innerHTML = '@csrf @method('DELETE')';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
