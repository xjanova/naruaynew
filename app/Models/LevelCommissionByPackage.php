<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelCommissionByPackage extends Model
{
    use HasFactory;

    protected $table = 'level_commission_by_package';

    protected $fillable = [
        'level',
        'product_id',
        'commission_reg_pck',
        'commission_member_pck',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
