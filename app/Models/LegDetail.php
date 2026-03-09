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
        'left_count', 'right_count',
        'left_active_count', 'right_active_count',
        'left_pv', 'right_pv',
        'left_carry', 'right_carry',
        'left_total_pv', 'right_total_pv',
        'total_left_count', 'total_right_count',
        'total_left_active_count', 'total_right_active_count',
    ];

    protected function casts(): array
    {
        return [
            'left_count' => 'integer',
            'right_count' => 'integer',
            'left_active_count' => 'integer',
            'right_active_count' => 'integer',
            'left_pv' => 'decimal:2',
            'right_pv' => 'decimal:2',
            'left_carry' => 'decimal:2',
            'right_carry' => 'decimal:2',
            'left_total_pv' => 'decimal:2',
            'right_total_pv' => 'decimal:2',
            'total_left_count' => 'integer',
            'total_right_count' => 'integer',
            'total_left_active_count' => 'integer',
            'total_right_active_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
