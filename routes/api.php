<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderRefundController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\Vendor\BranchController as VendorBranchController;
use App\Http\Controllers\Api\Vendor\CategoryController as VendorCategoryController;
use App\Http\Controllers\Api\Vendor\HomeController as VendorHomeController;
use App\Http\Controllers\Api\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Api\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Api\Vendor\ReportController as ApiVendorReportController;
use App\Http\Controllers\Api\Vendor\VendorRatingController as ApiVendorRatingController;
use App\Http\Controllers\Api\Vendor\VendorTimeSlotController as ApiVendorTimeSlotController;
use App\Http\Controllers\Api\Vendor\VariantController as ApiVendorVariantController;
use App\Http\Controllers\Api\Vendor\WalletController as ApiVendorWalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes with rate limiting
// vendor register
Route::post('auth/vendor/register', [AuthController::class, 'vedorRegister'])->name('api.vendor.register');

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('api.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login'); // Rate limiting handled in LoginRequest
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail'])
    ->middleware('throttle:10,1')
    ->name('api.verify-email');
Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone'])
    ->middleware('throttle:10,1')
    ->name('api.verify-phone');
Route::post('/auth/resend-verification-code', [AuthController::class, 'resendVerificationCode'])
    ->middleware('throttle:3,1')
    ->name('api.resend-verification-code');

Route::post('/auth/reset-password/send-code', [ResetPasswordController::class, 'resetPasswordSendCode'])
    ->middleware('throttle:5,1')
    ->name('api.reset-password.send-code');
Route::post('/auth/reset-password/verify-code', [ResetPasswordController::class, 'resetPasswordVerifyCode'])
    ->middleware('throttle:10,1')
    ->name('api.reset-password.verify-code');
Route::post('/auth/reset-password/set-new-password', [ResetPasswordController::class, 'resetPasswordSetNewPassword'])
    ->middleware('throttle:5,1')
    ->name('api.reset-password.set-new-password');

