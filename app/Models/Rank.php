<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'referral_count',
        'personal_pv',
        'group_pv',
        'downline_count',
        'team_member_count',
        'rank_bonus',
        'party_commission',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'referral_count' => 'integer',
            'personal_pv' => 'decimal:2',
            'group_pv' => 'decimal:2',
            'downline_count' => 'integer',
            'team_member_count' => 'integer',
            'rank_bonus' => 'decimal:2',
            'party_commission' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function downlineRankConfigs()
    {
        return $this->hasMany(DownlineRankConfig::class);
    }

    public function purchaseRankConfigs()
    {
        return $this->hasMany(PurchaseRankConfig::class);
    }
}
