<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = Commission::with(['user:id,username,first_name,last_name', 'fromUser:id,username,first_name,last_name'])
            ->when($request->type, fn($q, $type) => $q->where('commission_type', $type))
            ->when($request->user_id, fn($q, $uid) => $q->where('user_id', $uid))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $summary = Commission::select('commission_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('commission_type')
            ->get();

        return Inertia::render('Admin/Commissions/Index', [
            'commissions' => $commissions,
            'summary' => $summary,
            'filters' => $request->only(['type', 'user_id', 'date_from', 'date_to']),
            'commissionTypes' => Commission::distinct('commission_type')->pluck('commission_type'),
        ]);
    }

    public function report(Request $request)
    {
        $period = $request->get('period', 'monthly');

        $format = match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m',
        };

        $data = Commission::select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                'commission_type',
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(tds_amount) as total_tds'),
                DB::raw('SUM(service_charge) as total_sc'),
                DB::raw('COUNT(*) as count')
            )
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->groupBy('period', 'commission_type')
            ->orderBy('period', 'desc')
            ->get();

        return Inertia::render('Admin/Commissions/Report', [
            'data' => $data,
            'period' => $period,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }
}
