<?php

namespace App\Http\Controllers;

use App\Http\Requests\BecomeDeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliveryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BecomeDeliveryController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (Delivery::where('user_id', $user->id)->exists()) {
                return redirect()->route('dashboard')
                    ->with('info', __('You are already registered as a delivery person.'));
            }
            if (DeliveryRequest::where('user_id', $user->id)->pending()->exists()) {
                return redirect()->route('dashboard')
                    ->with('info', __('You already have a pending request. We will notify you once it is reviewed.'));
            }
        }

        return view('become-delivery');
    }

    public function store(BecomeDeliveryRequest $request): RedirectResponse
    {
        $phone = $request->validated('phone');
        $userId = auth()->id();

        if ($userId && DeliveryRequest::where('user_id', $userId)->pending()->exists()) {
            return redirect()->route('become-delivery.create')
                ->with('info', __('You already have a pending request.'));
        }
        if (DeliveryRequest::where('phone', $phone)->pending()->exists()) {
            return redirect()->route('become-delivery.create')
                ->with('info', __('A pending request with this phone number already exists.'));
        }

        DeliveryRequest::create([
            'user_id' => $userId,
            'name' => $request->validated('name'),
            'phone' => $phone,
            'email' => $request->validated('email'),
            'vehicle_type' => $request->validated('vehicle_type'),
            'vehicle_number' => $request->validated('vehicle_number'),
            'vehicle_color' => $request->validated('vehicle_color'),
            'message' => $request->validated('message'),
            'status' => 'pending',
        ]);

        return redirect()->route('become-delivery.create')
            ->with('success', __('Your request to become a delivery person has been submitted. We will review it and notify you.'));
    }
}
