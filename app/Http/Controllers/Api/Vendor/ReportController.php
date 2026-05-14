<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorOrder;
use App\Models\VendorUser;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();
        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $rangeKey = $this->normalizeRangeKey($request);

        $request->merge(['range' => $rangeKey]);
        $request->validate([
            'range' => ['required', 'string', 'in:today,yesterday,last_7_days,last_30_days,custom'],
            'from' => ['required_if:range,custom', 'nullable', 'date'],
            'to' => ['required_if:range,custom', 'nullable', 'date', 'after_or_equal:from'],
        ]);

        [$from, $to] = $this->resolveRange($rangeKey, $request);

        $branchId = $this->resolveBranchRestriction();
        $timezone = config('app.timezone', 'UTC');

        $baseQuery = VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to])
                    ->where('payment_status', 'paid');
            });

        if ($branchId !== null) {
            $baseQuery->where('branch_id', $branchId);
        }

        $rows = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as order_date, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('order_date')
            ->get();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(string) $row->order_date] = round((float) $row->total, 2);
        }

        $bars = [];
        $cursor = $from->startOfDay();
        $end = $to->endOfDay();
        $todayDate = now()->toDateString();

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $bars[] = [
                'date' => $date,
                'label' => $cursor->format('M j'),
                'value' => $indexed[$date] ?? 0.0,
                'is_current' => $date === $todayDate,
            ];
            $cursor = $cursor->addDay();
        }

        $total = array_reduce($bars, fn ($carry, $bar) => $carry + (float) $bar['value'], 0.0);

        return response()->json([
            'success' => true,
            'data' => [
                'range' => [
                    'key' => $rangeKey,
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'timezone' => $timezone,
                ],
                'summary_chart' => [
                    'currency' => 'L.E',
                    'bars' => $bars,
                    'total' => round($total, 2),
                ],
            ],
        ]);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function resolveRange(string $rangeKey, Request $request): array
    {
        $now = CarbonImmutable::now();

        return match ($rangeKey) {
            'yesterday' => [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()],
            'last_7_days' => [$now->subDays(6)->startOfDay(), $now->endOfDay()],
            'last_30_days' => [$now->subDays(29)->startOfDay(), $now->endOfDay()],
            'custom' => [
                CarbonImmutable::parse((string) $request->get('from'))->startOfDay(),
                CarbonImmutable::parse((string) $request->get('to'))->endOfDay(),
            ],
            default => [$now->startOfDay(), $now->endOfDay()],
        };
    }

    private function resolveBranchRestriction(): ?int
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('vendor_employee')) {
            return null;
        }

        $vendorUser = VendorUser::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $vendorUser || $vendorUser->user_type !== 'branch') {
            return null;
        }

        return (int) $vendorUser->branch_id;
    }

    private function normalizeRangeKey(Request $request): string
    {
        $raw = $request->get('range')
            ?? $request->get('key')
            ?? $request->get('period')
            ?? $request->get('filter')
            ?? 'today';

        $value = strtolower(trim((string) $raw));
        $normalized = str_replace([' ', '-'], '_', $value);

        return match ($normalized) {
            'today', '1d' => 'today',
            'yesterday' => 'yesterday',
            'last_7_days', 'last7days', '7days', '7d' => 'last_7_days',
            'last_30_days', 'last30days', '30days', '30d' => 'last_30_days',
            'custom' => 'custom',
            default => 'today',
        };
    }
}
