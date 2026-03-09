<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessVolume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'volume',
        'type',
        'period_date',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:2',
            'period_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
