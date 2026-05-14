@extends('layouts.landing')

@section('title', __('Become a Delivery Person'))

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <p class="text-muted mb-3">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none">{{ __('Home') }}</a>
                        <span class="mx-2">/</span>
                        <span>{{ __('Become a Delivery Person') }}</span>
                    </p>

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

                <div class="card">
                    <div class="card-header">
                        <h1 class="h4 mb-0">
                            <i class="bi bi-truck me-2 text-primary"></i>{{ __('Become a Delivery Person') }}
                        </h1>
                        <p class="text-muted mb-0 mt-2">{{ __('Submit your details to apply. We will review your request and get back to you.') }}</p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('become-delivery.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="name" class="form-label">{{ __('Name') }} *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="255">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="phone" class="form-label">{{ __('Phone') }} *</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" required maxlength="50">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()?->email) }}" maxlength="255">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
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
                                    <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number') }}" required maxlength="50" placeholder="e.g. ABC-1234">
                                    @error('vehicle_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="vehicle_color" class="form-label">{{ __('Vehicle color') }} *</label>
                                    <input type="text" class="form-control @error('vehicle_color') is-invalid @enderror" id="vehicle_color" name="vehicle_color" value="{{ old('vehicle_color') }}" required maxlength="50">
                                    @error('vehicle_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">{{ __('Message (optional)') }}</label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="3" maxlength="1000" placeholder="{{ __('Any additional information for the admin') }}">{{ old('message') }}</textarea>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>{{ __('Submit Request') }}
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
