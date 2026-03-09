<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'personal_pv_required',
        'group_pv_required',
        'bonus_percent',
        'bonus_amount',
        'is_active',
    ];
}
