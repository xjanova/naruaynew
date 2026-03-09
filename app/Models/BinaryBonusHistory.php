<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinaryBonusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_leg',
        'left_leg',
        'right_leg',
        'calculation_type',
        'period_from',
        'period_to',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
