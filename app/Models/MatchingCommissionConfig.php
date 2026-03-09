<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchingCommissionConfig extends Model
{
    use HasFactory;

    protected $table = 'matching_commission_configs';

    protected $fillable = [
        'level',
        'product_id',
        'commission_member_pck',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
