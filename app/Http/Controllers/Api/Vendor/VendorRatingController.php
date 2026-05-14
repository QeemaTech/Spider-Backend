<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorRatingResource;
use App\Models\VendorRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorRatingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_visible' => ['nullable', 'boolean'],
            'min_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $perPage = (int) $request->get('per_page', 20);

        $query = VendorRating::query()
            ->with(['vendor', 'user'])
            ->where('vendor_id', $vendor->id);

        if ($request->filled('is_visible')) {
            $query->where('is_visible', (bool) $request->boolean('is_visible'));
        }

        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (int) $request->get('min_rating'));
        }

        $ratings = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => VendorRatingResource::collection($ratings),
            'meta' => [
                'current_page' => $ratings->currentPage(),
                'last_page' => $ratings->lastPage(),
                'per_page' => $ratings->perPage(),
                'total' => $ratings->total(),
                'average_rating' => round((float) VendorRating::query()->where('vendor_id', $vendor->id)->avg('rating'), 2),
            ],
        ]);
    }
}

