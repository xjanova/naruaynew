<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelCommissionConfig extends Model
{
    use HasFactory;

    protected $table = 'level_commission_configs';

    protected $fillable = [
        'level',
        'percentage',
        'donation_1',
        'donation_2',
        'donation_3',
        'donation_4',
    ];
}
