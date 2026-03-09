<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownlineRankConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'rank_id',
        'required_rank_id',
        'required_count',
    ];

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function requiredRank()
    {
        return $this->belongsTo(Rank::class, 'required_rank_id');
    }
}
