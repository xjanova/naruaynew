<?php

namespace App\Services;

use App\Models\UserBalance;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EWalletService
{
    public function credit(int $userId, float $amount, string $amountType, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $amountType, $meta) {
            $balance = UserBalance::lockForUpdate()->findOrFail($userId);
            $balance->increment('balance_amount', $amount);

            return WalletTransaction::create([
                'user_id' => $userId,
                'from_user_id' => $meta['from_user_id'] ?? null,
                'ewallet_type' => $meta['ewallet_type'] ?? null,
                'amount' => $amount,
                'purchase_wallet' => 0,
                'amount_type' => $amountType,
                'type' => 'credit',
                'transaction_fee' => $meta['transaction_fee'] ?? 0,
                'transaction_id' => $meta['transaction_id'] ?? Str::uuid()->toString(),
                'note' => $meta['note'] ?? null,
                'pending_id' => $meta['pending_id'] ?? null,
            ]);
        });
    }

    public function debit(int $userId, float $amount, string $amountType, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $amountType, $meta) {
            $balance = UserBalance::lockForUpdate()->findOrFail($userId);

            if ($balance->balance_amount < $amount) {
                throw new \App\Exceptions\InsufficientBalanceException(
                    "Insufficient balance. Available: {$balance->balance_amount}, Required: {$amount}"
                );
            }

            $balance->decrement('balance_amount', $amount);

            return WalletTransaction::create([
                'user_id' => $userId,
                'from_user_id' => $meta['from_user_id'] ?? null,
                'amount' => $amount,
                'amount_type' => $amountType,
                'type' => 'debit',
                'transaction_fee' => $meta['transaction_fee'] ?? 0,
                'transaction_id' => $meta['transaction_id'] ?? Str::uuid()->toString(),
                'note' => $meta['note'] ?? null,
            ]);
        });
    }

    public function creditPurchaseWallet(int $userId, float $amount, string $amountType, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $amountType, $meta) {
            $balance = UserBalance::lockForUpdate()->findOrFail($userId);
            $balance->increment('purchase_wallet', $amount);

            return WalletTransaction::create([
                'user_id' => $userId,
                'amount' => 0,
                'purchase_wallet' => $amount,
                'amount_type' => $amountType,
                'type' => 'credit',
                'transaction_id' => $meta['transaction_id'] ?? Str::uuid()->toString(),
                'note' => $meta['note'] ?? null,
            ]);
        });
    }

    public function transfer(int $fromUserId, int $toUserId, float $amount): array
    {
        $transFee = SettingService::getFloat('trans_fee', 0);
        $fee = $amount * ($transFee / 100);
        $netAmount = $amount - $fee;

        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $netAmount, $fee) {
            $txId = Str::uuid()->toString();

            $debitTx = $this->debit($fromUserId, $amount, 'fund_transfer', [
                'transaction_id' => $txId,
                'transaction_fee' => $fee,
                'note' => "Transfer to user #{$toUserId}",
            ]);

            $creditTx = $this->credit($toUserId, $netAmount, 'fund_transfer', [
                'from_user_id' => $fromUserId,
                'transaction_id' => $txId,
                'note' => "Transfer from user #{$fromUserId}",
            ]);

            \App\Models\FundTransfer::create([
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'fee' => $fee,
                'status' => 'completed',
            ]);

            return ['debit' => $debitTx, 'credit' => $creditTx, 'fee' => $fee];
        });
    }

    public function getBalance(int $userId): array
    {
        $balance = UserBalance::findOrFail($userId);
        return [
            'balance' => (float) $balance->balance_amount,
            'purchase_wallet' => (float) $balance->purchase_wallet,
            'total' => (float) $balance->balance_amount + (float) $balance->purchase_wallet,
        ];
    }
}
