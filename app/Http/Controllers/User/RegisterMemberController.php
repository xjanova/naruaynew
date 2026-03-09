<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Epin;
use App\Events\UserRegistered;
use App\Services\EWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisterMemberController extends Controller
{
    public function create(Request $request)
    {
        $packages = Product::where('is_active', true)
            ->where('is_registration_package', true)
            ->get();

        return Inertia::render('User/Register/Create', [
            'packages' => $packages,
            'sponsor' => $request->user()->only('id', 'username', 'first_name', 'last_name'),
            'positions' => ['left', 'right'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|confirmed|min:8',
            'product_id' => 'required|exists:products,id',
            'position' => 'required|in:left,right',
            'payment_method' => 'required|in:ewallet,epin',
            'epin_code' => 'required_if:payment_method,epin',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        return DB::transaction(function () use ($request, $validated, $product) {
            // Handle payment
            if ($validated['payment_method'] === 'ewallet') {
                app(EWalletService::class)->debit(
                    $request->user()->id, $product->price, 'registration', 'New member registration'
                );
            } elseif ($validated['payment_method'] === 'epin') {
                $epin = Epin::where('code', $validated['epin_code'])
                    ->where('status', 'active')
                    ->where('amount', '>=', $product->price)
                    ->firstOrFail();
                $epin->update(['status' => 'used', 'used_by' => null, 'used_at' => now()]);
            }

            // Create user
            $newUser = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'sponsor_id' => $request->user()->id,
                'position' => $validated['position'],
                'product_id' => $product->id,
                'personal_pv' => $product->pv,
                'active' => true,
                'subscription_status' => 'active',
                'subscription_expires_at' => now()->addYear(),
            ]);

            // Create order
            $order = Order::create([
                'user_id' => $newUser->id,
                'order_number' => 'REG-' . strtoupper(Str::random(8)),
                'total' => $product->price,
                'total_pv' => $product->pv,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'order_type' => 'registration',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'pv' => $product->pv,
                'total' => $product->price,
                'total_pv' => $product->pv,
            ]);

            event(new UserRegistered($newUser, $order));

            return redirect()->route('user.dashboard')
                ->with('success', "Member {$newUser->username} registered successfully!");
        });
    }
}
