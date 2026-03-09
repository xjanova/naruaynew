<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'ewallet_type',
        'amount',
        'purchase_wallet',
        'amount_type',
        'type',
        'transaction_fee',
        'transaction_id',
        'note',
        'pending_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'purchase_wallet' => 'decimal:8',
            'transaction_fee' => 'decimal:8',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
