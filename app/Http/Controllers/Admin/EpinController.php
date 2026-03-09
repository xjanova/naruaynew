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
        $epins = Epin::with(['owner:id,username', 'usedBy:id,username', 'product:id,name'])
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/Epins/Index', [
            'epins' => $epins,
            'filters' => $request->only(['status']),
            'configs' => EpinConfig::with('product:id,name')->where('is_active', true)->get(),
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
        $adminId = auth()->id();

        for ($i = 0; $i < $request->quantity; $i++) {
            $epins[] = [
                'pin_number' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                'amount' => $config->amount,
                'product_id' => $config->product_id,
                'generated_by' => $adminId,
                'owned_by' => $adminId,
                'status' => 'available',
                'expires_at' => now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Epin::insert($epins);

        return back()->with('success', "{$request->quantity} E-PINs generated.");
    }
}
