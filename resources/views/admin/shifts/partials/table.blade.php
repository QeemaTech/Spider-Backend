@if($shifts->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Capacity') }}</th>
                    <th>{{ __('Taken') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end" style="width: 150px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shifts as $shift)
                    <tr>
                        <td>{{ $shift->date->format('D, M d, Y') }}</td>
                        <td>{{ $shift->name ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</td>
                        <td><span class="badge bg-secondary">{{ $shift->capacity }}</span></td>
                        <td><span class="badge bg-info">{{ $shift->deliveries_count ?? $shift->deliveries->count() }}/{{ $shift->capacity }}</span></td>
                        <td>
                            @if($shift->isFull())
                                <span class="badge bg-warning">{{ __('Full') }}</span>
                            @else
                                <span class="badge bg-success">{{ __('Available') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.shifts.show', $shift) }}" class="btn btn-sm btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.shifts.edit', $shift) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-shift-btn" data-id="{{ $shift->id }}" data-label="{{ $shift->date->format('Y-m-d') }} {{ $shift->start_time }}" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $shifts->links() }}</div>
@else
    <div class="text-center py-5">
        <i class="bi bi-calendar-week fs-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No shifts found.') }}</p>
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary mt-2"><i class="bi bi-plus-lg me-2"></i>{{ __('Add Your First Shift') }}</a>
    </div>
@endif
