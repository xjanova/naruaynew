<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService,
    ) {}

    public function index(Request $request)
    {
        $payouts = PayoutRequest::with(['user:id,username,first_name,last_name,email'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'filters' => $request->only(['status']),
        ]);
    }

    public function approve(PayoutRequest $payoutRequest)
    {
        $this->payoutService->approvePayout($payoutRequest, auth()->id());
        return back()->with('success', 'Payout approved.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->payoutService->rejectPayout($payoutRequest, $request->reason);
        return back()->with('success', 'Payout rejected.');
    }

    public function complete(PayoutRequest $payoutRequest)
    {
        $this->payoutService->completePayout($payoutRequest, 'manual', 'MANUAL-' . now()->timestamp);
        return back()->with('success', 'Payout marked complete.');
    }
}
