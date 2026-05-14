@extends('layouts.app')

@php
    $page = 'delivery-requests';
@endphp

@section('title', __('Delivery Requests'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Delivery Requests') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Delivery Requests') }}</h1>
                <p class="text-muted mb-0">{{ __('Review and accept or reject user requests to become a delivery person') }}</p>
            </div>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
            </button>
        </div>

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

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Requests') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Applicant') }}</th>
                                <th>{{ __('Vehicle') }}</th>
                                <th>{{ __('Message') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Requested At') }}</th>
                                <th>{{ __('Reviewed By') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                                <tr>
                                    <td>{{ $req->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $req->name ?? $req->user?->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $req->phone }}</small>
                                        @if($req->email)
                                            <br><small class="text-muted">{{ $req->email }}</small>
                                        @endif
                                        @if($req->user_id && $req->user)
                                            <br><a href="{{ route('admin.customers.show', $req->user) }}" class="small">{{ __('View user') }}</a>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($req->vehicle_type) }}</span>
                                        <strong>{{ $req->vehicle_number }}</strong>
                                        <span class="text-muted">({{ $req->vehicle_color }})</span>
                                    </td>
                                    <td>
                                        @if($req->message)
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $req->message }}">{{ $req->message }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                        @elseif($req->status === 'accepted')
                                            <span class="badge bg-success">{{ __('Accepted') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @if($req->rejection_reason)
                                                <br><small class="text-muted" title="{{ $req->rejection_reason }}">{{ Str::limit($req->rejection_reason, 30) }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $req->created_at->format('Y-m-d H:i') }}</small></td>
                                    <td>
                                        @if($req->reviewer)
                                            <span class="badge bg-secondary">{{ $req->reviewer->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($req->status === 'pending')
                                            <form method="POST" action="{{ route('admin.delivery-requests.approve', $req) }}" class="d-inline delivery-request-approve-form">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-success delivery-request-approve-btn">
                                                    <i class="bi bi-check2 me-1"></i>{{ __('Accept') }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger delivery-request-reject-btn" data-bs-toggle="modal" data-bs-target="#rejectModal" data-request-id="{{ $req->id }}" data-request-url="{{ route('admin.delivery-requests.reject', $req) }}">
                                                <i class="bi bi-x-lg me-1"></i>{{ __('Reject') }}
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">{{ __('No delivery requests found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rejectForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">{{ __('Reject delivery request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">{{ __('Reason (optional)') }}</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="{{ __('Optionally provide a reason to show the user') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel">
                <i class="bi bi-funnel me-2"></i>{{ __('Filter delivery requests') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('admin.delivery-requests.index') }}">
                <div class="mb-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="accepted" {{ ($filters['status'] ?? '') === 'accepted' ? 'selected' : '' }}>{{ __('Accepted') }}</option>
                        <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.delivery-requests.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>{{ __('Apply') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveBtns = document.querySelectorAll('.delivery-request-approve-btn');
    approveBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (!form) return;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "{{ __('Accept this request?') }}",
                    text: "{{ __('A delivery profile will be created for this user.') }}",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Yes, accept') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                form.submit();
            }
        });
    });

    const rejectModal = document.getElementById('rejectModal');
    const rejectForm = document.getElementById('rejectForm');
    if (rejectModal && rejectForm) {
        rejectModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            if (btn && btn.dataset.requestUrl) {
                rejectForm.action = btn.dataset.requestUrl;
                rejectForm.querySelector('#rejection_reason').value = '';
            }
        });
    }
});
</script>
@endpush
