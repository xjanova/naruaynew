<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Commission;
use App\Models\PayoutRequest;
use App\Models\FundTransfer;
use App\Models\User;
use App\Services\EWalletService;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function __construct(
        protected EWalletService $walletService,
        protected PayoutService $payoutService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('balance');

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('User/Wallet/Index', [
            'balance' => $user->balance,
            'transactions' => $transactions,
            'filters' => $request->only(['type']),
        ]);
    }

    public function commissions(Request $request)
    {
        $commissions = Commission::where('user_id', $request->user()->id)
            ->with('fromUser:id,username,first_name,last_name')
            ->when($request->type, fn($q, $t) => $q->where('commission_type', $t))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('User/Wallet/Commissions', [
            'commissions' => $commissions,
            'filters' => $request->only(['type']),
        ]);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'to_username' => 'required|exists:users,username',
            'amount' => 'required|numeric|min:1',
            'transaction_password' => 'required|string',
        ]);

        $toUser = User::where('username', $request->to_username)->firstOrFail();

        if ($toUser->id === $request->user()->id) {
            return back()->withErrors(['to_username' => 'Cannot transfer to yourself.']);
        }

        // Verify transaction password
        if (!Hash::check($request->transaction_password, $request->user()->transaction_password)) {
            return back()->withErrors(['transaction_password' => 'Invalid transaction password.']);
        }

        $this->walletService->transfer($request->user()->id, $toUser->id, $request->amount, 'Fund transfer');

        return back()->with('success', "Transferred \u20B9{$request->amount} to {$toUser->username}.");
    }

    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'method' => 'required|in:bank_transfer,upi',
        ]);

        $this->payoutService->requestPayout($request->user(), $request->amount, $request->method);

        return back()->with('success', 'Payout request submitted.');
    }

    public function payouts(Request $request)
    {
        $payouts = PayoutRequest::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(25);

        return Inertia::render('User/Wallet/Payouts', [
            'payouts' => $payouts,
        ]);
    }
}
