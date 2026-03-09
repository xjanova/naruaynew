<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'balance_amount',
        'purchase_wallet',
    ];

    protected function casts(): array
    {
        return [
            'balance_amount' => 'decimal:8',
            'purchase_wallet' => 'decimal:8',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
