<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBalanceTransaction;
use App\Models\VendorOrder;
use App\Models\VendorWithdrawal;
use App\Services\VendorWithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(protected VendorWithdrawalService $withdrawalService) {}

    /**
     * Wallet page payload in one endpoint.
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

        $transactionsLimit = (int) $request->get('transactions_limit', 20);
        $transactionsLimit = max(1, min($transactionsLimit, 100));

        $withdrawalsLimit = (int) $request->get('withdrawals_limit', 20);
        $withdrawalsLimit = max(1, min($withdrawalsLimit, 100));

        $now = now();
        $startOfWeek = now()->startOfWeek();

        $pendingPayments = (float) VendorOrder::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid')
                    ->whereNull('vendor_balance_processed_at');
            })
            ->sum('total');

        $thisWeekEarnings = (float) VendorBalanceTransaction::query()
            ->where('vendor_id', $vendor->id)
            ->where('type', 'addition')
            ->whereBetween('created_at', [$startOfWeek, $now])
            ->sum('amount');

        $pendingWithdrawals = (float) VendorWithdrawal::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('amount');

        $approvedWithdrawals = (float) VendorWithdrawal::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->sum('amount');

        $transactions = VendorBalanceTransaction::query()
            ->with(['order', 'vendorOrder'])
            ->where('vendor_id', $vendor->id)
            ->latest('id')
            ->limit($transactionsLimit)
            ->get()
            ->map(function (VendorBalanceTransaction $tx) {
                return [
                    'id' => $tx->id,
                    'type' => $tx->type,
                    'direction' => $tx->type === 'addition' ? 'in' : 'out',
                    'amount' => (float) $tx->amount,
                    'balance_after' => (float) $tx->balance_after,
                    'notes' => $tx->notes,
                    'order_id' => $tx->order_id,
                    'vendor_order_id' => $tx->vendor_order_id,
                    'vendor_withdrawal_id' => $tx->vendor_withdrawal_id,
                    'created_at' => optional($tx->created_at)->toIso8601String(),
                ];
            });

        $withdrawals = VendorWithdrawal::query()
            ->where('vendor_id', $vendor->id)
            ->latest('id')
            ->limit($withdrawalsLimit)
            ->get()
            ->map(function (VendorWithdrawal $withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => (float) $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'method' => $withdrawal->method,
                    'notes' => $withdrawal->notes,
                    'balance_before' => $withdrawal->balance_before !== null ? (float) $withdrawal->balance_before : null,
                    'balance_after' => $withdrawal->balance_after !== null ? (float) $withdrawal->balance_after : null,
                    'processed_at' => optional($withdrawal->processed_at)->toIso8601String(),
                    'created_at' => optional($withdrawal->created_at)->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'balance' => round((float) $vendor->balance, 2),
                    'available_for_withdrawal' => round((float) $vendor->balance, 2),
                    'cash_on_hand' => round((float) $vendor->balance, 2),
                    'this_week_earnings' => round($thisWeekEarnings, 2),
                    'pending_payments' => round($pendingPayments, 2),
                    'pending_withdrawal' => round($pendingWithdrawals, 2),
                    'already_withdrawn' => round($approvedWithdrawals, 2),
                ],
                'transactions' => $transactions,
                'withdrawals' => $withdrawals,
            ],
        ]);
    }

    /**
     * Same flow as vendor dashboard withdrawal request.
     */
    public function requestWithdrawal(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor();

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => __('Vendor account not found.'),
            ], 404);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $withdrawal = $this->withdrawalService->createRequestForVendor(
                $vendor,
                (float) $validated['amount'],
                $validated['method'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation failed.'),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Withdrawal request submitted successfully.'),
            'data' => [
                'id' => $withdrawal->id,
                'amount' => (float) $withdrawal->amount,
                'status' => $withdrawal->status,
                'method' => $withdrawal->method,
                'notes' => $withdrawal->notes,
                'created_at' => optional($withdrawal->created_at)->toIso8601String(),
            ],
        ], 201);
    }
}

