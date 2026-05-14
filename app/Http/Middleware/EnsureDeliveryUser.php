<?php

namespace App\Http\Middleware;

use App\Models\Delivery;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeliveryUser
{
    /**
     * Ensure the authenticated user is linked to a delivery (can access delivery area).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => __('Unauthenticated.')], 401);
            }

            return redirect()->route('login');
        }

        $delivery = Delivery::where('user_id', Auth::id())->where('is_active', true)->first();

        if (! $delivery) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have access to the delivery area.'),
                ], 403);
            }
            abort(403, __('You do not have access to the delivery area.'));
        }

        $request->attributes->set('delivery', $delivery);

        return $next($request);
    }
}
