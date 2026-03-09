<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'legacy_id',
        'user_id',
        'from_user_id',
        'amount_type',
        'amount',
        'tds',
        'service_charge',
        'amount_payable',
        'product_id',
        'level',
        'transaction_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'tds' => 'decimal:8',
            'service_charge' => 'decimal:8',
            'amount_payable' => 'decimal:8',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
