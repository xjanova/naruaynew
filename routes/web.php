<?php

use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\TreeController as AdminTreeController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\EpinController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\RankController;

// User Controllers
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TreeController as UserTreeController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\ShopController;
use App\Http\Controllers\User\RegisterMemberController;

use App\Http\Controllers\SetupController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ActiveMemberMiddleware;
use App\Http\Middleware\SetupMiddleware;

// ─── Setup Wizard (first-run only) ──────────────────
Route::middleware(SetupMiddleware::class)->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return auth()->user()->user_level === 0
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }
        return inertia('Welcome');
    });

    Route::get('/setup', [SetupController::class, 'index'])->name('setup');
    Route::post('/setup/migrate', [SetupController::class, 'runMigrations'])->name('setup.migrate');
    Route::post('/setup/seed', [SetupController::class, 'runSeeders'])->name('setup.seed');
    Route::post('/setup/admin', [SetupController::class, 'createAdmin'])->name('setup.admin');
});

// ─── Admin Routes ─────────────────────────────────────
Route::middleware(['auth', 'verified', AdminMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Members
        Route::resource('members', MemberController::class)->only(['index', 'show', 'edit', 'update']);
        Route::post('members/{user}/toggle-block', [MemberController::class, 'toggleBlock'])->name('members.toggle-block');

        // Tree
        Route::get('/tree', [AdminTreeController::class, 'index'])->name('tree.index');
        Route::get('/tree/sponsor', [AdminTreeController::class, 'sponsorTree'])->name('tree.sponsor');
        Route::get('/tree/search', [AdminTreeController::class, 'search'])->name('tree.search');

        // Commissions
        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::get('/commissions/report', [CommissionController::class, 'report'])->name('commissions.report');

        // Payouts
        Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{payoutRequest}/approve', [PayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{payoutRequest}/reject', [PayoutController::class, 'reject'])->name('payouts.reject');
        Route::post('/payouts/{payoutRequest}/complete', [PayoutController::class, 'complete'])->name('payouts.complete');

        // Products
        Route::resource('products', ProductController::class)->except(['destroy', 'show']);
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // E-Pins
        Route::get('/epins', [EpinController::class, 'index'])->name('epins.index');
        Route::post('/epins/generate', [EpinController::class, 'generate'])->name('epins.generate');

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

        // Ranks
        Route::get('/ranks', [RankController::class, 'index'])->name('ranks.index');
        Route::get('/ranks/{rank}', [RankController::class, 'show'])->name('ranks.show');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/modules/{module}/toggle', [SettingController::class, 'toggleModule'])->name('settings.toggle-module');
    });

// ─── User Routes ──────────────────────────────────────
Route::middleware(['auth', 'verified', ActiveMemberMiddleware::class])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // Tree
        Route::get('/tree/binary', [UserTreeController::class, 'binary'])->name('tree.binary');
        Route::get('/tree/sponsor', [UserTreeController::class, 'sponsor'])->name('tree.sponsor');

        // Wallet & Commissions
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::get('/wallet/commissions', [WalletController::class, 'commissions'])->name('wallet.commissions');
        Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');
        Route::post('/wallet/payout', [WalletController::class, 'requestPayout'])->name('wallet.payout');
        Route::get('/wallet/payouts', [WalletController::class, 'payouts'])->name('wallet.payouts');

        // Profile
        Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/transaction-password', [UserProfileController::class, 'updateTransactionPassword'])->name('profile.transaction-password');

        // Shop
        Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/shop/cart', [ShopController::class, 'cart'])->name('shop.cart');
        Route::post('/shop/cart/add', [ShopController::class, 'addToCart'])->name('shop.cart.add');
        Route::post('/shop/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');

        // Register New Member
        Route::get('/register-member', [RegisterMemberController::class, 'create'])->name('register-member.create');
        Route::post('/register-member', [RegisterMemberController::class, 'store'])->name('register-member.store');
    });

require __DIR__.'/auth.php';
