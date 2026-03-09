<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankHistory extends Model
{
    use HasFactory;

    protected $table = 'rank_histories';

    protected $fillable = [
        'user_id',
        'old_rank_id',
        'new_rank_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldRank()
    {
        return $this->belongsTo(Rank::class, 'old_rank_id');
    }

    public function newRank()
    {
        return $this->belongsTo(Rank::class, 'new_rank_id');
    }
}
