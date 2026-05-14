@extends('layouts.app')

@php
    $page = 'delivery-shifts';
@endphp

@section('title', __('My Shifts'))

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

        <div class="mb-4">
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">{{ __('Work Shifts') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ __('Work Shifts') }}</h1>
            <p class="text-muted mb-0">{{ __('View available shifts and the ones you have taken') }}</p>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>{{ __('My shifts') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($myShifts->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($myShifts as $shift)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $shift->date->format('D, M d') }}</strong>
                                            {{ $shift->name ? " – {$shift->name}" : '' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                            </small>
                                        </div>
                                        <form action="{{ route('delivery.shifts.leave', $shift) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Leave this shift?') }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Leave') }}</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">{{ __('You have not taken any shifts yet.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-calendar-plus me-2"></i>{{ __('Available shifts') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($availableShifts->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($availableShifts as $shift)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $shift->date->format('D, M d') }}</strong>
                                            {{ $shift->name ? " – {$shift->name}" : '' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                                · {{ $shift->availableSlots() }} {{ __('slots left') }}
                                            </small>
                                        </div>
                                        <form action="{{ route('delivery.shifts.take', $shift) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Take shift') }}</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">{{ __('No available shifts at the moment.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
