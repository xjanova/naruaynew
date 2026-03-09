<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRankConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'rank_id',
        'product_id',
        'required_count',
    ];

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
