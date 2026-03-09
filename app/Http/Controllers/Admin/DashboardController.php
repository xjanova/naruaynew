<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Commission;
use App\Models\WalletTransaction;
use App\Models\PayoutRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin.dashboard.stats', 300, function () {
            return [
                'total_members' => User::count(),
                'active_members' => User::where('active', true)->count(),
                'today_joins' => User::whereDate('created_at', today())->count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total'),
                'today_revenue' => Order::where('status', 'completed')->whereDate('created_at', today())->sum('total'),
                'pending_payouts' => PayoutRequest::where('status', 'pending')->sum('amount'),
                'total_commissions' => Commission::sum('amount'),
                'month_commissions' => Commission::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
            ];
        });

        $recentMembers = User::latest()->take(10)->get(['id', 'username', 'first_name', 'last_name', 'email', 'active', 'current_rank_id', 'created_at']);

        $joinChart = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueChart = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentMembers' => $recentMembers,
            'joinChart' => $joinChart,
            'revenueChart' => $revenueChart,
        ]);
    }
}
