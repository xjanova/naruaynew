<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelCommissionByRank extends Model
{
    use HasFactory;

    protected $table = 'level_commission_by_rank';

    protected $fillable = [
        'level',
        'rank_id',
        'commission',
    ];

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }
}
