<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * List active categories for vendor product create/update selection.
     */
    public function index(): JsonResponse
    {
        $rootCategories = Category::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->active()->orderBy('id');
            }])
            ->orderBy('id')
            ->get();

        $flatCategories = Category::query()
            ->active()
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id']);

        return response()->json([
            'success' => true,
            'data' => [
                'tree' => $rootCategories->map(function (Category $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'parent_id' => $category->parent_id,
                        'children' => $category->children->map(function (Category $child) {
                            return [
                                'id' => $child->id,
                                'name' => $child->name,
                                'parent_id' => $child->parent_id,
                            ];
                        })->values(),
                    ];
                })->values(),
                'flat' => $flatCategories->map(function (Category $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'parent_id' => $category->parent_id,
                    ];
                })->values(),
            ],
        ]);
    }
}

