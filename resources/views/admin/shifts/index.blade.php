@extends('layouts.app')

@php
    $page = 'shifts';
@endphp

@section('title', __('Work Shifts'))

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
                        <li class="breadcrumb-item active">{{ __('Work Shifts') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Work Shifts') }}</h1>
                <p class="text-muted mb-0">{{ __('Weekly shifts and delivery capacity') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('Add Shift') }}</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.shifts.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">{{ __('From date') }}</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">{{ __('To date') }}</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2"><i class="bi bi-search me-1"></i>{{ __('Filter') }}</button>
                        <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @include('admin.shifts.partials.table', ['shifts' => $shifts])
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-shift-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const shiftId = this.getAttribute('data-id');
            const shiftLabel = this.getAttribute('data-label');
            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                text: '{{ __('You are about to delete shift') }}: ' + shiftLabel + '. {{ __('This action cannot be undone!') }}',
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
                    form.action = '{{ route('admin.shifts.destroy', ':id') }}'.replace(':id', shiftId);
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
