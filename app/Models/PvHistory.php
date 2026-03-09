<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PvHistory extends Model
{
    use HasFactory;

    protected $table = 'pv_histories';

    protected $fillable = [
        'user_id',
        'pv_amount',
        'type',
        'source',
        'from_user_id',
        'product_id',
    ];

    protected function casts(): array
    {
        return [
            'pv_amount' => 'decimal:2',
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
