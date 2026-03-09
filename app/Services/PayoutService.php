<?php

namespace App\Services;

use App\Models\PayoutRequest;
use App\Models\User;

class PayoutService
{
    public function __construct(
        private EWalletService $walletService,
    ) {}

    public function requestPayout(int $userId, float $amount, ?string $paymentMethod = null): PayoutRequest
    {
        $minPayout = SettingService::getFloat('min_payout', 100);
        $maxPayout = SettingService::getFloat('max_payout', 100000);

        if ($amount < $minPayout) {
            throw new \InvalidArgumentException("Minimum payout is {$minPayout}");
        }

        if ($amount > $maxPayout) {
            throw new \InvalidArgumentException("Maximum payout is {$maxPayout}");
        }

        // Check balance
        $balance = $this->walletService->getBalance($userId);
        if ($balance['balance'] < $amount) {
            throw new \App\Exceptions\InsufficientBalanceException('Insufficient balance for payout');
        }

        $transFee = SettingService::getFloat('trans_fee', 0);
        $fee = $amount * ($transFee / 100);
        $netAmount = $amount - $fee;

        return PayoutRequest::create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
        ]);
    }

    public function approvePayout(int $requestId, int $approvedBy): PayoutRequest
    {
        $request = PayoutRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            throw new \InvalidArgumentException('Payout request is not pending');
        }

        // Debit from wallet
        $this->walletService->debit($request->user_id, $request->amount, 'payout', [
            'note' => "Payout request #{$request->id}",
        ]);

        $request->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $request;
    }

    public function completePayout(int $requestId, ?string $reference = null): PayoutRequest
    {
        $request = PayoutRequest::findOrFail($requestId);

        $request->update([
            'status' => 'completed',
            'payment_reference' => $reference,
            'completed_at' => now(),
        ]);

        return $request;
    }

    public function rejectPayout(int $requestId, string $reason): PayoutRequest
    {
        $request = PayoutRequest::findOrFail($requestId);

        if ($request->status === 'approved') {
            // Refund if already deducted
            $this->walletService->credit($request->user_id, $request->amount, 'payout_refund', [
                'note' => "Refund for rejected payout #{$request->id}",
            ]);
        }

        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $request;
    }
}
