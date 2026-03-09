<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserBalance;
use App\Models\Rank;
use App\Models\Commission;
use App\Models\WalletTransaction;
use App\Services\BinaryTreeService;
use App\Services\RankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['rank', 'sponsor:id,username,first_name,last_name'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') $q->where('active', true);
                elseif ($status === 'inactive') $q->where('active', false);
                elseif ($status === 'blocked') $q->where('is_blocked', true);
            })
            ->when($request->rank, fn($q, $rank) => $q->where('current_rank_id', $rank))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Members/Index', [
            'members' => $query,
            'ranks' => Rank::orderBy('order')->get(['id', 'name']),
            'filters' => $request->only(['search', 'status', 'rank']),
        ]);
    }

    public function show(User $user)
    {
        $user->load(['rank', 'sponsor:id,username,first_name,last_name', 'profile', 'balance']);

        $commissions = Commission::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        $downlineCount = $user->descendantPaths()->count();

        return Inertia::render('Admin/Members/Show', [
            'member' => $user,
            'commissions' => $commissions,
            'transactions' => $transactions,
            'downlineCount' => $downlineCount,
        ]);
    }

    public function edit(User $user)
    {
        $user->load(['profile', 'balance', 'rank']);
        return Inertia::render('Admin/Members/Edit', [
            'member' => $user,
            'ranks' => Rank::orderBy('order')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'active' => 'boolean',
            'is_blocked' => 'boolean',
            'current_rank_id' => 'nullable|exists:ranks,id',
        ]);

        $user->update($validated);

        return redirect()->route('admin.members.show', $user)
            ->with('success', 'Member updated successfully.');
    }

    public function toggleBlock(User $user)
    {
        $user->update(['is_blocked' => !$user->is_blocked]);

        return back()->with('success', $user->is_blocked ? 'Member blocked.' : 'Member unblocked.');
    }
}
