<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\WalletTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load(['balance', 'rank']);

        $stats = [
            'balance' => $user->balance?->balance ?? 0,
            'purchase_wallet' => $user->balance?->purchase_wallet ?? 0,
            'total_earned' => Commission::where('user_id', $user->id)->sum('amount'),
            'month_earned' => Commission::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'personal_pv' => $user->personal_pv,
            'group_pv' => $user->group_pv,
            'rank' => $user->rank?->name ?? 'Unranked',
            'direct_referrals' => $user->referrals()->count(),
            'total_downline' => $user->descendantPaths()->count(),
        ];

        $recentCommissions = Commission::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $recentTransactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $earningChart = Commission::where('user_id', $user->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('User/Dashboard', [
            'stats' => $stats,
            'recentCommissions' => $recentCommissions,
            'recentTransactions' => $recentTransactions,
            'earningChart' => $earningChart,
        ]);
    }
}
