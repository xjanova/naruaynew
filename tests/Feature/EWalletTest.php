<?php

use App\Models\User;
use App\Models\UserBalance;
use App\Models\WalletTransaction;
use App\Models\FundTransfer;
use App\Models\Setting;
use App\Services\EWalletService;
use App\Exceptions\InsufficientBalanceException;

beforeEach(function () {
    $this->walletService = app(EWalletService::class);
    Setting::create(['key' => 'trans_fee', 'value' => '0', 'group' => 'fund_transfer']);
});

test('can credit wallet', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $this->walletService->credit($user->id, 5000, 'commission', [
        'note' => 'Test credit',
    ]);

    $balance = UserBalance::where('user_id', $user->id)->first();
    expect((float) $balance->balance_amount)->toBe(5000.0);

    $txn = WalletTransaction::where('user_id', $user->id)->first();
    expect($txn->type)->toBe('credit');
    expect((float) $txn->amount)->toBe(5000.0);
    expect($txn->amount_type)->toBe('commission');
});

test('can debit wallet', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 5000, 'purchase_wallet' => 0]);

    $this->walletService->debit($user->id, 2000, 'purchase', [
        'note' => 'Test debit',
    ]);

    $balance = UserBalance::where('user_id', $user->id)->first();
    expect((float) $balance->balance_amount)->toBe(3000.0);

    $txn = WalletTransaction::where('user_id', $user->id)->first();
    expect($txn->type)->toBe('debit');
    expect((float) $txn->amount)->toBe(2000.0);
});

test('cannot debit more than balance', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 1000, 'purchase_wallet' => 0]);

    expect(fn () => $this->walletService->debit($user->id, 2000, 'purchase'))
        ->toThrow(InsufficientBalanceException::class);
});

test('balance remains unchanged after failed debit', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 1000, 'purchase_wallet' => 0]);

    try {
        $this->walletService->debit($user->id, 2000, 'purchase');
    } catch (InsufficientBalanceException $e) {
        // expected
    }

    $balance = UserBalance::where('user_id', $user->id)->first();
    expect((float) $balance->balance_amount)->toBe(1000.0);
});

test('can transfer funds between users', function () {
    $sender = User::factory()->create();
    UserBalance::create(['user_id' => $sender->id, 'balance_amount' => 10000, 'purchase_wallet' => 0]);

    $receiver = User::factory()->create();
    UserBalance::create(['user_id' => $receiver->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $result = $this->walletService->transfer($sender->id, $receiver->id, 3000);

    expect((float) UserBalance::where('user_id', $sender->id)->first()->balance_amount)->toBe(7000.0);
    expect((float) UserBalance::where('user_id', $receiver->id)->first()->balance_amount)->toBe(3000.0);
});

test('fund transfer creates wallet transactions for both users', function () {
    $sender = User::factory()->create();
    UserBalance::create(['user_id' => $sender->id, 'balance_amount' => 10000, 'purchase_wallet' => 0]);

    $receiver = User::factory()->create();
    UserBalance::create(['user_id' => $receiver->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $this->walletService->transfer($sender->id, $receiver->id, 3000);

    $senderTxn = WalletTransaction::where('user_id', $sender->id)->first();
    expect($senderTxn->type)->toBe('debit');
    expect($senderTxn->amount_type)->toBe('fund_transfer');

    $receiverTxn = WalletTransaction::where('user_id', $receiver->id)->first();
    expect($receiverTxn->type)->toBe('credit');
    expect($receiverTxn->amount_type)->toBe('fund_transfer');
});

test('fund transfer creates fund transfer record', function () {
    $sender = User::factory()->create();
    UserBalance::create(['user_id' => $sender->id, 'balance_amount' => 10000, 'purchase_wallet' => 0]);

    $receiver = User::factory()->create();
    UserBalance::create(['user_id' => $receiver->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $this->walletService->transfer($sender->id, $receiver->id, 3000);

    $transfer = FundTransfer::where('from_user_id', $sender->id)->first();
    expect($transfer)->not->toBeNull();
    expect($transfer->to_user_id)->toBe($receiver->id);
    expect((float) $transfer->amount)->toBe(3000.0);
    expect($transfer->status)->toBe('completed');
});

test('fund transfer with fee deducts fee from receiver amount', function () {
    // Set 5% transfer fee
    Setting::where('key', 'trans_fee')->update(['value' => '5']);
    \Illuminate\Support\Facades\Cache::forget('settings');

    $sender = User::factory()->create();
    UserBalance::create(['user_id' => $sender->id, 'balance_amount' => 10000, 'purchase_wallet' => 0]);

    $receiver = User::factory()->create();
    UserBalance::create(['user_id' => $receiver->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $result = $this->walletService->transfer($sender->id, $receiver->id, 1000);

    // Sender should lose full 1000
    expect((float) UserBalance::where('user_id', $sender->id)->first()->balance_amount)->toBe(9000.0);
    // Receiver gets 950 (1000 - 5% fee)
    expect((float) UserBalance::where('user_id', $receiver->id)->first()->balance_amount)->toBe(950.0);
    // Fee recorded
    expect((float) $result['fee'])->toBe(50.0);
});

test('cannot transfer more than sender balance', function () {
    $sender = User::factory()->create();
    UserBalance::create(['user_id' => $sender->id, 'balance_amount' => 500, 'purchase_wallet' => 0]);

    $receiver = User::factory()->create();
    UserBalance::create(['user_id' => $receiver->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    expect(fn () => $this->walletService->transfer($sender->id, $receiver->id, 1000))
        ->toThrow(InsufficientBalanceException::class);
});

test('can credit purchase wallet', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $this->walletService->creditPurchaseWallet($user->id, 2000, 'admin_credit', [
        'note' => 'Test purchase wallet credit',
    ]);

    $balance = UserBalance::where('user_id', $user->id)->first();
    expect((float) $balance->purchase_wallet)->toBe(2000.0);
    expect((float) $balance->balance_amount)->toBe(0.0); // main balance unchanged
});

test('getBalance returns correct balances', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 5000, 'purchase_wallet' => 2000]);

    $balances = $this->walletService->getBalance($user->id);

    expect($balances['balance'])->toBe(5000.0);
    expect($balances['purchase_wallet'])->toBe(2000.0);
    expect($balances['total'])->toBe(7000.0);
});

test('credit creates transaction with unique transaction_id', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $txn1 = $this->walletService->credit($user->id, 100, 'commission');
    $txn2 = $this->walletService->credit($user->id, 200, 'commission');

    expect($txn1->transaction_id)->not->toBe($txn2->transaction_id);
});
