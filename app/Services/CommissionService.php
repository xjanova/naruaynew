<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommissionService
{
    public function __construct(
        private EWalletService $walletService,
        private SettingService $settingService,
    ) {}

    /**
     * Credit commission to user with TDS and service charge deduction
     */
    public function creditCommission(
        User $user,
        float $amount,
        string $amountType,
        ?User $fromUser = null,
        ?Product $product = null,
        ?int $level = null,
        ?string $note = null,
    ): ?Commission {
        if ($amount <= 0) return null;

        $tdsPercent = SettingService::getFloat('tds', 0);
        $serviceChargePercent = SettingService::getFloat('service_charge', 0);

        $tds = $amount * ($tdsPercent / 100);
        $serviceCharge = $amount * ($serviceChargePercent / 100);
        $amountPayable = $amount - $tds - $serviceCharge;

        if ($amountPayable <= 0) return null;

        return DB::transaction(function () use ($user, $amount, $tds, $serviceCharge, $amountPayable, $amountType, $fromUser, $product, $level, $note) {
            $txId = Str::uuid()->toString();

            // Record in commission ledger
            $commission = Commission::create([
                'user_id' => $user->id,
                'from_user_id' => $fromUser?->id,
                'amount_type' => $amountType,
                'amount' => $amount,
                'tds' => $tds,
                'service_charge' => $serviceCharge,
                'amount_payable' => $amountPayable,
                'product_id' => $product?->id,
                'level' => $level,
                'transaction_id' => $txId,
                'note' => $note,
            ]);

            // Credit to e-wallet
            $this->walletService->credit($user->id, $amountPayable, $amountType, [
                'from_user_id' => $fromUser?->id,
                'transaction_id' => $txId,
                'note' => $note ?? "Commission: {$amountType}",
            ]);

            return $commission;
        });
    }

    /**
     * Get sponsor upline chain up to N levels
     */
    public function getSponsorUplines(User $user, int $maxLevels): array
    {
        $uplines = [];
        $current = $user->sponsor;
        $level = 1;

        while ($current && $level <= $maxLevels) {
            $uplines[$level] = $current;
            $current = $current->sponsor;
            $level++;
        }

        return $uplines;
    }

    /**
     * Check if user should be skipped for commission
     */
    public function shouldSkipUser(User $user): bool
    {
        if (!$user->isActive() && SettingService::getBool('skip_blocked_users_commission')) {
            return true;
        }
        return false;
    }
}
