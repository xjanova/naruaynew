<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchingBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'percentage',
        'rank_id',
    ];
}
