<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * List available shifts (not full, upcoming) and shifts the delivery has taken.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $delivery = $request->attributes->get('delivery');
        if (! $delivery) {
            return redirect()->route('login');
        }

        $availableShifts = Shift::upcoming()
            ->withCount('deliveries')
            ->whereDoesntHave('deliveries', fn ($q) => $q->where('delivery_id', $delivery->id))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->filter(fn (Shift $s) => ! $s->isFull());

        $myShifts = $delivery->shifts()
            ->upcoming()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('delivery.shifts.index', compact('availableShifts', 'myShifts'));
    }

    /**
     * Delivery takes a shift (assigns themselves to it).
     */
    public function take(Request $request, Shift $shift): RedirectResponse
    {
        $delivery = $request->attributes->get('delivery');
        if (! $delivery) {
            return redirect()->route('login');
        }

        if ($shift->isFull()) {
            return redirect()->route('delivery.shifts.index')
                ->with('error', __('This shift is already full.'));
        }

        if (! $shift->isUpcoming()) {
            return redirect()->route('delivery.shifts.index')
                ->with('error', __('You can only take upcoming shifts.'));
        }

        if ($delivery->shifts()->where('shift_id', $shift->id)->exists()) {
            return redirect()->route('delivery.shifts.index')
                ->with('error', __('You have already taken this shift.'));
        }

        $delivery->shifts()->attach($shift->id);

        return redirect()->route('delivery.shifts.index')
            ->with('success', __('You have taken the shift successfully.'));
    }

    /**
     * Delivery leaves a shift they had taken.
     */
    public function leave(Request $request, Shift $shift): RedirectResponse
    {
        $delivery = $request->attributes->get('delivery');
        if (! $delivery) {
            return redirect()->route('login');
        }

        $delivery->shifts()->detach($shift->id);

        return redirect()->route('delivery.shifts.index')
            ->with('success', __('You have left the shift.'));
    }
}
