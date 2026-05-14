<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use Illuminate\Http\JsonResponse;

class VariantController extends Controller
{
    /**
     * List active variants with options for vendor product create/edit.
     */
    public function index(): JsonResponse
    {
        $variants = Variant::query()
            ->where('is_active', true)
            ->with('options')
            ->orderBy('id')
            ->get()
            ->map(function (Variant $variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'is_required' => (bool) $variant->is_required,
                    'is_active' => (bool) $variant->is_active,
                    'options' => $variant->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'variant_id' => $option->variant_id,
                            'name' => $option->name,
                            'code' => $option->code,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $variants,
        ]);
    }
}

