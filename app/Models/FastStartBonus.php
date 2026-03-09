<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FastStartBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_count',
        'days_count',
        'bonus_amount',
        'is_active',
    ];
}
