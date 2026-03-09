<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinaryBonusConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculation_criteria',
        'calculation_period',
        'commission_type',
        'pair_commission',
        'pair_type',
        'pair_value',
        'point_value',
        'carry_forward',
        'flush_out',
        'flush_out_limit',
        'flush_out_period',
        'locking_period',
        'block_binary_pv',
    ];

    protected function casts(): array
    {
        return [
            'pair_commission' => 'decimal:2',
            'pair_value' => 'decimal:2',
            'point_value' => 'decimal:2',
            'block_binary_pv' => 'decimal:2',
        ];
    }
}
