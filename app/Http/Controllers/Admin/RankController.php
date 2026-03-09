<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RankController extends Controller
{
    public function index()
    {
        $ranks = Rank::withCount('users')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Ranks/Index', [
            'ranks' => $ranks,
        ]);
    }

    public function show(Rank $rank)
    {
        $rank->load('configs');

        $members = User::where('current_rank_id', $rank->id)
            ->with('sponsor:id,username')
            ->paginate(25);

        $recentPromotions = RankHistory::where('new_rank_id', $rank->id)
            ->with('user:id,username,first_name,last_name')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Admin/Ranks/Show', [
            'rank' => $rank,
            'members' => $members,
            'recentPromotions' => $recentPromotions,
        ]);
    }
}
