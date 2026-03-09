<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'total_left_count', 'total_right_count',
        'total_left_carry', 'total_right_carry',
        'total_active', 'total_inactive',
        'left_carry_forward', 'right_carry_forward',
    ];

    protected function casts(): array
    {
        return [
            'total_left_count' => 'integer',
            'total_right_count' => 'integer',
            'total_left_carry' => 'decimal:2',
            'total_right_carry' => 'decimal:2',
            'total_active' => 'integer',
            'total_inactive' => 'integer',
            'left_carry_forward' => 'decimal:2',
            'right_carry_forward' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
