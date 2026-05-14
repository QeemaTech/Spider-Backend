@if($deliveries->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Vehicle') }}</th>
                    <th>{{ __('Linked user') }}</th>
                    <th>{{ __('Zones') }}</th>
                    <th>{{ __('Wallet') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end" style="width: 180px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveries as $delivery)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($delivery->vehicle_type) }}</span>
                            <strong>{{ $delivery->vehicle_number }}</strong>
                            <span class="text-muted">({{ $delivery->vehicle_color }})</span>
                        </td>
                        <td>
                            @if($delivery->user)
                                <span>{{ $delivery->user->name }}</span>
                                <br><small class="text-muted">{{ $delivery->user->email }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($delivery->zones->count() > 0)
                                <span class="badge bg-info">{{ $delivery->zones->count() }} {{ __('zones') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ number_format((float) $delivery->wallet, 2) }} {{ setting('currency', 'EGP') }}</td>
                        <td>
                            @if($delivery->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                {{-- <a href="{{ route('admin.deliveries.show', $delivery) }}#assign-shift" class="btn btn-sm btn-outline-success" title="{{ __('Assign shift') }}"><i class="bi bi-calendar-plus"></i></a> --}}
                                <a href="{{ route('admin.deliveries.edit', $delivery) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-delivery-btn" data-id="{{ $delivery->id }}" data-label="{{ e($delivery->vehicle_number) }}" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deliveries->links() }}</div>
@else
    <div class="text-center py-5">
        <i class="bi bi-truck fs-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No deliveries found.') }}</p>
        <a href="{{ route('admin.deliveries.create') }}" class="btn btn-primary mt-2">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Add Your First Delivery') }}
        </a>
    </div>
@endif
