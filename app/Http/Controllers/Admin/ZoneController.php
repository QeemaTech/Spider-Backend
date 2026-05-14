<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Zones\CreateRequest;
use App\Http\Requests\Admin\Zones\UpdateRequest;
use App\Models\Zone;
use App\Services\ZoneService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function __construct(
        protected ZoneService $service
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'is_active' => $request->get('is_active', ''),
        ];

        $zones = $this->service->getPaginatedZones($perPage, $filters);

        return view('admin.zones.index', compact('zones', 'filters'));
    }

    public function create(): View
    {
        return view('admin.zones.create');
    }

    public function store(CreateRequest $request): RedirectResponse
    {
        $this->service->createZone($request->validated());

        return redirect()->route('admin.zones.index')
            ->with('success', __('Zone created successfully.'));
    }

    public function show(Zone $zone): View
    {
        $zone->load('deliveries.user');

        return view('admin.zones.show', compact('zone'));
    }

    public function edit(Zone $zone): View
    {
        return view('admin.zones.edit', compact('zone'));
    }

    public function update(UpdateRequest $request, Zone $zone): RedirectResponse
    {
        $this->service->updateZone($zone, $request->validated());

        return redirect()->route('admin.zones.index')
            ->with('success', __('Zone updated successfully.'));
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $this->service->deleteZone($zone);

        return redirect()->route('admin.zones.index')
            ->with('success', __('Zone deleted successfully.'));
    }
}
