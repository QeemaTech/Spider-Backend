@extends('layouts.app')

@php
    $page = 'settings';
@endphp

@section('title', __('Settings'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <!-- Success/Error Messages -->
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

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Settings') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your vendor settings and preferences') }}</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-12">
                <!-- Settings Form -->
                <form action="{{ route('vendor.settings.update') }}" method="POST" id="settingsForm">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">{{ __('Branch Management') }}</h5>

                            <!-- Allow Branch Users to Edit Stock -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="allow_branch_user_to_edit_stock" value="0">
                                    <input class="form-check-input" type="checkbox" id="allow_branch_user_to_edit_stock" name="allow_branch_user_to_edit_stock" value="1" {{ (isset($settings['allow_branch_user_to_edit_stock']) && $settings['allow_branch_user_to_edit_stock']->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_branch_user_to_edit_stock">
                                        <strong>{{ $defaultSettings['branch']['allow_branch_user_to_edit_stock']['label'] }}</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['branch']['allow_branch_user_to_edit_stock']['description'] }}</small>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>{{ __('Save Settings') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-12">
                <!-- Working Hours / Timeplan -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-muted"></i>
                            {{ __('Working Hours (Timeplan)') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $days = [
                                0 => __('Sunday'),
                                1 => __('Monday'),
                                2 => __('Tuesday'),
                                3 => __('Wednesday'),
                                4 => __('Thursday'),
                                5 => __('Friday'),
                                6 => __('Saturday'),
                            ];
                        @endphp

                        @if($vendor->timeSlots->count())
                            <div class="table-responsive mb-3">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Day') }}</th>
                                            <th>{{ __('Opens at') }}</th>
                                            <th>{{ __('Closes at') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vendor->timeSlots as $slot)
                                            <tr>
                                                <form action="{{ route('vendor.time-slots.update', $slot) }}" method="POST" class="align-middle">
                                                    @csrf
                                                    @method('PUT')
                                                    <td style="width: 140px;">
                                                        <select name="day_of_week" class="form-select form-select-sm">
                                                            @foreach($days as $index => $label)
                                                                <option value="{{ $index }}" {{ $slot->day_of_week === $index ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td style="width: 120px;">
                                                        <input type="time" name="opens_at" class="form-control form-control-sm" value="{{ \Illuminate\Support\Str::of($slot->opens_at)->substr(0,5) }}">
                                                    </td>
                                                    <td style="width: 120px;">
                                                        <input type="time" name="closes_at" class="form-control form-control-sm" value="{{ \Illuminate\Support\Str::of($slot->closes_at)->substr(0,5) }}">
                                                    </td>
                                                    <td style="width: 130px;">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $slot->is_active ? 'checked' : '' }}>
                                                            <span class="ms-2 small">{{ $slot->is_active ? __('Active') : __('Inactive') }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end" style="width: 190px;">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-save me-1"></i>{{ __('Save') }}
                                                        </button>
                                                </form>
                                                <form action="{{ route('vendor.time-slots.destroy', $slot) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('{{ __('Delete this time slot?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                                        <i class="bi bi-trash me-1"></i>{{ __('Delete') }}
                                                    </button>
                                                </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">{{ __('No working hours defined yet. Use the form below to add time slots.') }}</p>
                        @endif

                        <h6 class="mb-2">{{ __('Add New Time Slot') }}</h6>
                        <form action="{{ route('vendor.time-slots.store') }}" method="POST" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-4">
                                <label for="day_of_week" class="form-label small text-muted">{{ __('Day') }}</label>
                                <select name="day_of_week" id="day_of_week" class="form-select form-select-sm" required>
                                    @foreach($days as $index => $label)
                                        <option value="{{ $index }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="opens_at" class="form-label small text-muted">{{ __('Opens at') }}</label>
                                <input type="time" name="opens_at" id="opens_at" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label for="closes_at" class="form-label small text-muted">{{ __('Closes at') }}</label>
                                <input type="time" name="closes_at" id="closes_at" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check mt-4 pt-1">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active_slot" value="1" checked>
                                    <label class="form-check-label small" for="is_active_slot">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>{{ __('Add') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
