<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCommissionConfig extends Model
{
    use HasFactory;

    protected $table = 'sales_commission_configs';

    protected $fillable = [
        'level',
        'product_id',
        'commission',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
