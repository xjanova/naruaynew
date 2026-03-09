<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoolBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percentage',
        'min_rank_id',
        'is_active',
    ];
}