// User
Route::group(['middleware' => 'locale'], function () {

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Authentication routes
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.user');

        // Profile routes with rate limiting
        Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('api.profile.update');

        // Password routes with rate limiting
        Route::put('/password', [PasswordController::class, 'update'])
            ->middleware('throttle:5,1')
            ->name('api.password.update');

        // addresses with rate limiting
        Route::get('addresses', [AddressController::class, 'index'])->name('api.addresses.index');
        Route::post('addresses', [AddressController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('api.addresses.store');
        Route::delete('addresses/{address}', [AddressController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('api.addresses.destroy');

        // favorite-list
        Route::get('favorite-list', [ProductController::class, 'favoriteList']);

        // toggle-favorite
        Route::post('products/{product}/toggle-favorite', [ProductController::class, 'toggleFavorite'])->name('api.products.toggle-favorite');

        // Tickets routes with rate limiting
        Route::get('tickets', [\App\Http\Controllers\Api\TicketController::class, 'index'])->name('api.tickets.index');
        Route::post('tickets', [\App\Http\Controllers\Api\TicketController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('api.tickets.store');
        Route::get('tickets/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'show'])->name('api.tickets.show');
        Route::put('tickets/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('api.tickets.update');
        Route::delete('tickets/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'destroy'])
            ->middleware('throttle:5,1')
            ->name('api.tickets.destroy');
        Route::post('tickets/{ticket}/add-message', [\App\Http\Controllers\Api\TicketController::class, 'addMessage'])
            ->middleware('throttle:20,1')
            ->name('api.tickets.add-message');
        Route::post('tickets/{ticket}/update-status', [\App\Http\Controllers\Api\TicketController::class, 'updateStatus'])
            ->middleware('throttle:10,1')
            ->name('api.tickets.update-status');

        // cart with rate limiting
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart/{product}', [CartController::class, 'add'])
            ->middleware('throttle:30,1');
        Route::put('cart/{product}', [CartController::class, 'updateQuantity'])
            ->middleware('throttle:30,1');
        Route::delete('cart/{product}', [CartController::class, 'remove'])
            ->middleware('throttle:30,1');
        Route::delete('cart', [CartController::class, 'clear'])
            ->middleware('throttle:10,1');
        Route::post('cart/apply-coupon', [CartController::class, 'applyCoupon'])
            ->middleware('throttle:10,1')
            ->name('api.cart.apply-coupon');

        // orders (user) with rate limiting
        Route::apiResource('orders', OrderController::class)->only(['index', 'show']);
        Route::post('orders', [OrderController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('api.orders.store');
        Route::post('orders/calculate-shipping', [OrderController::class, 'calculateShipping'])
            ->middleware('throttle:30,1')
            ->name('api.orders.calculate-shipping');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])
            ->middleware('throttle:5,1')
            ->name('api.orders.cancel');
        Route::post('orders/{order}/reorder', [OrderController::class, 'reorder'])
            ->middleware('throttle:10,1')
            ->name('api.orders.reorder');
        Route::post('orders/{order}/pay', [OrderController::class, 'pay'])
            ->middleware('throttle:10,1')
            ->name('api.orders.pay');
        Route::post('orders/{order}/refund-request', [OrderRefundController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('api.orders.refund-request');

        // transactions (user)
        Route::get('wallet/history', [\App\Http\Controllers\Api\TransactionController::class, 'walletHistory'])->name('api.wallet.history');
        Route::get('points/history', [\App\Http\Controllers\Api\TransactionController::class, 'pointHistory'])->name('api.points.history');

        // delivery (driver) – requires delivery.user middleware
        Route::prefix('delivery')->middleware('delivery.user')->group(function () {
            Route::get('wallet/history', [DeliveryController::class, 'walletHistory'])->name('api.delivery.wallet.history');
            Route::get('wallet/balance', [DeliveryController::class, 'walletBalance'])->name('api.delivery.wallet.balance');
            Route::get('stats', [DeliveryController::class, 'stats'])->name('api.delivery.stats');
            Route::get('orders-ready', [DeliveryController::class, 'ordersReady'])->name('api.delivery.orders-ready');
            Route::get('orders-ready/{order}', [DeliveryController::class, 'showOrderReady'])->name('api.delivery.orders-ready.show');
            Route::post('orders-ready/{order}/assign', [DeliveryController::class, 'assign'])->name('api.delivery.assign');
            Route::get('assignments', [DeliveryController::class, 'assignments'])->name('api.delivery.assignments');
            Route::get('assignments/{assignment}', [DeliveryController::class, 'showAssignment'])->name('api.delivery.assignments.show');
            Route::post('assignments/{assignment}/start-picking-up', [DeliveryController::class, 'startPickingUp'])->name('api.delivery.start-picking-up');
            Route::post('assignments/{assignment}/mark-pickup', [DeliveryController::class, 'markPickup'])->name('api.delivery.mark-pickup');
            Route::post('assignments/{assignment}/in-transit', [DeliveryController::class, 'inTransit'])->name('api.delivery.in-transit');
            Route::post('assignments/{assignment}/delivered', [DeliveryController::class, 'delivered'])->name('api.delivery.delivered');
            Route::put('assignments/{assignment}/distance', [DeliveryController::class, 'updateDistance'])->name('api.delivery.update-distance');
        });

        // Ratings with rate limiting
        Route::post('products/{product}/rate', [RatingController::class, 'rateProduct'])
            ->middleware('throttle:10,1')
            ->name('api.products.rate');
        Route::post('vendors/{vendor}/rate', [RatingController::class, 'rateVendor'])
            ->middleware('throttle:10,1')
            ->name('api.vendors.rate');

        // Reports with rate limiting
        Route::post('products/{product}/report', [ReportController::class, 'reportProduct'])
            ->middleware('throttle:5,1')
            ->name('api.products.report');
        Route::post('vendors/{vendor}/report', [ReportController::class, 'reportVendor'])
            ->middleware('throttle:5,1')
            ->name('api.vendors.report');

        // vendor (mobile dashboard) - isolated from user and delivery flows
        Route::prefix('vendor')->middleware('vendor.user')->group(function () {
            Route::get('home', [VendorHomeController::class, 'index'])->name('api.vendor.home.index');
            Route::get('reports/summary', [ApiVendorReportController::class, 'summary'])->name('api.vendor.reports.summary');
            Route::get('variants', [ApiVendorVariantController::class, 'index'])->name('api.vendor.variants.index');
            Route::get('categories', [VendorCategoryController::class, 'index'])->name('api.vendor.categories.index');
            Route::get('branches', [VendorBranchController::class, 'index'])->name('api.vendor.branches.index');
            Route::get('branches/{id}', [VendorBranchController::class, 'show'])->name('api.vendor.branches.show');

            Route::get('products', [VendorProductController::class, 'index'])->name('api.vendor.products.index');
            Route::post('products', [VendorProductController::class, 'store'])->name('api.vendor.products.store');
            Route::get('products/{id}', [VendorProductController::class, 'show'])->name('api.vendor.products.show');
            Route::put('products/{id}', [VendorProductController::class, 'update'])->name('api.vendor.products.update');
            Route::delete('products/{id}', [VendorProductController::class, 'destroy'])->name('api.vendor.products.destroy');
            Route::post('products/{id}/toggle-active', [VendorProductController::class, 'toggleActive'])->name('api.vendor.products.toggle-active');
            Route::get('ratings', [ApiVendorRatingController::class, 'index'])->name('api.vendor.ratings.index');
            Route::get('wallet', [ApiVendorWalletController::class, 'index'])->name('api.vendor.wallet.index');
            Route::post('wallet/withdrawals', [ApiVendorWalletController::class, 'requestWithdrawal'])->name('api.vendor.wallet.withdrawals.store');
            Route::get('time-slots', [ApiVendorTimeSlotController::class, 'index'])->name('api.vendor.time-slots.index');
            Route::post('time-slots', [ApiVendorTimeSlotController::class, 'store'])->name('api.vendor.time-slots.store');
            Route::put('time-slots/{timeSlot}', [ApiVendorTimeSlotController::class, 'update'])->name('api.vendor.time-slots.update');
            Route::delete('time-slots/{timeSlot}', [ApiVendorTimeSlotController::class, 'destroy'])->name('api.vendor.time-slots.destroy');

            Route::get('orders', [VendorOrderController::class, 'index'])->name('api.vendor.orders.index');
            Route::get('orders/{id}', [VendorOrderController::class, 'show'])->name('api.vendor.orders.show');
            Route::post('orders/{id}/update-status', [VendorOrderController::class, 'updateStatus'])->name('api.vendor.orders.update-status');
            Route::get('orders/{id}/invoice', [VendorOrderController::class, 'invoice'])->name('api.vendor.orders.invoice');
        });
    });

    // Category routes
    Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);

    // Vendor routes
    Route::apiResource('/vendors', VendorController::class)->only(['index', 'show']);

    // Product routes
    Route::apiResource('/products', ProductController::class)->only(['index', 'show']);

    // Slider routes
    Route::apiResource('/sliders', SliderController::class)->only(['index']);
});
