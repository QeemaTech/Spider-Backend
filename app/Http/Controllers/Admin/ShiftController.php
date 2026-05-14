<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Shifts\CreateRequest;
use App\Http\Requests\Admin\Shifts\UpdateRequest;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftService $service
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
        ];

        $shifts = $this->service->getPaginatedShifts($perPage, $filters);

        return view('admin.shifts.index', compact('shifts', 'filters'));
    }

    public function create(): View
    {
        return view('admin.shifts.create');
    }

    public function store(CreateRequest $request): RedirectResponse
    {
        $this->service->createShift($request->validated());

        return redirect()->route('admin.shifts.index')
            ->with('success', __('Shift created successfully.'));
    }

    public function show(Shift $shift): View
    {
        $shift->loadCount('deliveries')->load('deliveries');

        return view('admin.shifts.show', compact('shift'));
    }

    public function edit(Shift $shift): View
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(UpdateRequest $request, Shift $shift): RedirectResponse
    {
        $this->service->updateShift($shift, $request->validated());

        return redirect()->route('admin.shifts.index')
            ->with('success', __('Shift updated successfully.'));
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $this->service->deleteShift($shift);

        return redirect()->route('admin.shifts.index')
            ->with('success', __('Shift deleted successfully.'));
    }
}
