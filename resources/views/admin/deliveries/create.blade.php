@extends('layouts.app')

@php
    $page = 'deliveries';
@endphp

@section('title', __('Add Delivery'))

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
                        <li class="breadcrumb-item active">{{ __('Add Delivery') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Add Delivery') }}</h1>
                <p class="text-muted mb-0">{{ __('Register a new delivery driver and assign zones') }}</p>
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
                        <form action="{{ route('admin.deliveries.store') }}" method="POST">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="vehicle_type" class="form-label">{{ __('Vehicle type') }} *</label>
                                    <select class="form-select @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type" required>
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="motorcycle" {{ old('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>{{ __('Motorcycle') }}</option>
                                        <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>{{ __('Car') }}</option>
                                        <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>{{ __('Van') }}</option>
                                    </select>
                                    @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="vehicle_number" class="form-label">{{ __('Vehicle number') }} *</label>
                                    <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number') }}" required maxlength="50" placeholder="ABC-1234">
                                    @error('vehicle_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="vehicle_color" class="form-label">{{ __('Vehicle color') }} *</label>
                                    <input type="text" class="form-control @error('vehicle_color') is-invalid @enderror" id="vehicle_color" name="vehicle_color" value="{{ old('vehicle_color') }}" required maxlength="50">
                                    @error('vehicle_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">{{ __('Login account for this delivery') }}</label>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="user_type" id="user_type_new" value="new" {{ old('user_type', 'new') === 'new' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user_type_new">{{ __('Create new user') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="user_type" id="user_type_existing" value="existing" {{ old('user_type') === 'existing' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user_type_existing">{{ __('Link to existing user') }}</label>
                                    </div>
                                </div>

                                <div id="new-user-fields" class="border rounded p-3 mb-2">
                                    <p class="text-muted small mb-3">{{ __('Create a new user account so the delivery can log in and see work shifts.') }}</p>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label for="new_user_name" class="form-label">{{ __('Name') }} *</label>
                                            <input type="text" class="form-control @error('new_user_name') is-invalid @enderror" id="new_user_name" name="new_user_name" value="{{ old('new_user_name') }}" maxlength="255">
                                            @error('new_user_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="new_user_email" class="form-label">{{ __('Email') }} *</label>
                                            <input type="email" class="form-control @error('new_user_email') is-invalid @enderror" id="new_user_email" name="new_user_email" value="{{ old('new_user_email') }}">
                                            @error('new_user_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="new_user_phone" class="form-label">{{ __('Phone') }}</label>
                                            <input type="text" class="form-control @error('new_user_phone') is-invalid @enderror" id="new_user_phone" name="new_user_phone" value="{{ old('new_user_phone') }}">
                                            @error('new_user_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="new_user_password" class="form-label">{{ __('Password') }} *</label>
                                            <input type="password" class="form-control @error('new_user_password') is-invalid @enderror" id="new_user_password" name="new_user_password" autocomplete="new-password">
                                            @error('new_user_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="new_user_password_confirmation" class="form-label">{{ __('Confirm password') }} *</label>
                                            <input type="password" class="form-control" id="new_user_password_confirmation" name="new_user_password_confirmation" autocomplete="new-password">
                                        </div>
                                    </div>
                                </div>

                                <div id="existing-user-field" class="d-none">
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                        <option value="">{{ __('Select user') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">{{ __('Zones this delivery can cover') }}</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    @forelse($zones as $zone)
                                        <div class="mt-2">
                                            <input class="form-check-input" type="checkbox" name="zone_ids[]" value="{{ $zone->id }}" id="zone_{{ $zone->id }}" {{ in_array($zone->id, old('zone_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="zone_{{ $zone->id }}">{{ $zone->name }}</label>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">{{ __('No zones defined yet.') }}</p>
                                    @endforelse
                                </div>
                                @error('zone_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('Create Delivery') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var userTypeNew = document.getElementById('user_type_new');
    var userTypeExisting = document.getElementById('user_type_existing');
    var newUserFields = document.getElementById('new-user-fields');
    var existingUserField = document.getElementById('existing-user-field');
    var newUserName = document.getElementById('new_user_name');
    var newUserEmail = document.getElementById('new_user_email');
    var newUserPassword = document.getElementById('new_user_password');

    function toggleUserFields() {
        if (userTypeNew.checked) {
            newUserFields.classList.remove('d-none');
            existingUserField.classList.add('d-none');
            var sel = existingUserField.querySelector('select');
            if (sel) sel.removeAttribute('required');
            newUserName.setAttribute('required', 'required');
            newUserEmail.setAttribute('required', 'required');
            newUserPassword.setAttribute('required', 'required');
        } else {
            newUserFields.classList.add('d-none');
            existingUserField.classList.remove('d-none');
            newUserName.removeAttribute('required');
            newUserEmail.removeAttribute('required');
            newUserPassword.removeAttribute('required');
            var sel = existingUserField.querySelector('select');
            if (sel) sel.setAttribute('required', 'required');
        }
    }

    userTypeNew.addEventListener('change', toggleUserFields);
    userTypeExisting.addEventListener('change', toggleUserFields);
    toggleUserFields();
});
</script>
@endpush
