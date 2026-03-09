<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epin extends Model
{
    use HasFactory;

    protected $fillable = [
        'pin_number',
        'amount',
        'product_id',
        'generated_by',
        'owned_by',
        'used_by',
        'status',
        'used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owned_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
