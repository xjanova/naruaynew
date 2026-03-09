<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Epin;
use App\Models\EpinConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EpinController extends Controller
{
    public function index(Request $request)
    {
        $epins = Epin::with(['allocatedTo:id,username', 'usedBy:id,username'])
            ->when($request->status, function ($q, $s) {
                if ($s === 'available') $q->where('status', 'active')->whereNull('allocated_to');
                elseif ($s === 'allocated') $q->whereNotNull('allocated_to')->where('status', 'active');
                elseif ($s === 'used') $q->where('status', 'used');
                elseif ($s === 'expired') $q->where('status', 'expired');
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/Epins/Index', [
            'epins' => $epins,
            'filters' => $request->only(['status']),
            'configs' => EpinConfig::where('is_active', true)->get(),
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'config_id' => 'required|exists:epin_configs,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        $config = EpinConfig::findOrFail($request->config_id);
        $epins = [];

        for ($i = 0; $i < $request->quantity; $i++) {
            $epins[] = [
                'code' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                'amount' => $config->amount,
                'pv' => $config->pv,
                'status' => 'active',
                'expires_at' => $config->validity_days ? now()->addDays($config->validity_days) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Epin::insert($epins);

        return back()->with('success', "{$request->quantity} E-PINs generated.");
    }
}
