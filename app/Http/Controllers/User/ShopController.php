<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\EWalletService;
use App\Events\OrderCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->when($request->category, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('User/Shop/Index', [
            'products' => $products,
            'categories' => ProductCategory::withCount('products')->get(),
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function cart(Request $request)
    {
        $cartItems = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        return Inertia::render('User/Shop/Cart', [
            'cartItems' => $cartItems,
            'balance' => $request->user()->balance?->balance ?? 0,
            'purchaseWallet' => $request->user()->balance?->purchase_wallet ?? 0,
        ]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['quantity' => DB::raw("quantity + {$request->quantity}")]
        );

        return back()->with('success', 'Added to cart.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:ewallet,purchase_wallet',
        ]);

        $user = $request->user();
        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Cart is empty.']);
        }

        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        $totalPV = $cartItems->sum(fn($item) => $item->product->pv * $item->quantity);

        return DB::transaction(function () use ($user, $cartItems, $total, $totalPV, $request) {
            $walletService = app(EWalletService::class);

            if ($request->payment_method === 'purchase_wallet') {
                $walletService->debit($user->id, $total, 'purchase', 'Product purchase');
            } else {
                $walletService->debit($user->id, $total, 'purchase', 'Product purchase');
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'total' => $total,
                'total_pv' => $totalPV,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'order_type' => 'repurchase',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'pv' => $item->product->pv,
                    'total' => $item->product->price * $item->quantity,
                    'total_pv' => $item->product->pv * $item->quantity,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            event(new OrderCompleted($order));

            return redirect()->route('user.shop.index')
                ->with('success', "Order {$order->order_number} placed successfully!");
        });
    }
}
