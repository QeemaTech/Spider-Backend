<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Http\Resources\ProductResource;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $branches = Branch::query()
            ->where('vendor_id', $vendor->id)
            ->with('vendor')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => BranchResource::collection($branches),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $branch = Branch::query()
            ->where('vendor_id', $vendor->id)
            ->with('vendor')
            ->find($id);

        if (! $branch) {
            return response()->json([
                'success' => false,
                'message' => __('Branch not found.'),
            ], 404);
        }

        $perPage = (int) $request->get('per_page', 15);

        $branchProducts = Product::query()
            ->where('vendor_id', $vendor->id)
            ->where(function ($query) use ($branch) {
                $query->whereHas('branchProductStocks', function ($stockQuery) use ($branch) {
                    $stockQuery->where('branch_id', $branch->id);
                })->orWhereHas('variants.branchVariantStocks', function ($stockQuery) use ($branch) {
                    $stockQuery->where('branch_id', $branch->id);
                });
            })
            ->with([
                'vendor',
                'categories',
                'images',
                'ratings',
                'branchProductStocks' => fn ($q) => $q->where('branch_id', $branch->id),
                'variants.branchVariantStocks' => fn ($q) => $q->where('branch_id', $branch->id),
            ])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'branch' => new BranchResource($branch),
                'products' => ProductResource::collection($branchProducts),
            ],
            'meta' => [
                'current_page' => $branchProducts->currentPage(),
                'last_page' => $branchProducts->lastPage(),
                'per_page' => $branchProducts->perPage(),
                'total' => $branchProducts->total(),
            ],
        ]);
    }
}

