<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Products\CreateRequest;
use App\Http\Requests\Vendor\Products\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {}

    /**
     * Display a listing of the vendor's products.
     */
    public function index(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        // Check if user is a branch user
        $branch = currentBranch();

        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'featured' => $request->get('featured', ''),
            'approved' => $request->get('approved', ''),
            'type' => $request->get('type', ''),
            'vendor_id' => $vendor->id, // Force filter by vendor
            'category_id' => $request->get('category_id', ''),
            'stock' => $request->get('stock', ''),
            'branch_id' => $branch?->id, // Filter by branch if user is branch user
            'min_price' => $request->get('min_price', ''),
            'max_price' => $request->get('max_price', ''),
            'is_new' => $request->get('is_new', ''),
            'is_bookable' => $request->get('is_bookable', ''),
            'sort' => $request->get('sort', ''),
        ];

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $products = $this->service->getPaginatedProducts($perPage, $filters);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(CreateRequest $request): JsonResponse
    {
        if (! canCreateProducts()) {
            return response()->json([
                'success' => false,
                'message' => __('Only vendor owners can create products. Branch users cannot create products.'),
            ], 403);
        }

        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $canAddProducts = setting('profit_type') == 'subscription'
            ? $vendor->plan && $vendor->plan->max_products_count > $vendor->products()->active()->count()
            : true;

        if (! $canAddProducts) {
            return response()->json([
                'success' => false,
                'message' => __('You have reached the maximum number of products. Please delete or deactivate some products to add a new one.'),
            ], 422);
        }

        $canFeatureProducts = setting('profit_type') == 'subscription'
            ? (bool) ($vendor->plan?->can_feature_products ?? false)
            : true;

        if (! $canFeatureProducts && $request->boolean('is_featured')) {
            return response()->json([
                'success' => false,
                'message' => __('You are not allowed to feature products. Please subscribe to a plan that allows you to feature products.'),
            ], 422);
        }

        // Keep the same flow as dashboard store
        $request->merge(['vendor_id' => $vendor->id]);
        $product = $this->service->createProduct($request);

        return response()->json([
            'success' => true,
            'message' => __('Product created successfully.'),
            'data' => new ProductResource($product->load([
                'vendor',
                'categories',
                'images',
                'ratings',
                'variants.values.variantOption.variant',
                'variants.branchVariantStocks',
                'branchProductStocks',
            ])),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $product = Product::query()
            ->where('vendor_id', $vendor->id)
            ->with([
                'vendor',
                'categories',
                'images',
                'ratings',
                'variants.values.variantOption.variant',
                'variants.branchVariantStocks',
                'branchProductStocks',
            ])
            ->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => __('Product not found.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        if (! canCreateProducts()) {
            return response()->json([
                'success' => false,
                'message' => __('Only vendor owners can edit products. Branch users cannot edit products.'),
            ], 403);
        }

        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $product = Product::query()
            ->where('vendor_id', $vendor->id)
            ->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => __('Product not found.'),
            ], 404);
        }

        $canFeatureProducts = setting('profit_type') == 'subscription'
            ? (bool) ($vendor->plan?->can_feature_products ?? false)
            : true;
        if (! $canFeatureProducts && $request->boolean('is_featured', $product->is_featured)) {
            return response()->json([
                'success' => false,
                'message' => __('You are not allowed to feature products. Please subscribe to a plan that allows you to feature products.'),
            ], 422);
        }

        $canAddProducts = setting('profit_type') == 'subscription'
            ? $vendor->plan && $vendor->plan->max_products_count > $vendor->products()->active()->count()
            : true;
        if (! $canAddProducts && $request->boolean('is_active', $product->is_active)) {
            return response()->json([
                'success' => false,
                'message' => __('You have reached the maximum number of products. Please delete or deactivate some products to add a new one.'),
            ], 422);
        }

        $request->merge(['vendor_id' => $vendor->id]);
        $updated = $this->service->updateProduct($request, $product);

        return response()->json([
            'success' => true,
            'message' => __('Product updated successfully.'),
            'data' => new ProductResource($updated->load([
                'vendor',
                'categories',
                'images',
                'ratings',
                'variants.values.variantOption.variant',
                'variants.branchVariantStocks',
                'branchProductStocks',
            ])),
        ]);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $product = Product::query()
            ->where('vendor_id', $vendor->id)
            ->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => __('Product not found.'),
            ], 404);
        }

        $product = $this->service->toggleActive($product);

        return response()->json([
            'success' => true,
            'message' => __('Product status updated successfully.'),
            'data' => new ProductResource($product->load(['vendor', 'categories', 'images', 'ratings'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (! canCreateProducts()) {
            return response()->json([
                'success' => false,
                'message' => __('Only vendor owners can delete products. Branch users cannot delete products.'),
            ], 403);
        }

        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $product = Product::query()
            ->where('vendor_id', $vendor->id)
            ->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => __('Product not found.'),
            ], 404);
        }

        $this->service->deleteProduct($product);

        return response()->json([
            'success' => true,
            'message' => __('Product deleted successfully.'),
        ]);
    }
}
