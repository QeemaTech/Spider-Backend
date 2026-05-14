@extends('layouts.app')

@php
    $page = 'deliveries';
@endphp

@section('title', __('Delivery') . ' – ' . $delivery->vehicle_number)

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

        {{-- Page header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.deliveries.index') }}">{{ __('Deliveries') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $delivery->vehicle_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h1 class="h3 mb-0">{{ ucfirst($delivery->vehicle_type) }} · {{ $delivery->vehicle_number }}</h1>
                    @if($delivery->is_active)
                        <span class="badge bg-success">{{ __('Active') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                    @endif
                    <span class="text-muted">· {{ $delivery->vehicle_color }}</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="#assign-shift" class="btn btn-success">
                    <i class="bi bi-calendar-plus me-1"></i>{{ __('Assign shift') }}
                </a>
                <a href="{{ route('admin.deliveries.edit', $delivery) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-wallet2 text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">{{ __('Wallet') }}</p>
                                <p class="fw-bold mb-0 fs-5">{{ number_format((float) $delivery->wallet, 2) }} <span class="fs-6 fw-normal text-muted">{{ setting('currency', 'EGP') }}</span></p>
                            </div>
                        </div>
                        <form action="{{ route('admin.deliveries.update-wallet', $delivery) }}" method="POST" class="d-flex gap-2 align-items-end">
                            @csrf
                            @method('PUT')
                            <div class="flex-grow-1">
                                <label for="wallet-edit" class="form-label visually-hidden">{{ __('New wallet value') }}</label>
                                <input type="number" step="0.01" min="0" name="wallet" id="wallet-edit" class="form-control form-control-sm" value="{{ old('wallet', (float) $delivery->wallet) }}" placeholder="0.00" required>
                                @error('wallet')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg"></i> {{ __('Update') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="bi bi-geo-alt text-info fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">{{ __('Zones') }}</p>
                            <p class="fw-bold mb-0 fs-5">{{ $delivery->zones->count() }} <span class="fs-6 fw-normal text-muted">{{ __('covered') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-calendar-week text-success fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">{{ __('Upcoming shifts') }}</p>
                            <p class="fw-bold mb-0 fs-5">{{ $delivery->shifts->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                            <i class="bi bi-person text-secondary fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">{{ __('Linked user') }}</p>
                            <p class="fw-bold mb-0 text-truncate" style="max-width: 140px;" title="{{ $delivery->user?->name ?? __('None') }}">
                                {{ $delivery->user ? $delivery->user->name : __('Not linked') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left column: Details + Zones --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-muted"></i>
                            {{ __('Details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-4 col-md-3 text-muted fw-normal">{{ __('Vehicle type') }}</dt>
                            <dd class="col-8 col-md-9 mb-2"><span class="badge bg-secondary">{{ ucfirst($delivery->vehicle_type) }}</span></dd>

                            <dt class="col-4 col-md-3 text-muted fw-normal">{{ __('Vehicle number') }}</dt>
                            <dd class="col-8 col-md-9 mb-2"><strong>{{ $delivery->vehicle_number }}</strong></dd>

                            <dt class="col-4 col-md-3 text-muted fw-normal">{{ __('Vehicle color') }}</dt>
                            <dd class="col-8 col-md-9 mb-2">{{ $delivery->vehicle_color }}</dd>

                            <dt class="col-4 col-md-3 text-muted fw-normal">{{ __('Linked user') }}</dt>
                            <dd class="col-8 col-md-9 mb-0">
                                @if($delivery->user)
                                    <div>
                                        <span class="fw-medium">{{ $delivery->user->name }}</span>
                                        <br><small class="text-muted">{{ $delivery->user->email }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">{{ __('Not linked') }}</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-muted"></i>
                            {{ __('Zones covered') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($delivery->zones->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($delivery->zones as $zone)
                                    <span class="badge bg-light text-dark border">{{ $zone->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('No zones assigned.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-wallet2 text-muted"></i>
                            {{ __('Wallet history') }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($delivery->walletTransactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">{{ __('Date') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="text-end">{{ __('Amount') }}</th>
                                            <th class="text-end pe-3">{{ __('Balance after') }}</th>
                                            <th>{{ __('Notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($delivery->walletTransactions as $tx)
                                            <tr>
                                                <td class="ps-3"><small>{{ $tx->created_at->format('M d, Y H:i') }}</small></td>
                                                <td>
                                                    @if($tx->type === 'delivery_completed')
                                                        <span class="badge bg-success">{{ __('Delivery completed') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Admin adjustment') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end {{ (float) $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ (float) $tx->amount >= 0 ? '+' : '' }}{{ number_format((float) $tx->amount, 2) }} {{ setting('currency', 'EGP') }}
                                                </td>
                                                <td class="text-end pe-3">{{ number_format((float) $tx->balance_after, 2) }} {{ setting('currency', 'EGP') }}</td>
                                                <td><small class="text-muted">{{ $tx->notes ?? '–' }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0 text-center py-4">{{ __('No wallet transactions yet.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right column: Assign shift + Upcoming shifts --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4" id="assign-shift">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-plus text-muted"></i>
                            {{ __('Assign to shift') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($availableShifts->count() > 0)
                            <form action="{{ route('admin.deliveries.assign-shift', $delivery) }}" method="POST" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-12 col-md">
                                    <label for="shift_id" class="form-label small text-muted">{{ __('Select shift') }}</label>
                                    <select class="form-select" id="shift_id" name="shift_id" required>
                                        <option value="">{{ __('Choose a shift...') }}</option>
                                        @foreach($availableShifts as $s)
                                            <option value="{{ $s->id }}">
                                                {{ $s->date->format('D, M d') }}{{ $s->name ? " – {$s->name}" : '' }}
                                                · {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}
                                                · {{ $s->availableSlots() }} {{ __('slots') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-auto">
                                    <button type="submit" class="btn btn-success w-100 w-md-auto">
                                        <i class="bi bi-plus-lg me-1"></i>{{ __('Assign') }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="text-muted mb-0 small">{{ __('No available shifts. Add upcoming shifts with free capacity in Work Shifts.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-check text-muted"></i>
                            {{ __('Upcoming shifts') }}
                        </h5>
                        @if($delivery->shifts->count() > 0)
                            <span class="badge bg-success">{{ $delivery->shifts->count() }}</span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if($delivery->shifts->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($delivery->shifts as $shift)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <span class="fw-medium">{{ $shift->date->format('D, M d, Y') }}</span>
                                            @if($shift->name)
                                                <span class="text-muted">· {{ $shift->name }}</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</small>
                                        </div>
                                        <form action="{{ route('admin.deliveries.unassign-shift', [$delivery, $shift]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Remove this delivery from the shift?') }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Remove') }}</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="card-body">
                                <p class="text-muted mb-0 text-center py-3">{{ __('No shifts assigned. Use the form above to assign a shift.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
@if(session('scroll_to') === 'assign-shift')
<script>document.addEventListener('DOMContentLoaded', function() { document.getElementById('assign-shift')?.scrollIntoView({ behavior: 'smooth' }); });</script>
@endif
@endpush
