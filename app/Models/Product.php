<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'legacy_id',
        'name',
        'sku',
        'type',
        'price',
        'pv_value',
        'bv_value',
        'referral_commission',
        'pair_price',
        'roi_percent',
        'roi_days',
        'subscription_period',
        'product_validity_days',
        'category_id',
        'description',
        'image',
        'tree_icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'pv_value' => 'decimal:2',
            'bv_value' => 'decimal:2',
            'referral_commission' => 'decimal:2',
            'pair_price' => 'decimal:2',
            'roi_percent' => 'decimal:2',
            'roi_days' => 'integer',
            'subscription_period' => 'integer',
            'product_validity_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
