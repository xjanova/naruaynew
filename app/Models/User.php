<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'legacy_id', 'username', 'email', 'password', 'transaction_password',
        'first_name', 'last_name', 'phone', 'id_card', 'date_of_birth', 'gender', 'photo',
        'sponsor_id', 'placement_id', 'position', 'leg_position',
        'product_id', 'product_validity', 'personal_pv', 'group_pv',
        'rank_id', 'user_level', 'sponsor_level', 'binary_leg',
        'active_status', 'kyc_status', 'google_auth_enabled', 'google_auth_secret',
        'join_date', 'register_by_using',
    ];

    protected $hidden = ['password', 'transaction_password', 'remember_token', 'google_auth_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'transaction_password' => 'hashed',
            'date_of_birth' => 'date',
            'product_validity' => 'date',
            'personal_pv' => 'decimal:2',
            'group_pv' => 'decimal:2',
            'join_date' => 'datetime',
            'google_auth_enabled' => 'boolean',
        ];
    }

    // MLM Relationships
    public function sponsor() { return $this->belongsTo(User::class, 'sponsor_id'); }
    public function placement() { return $this->belongsTo(User::class, 'placement_id'); }
    public function directReferrals() { return $this->hasMany(User::class, 'sponsor_id'); }
    public function placementChildren() { return $this->hasMany(User::class, 'placement_id'); }
    public function leftChild() { return $this->hasOne(User::class, 'placement_id')->where('position', 'L'); }
    public function rightChild() { return $this->hasOne(User::class, 'placement_id')->where('position', 'R'); }

    // Profile & Financial
    public function profile() { return $this->hasOne(UserProfile::class); }
    public function balance() { return $this->hasOne(UserBalance::class); }
    public function rank() { return $this->belongsTo(Rank::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function legDetail() { return $this->hasOne(LegDetail::class); }

    // Tree paths
    public function ancestorPaths() { return $this->hasMany(TreePath::class, 'descendant'); }
    public function descendantPaths() { return $this->hasMany(TreePath::class, 'ancestor'); }
    public function sponsorAncestorPaths() { return $this->hasMany(SponsorTreePath::class, 'descendant'); }
    public function sponsorDescendantPaths() { return $this->hasMany(SponsorTreePath::class, 'ancestor'); }

    // Transactions
    public function commissions() { return $this->hasMany(Commission::class); }
    public function walletTransactions() { return $this->hasMany(WalletTransaction::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function payoutRequests() { return $this->hasMany(PayoutRequest::class); }
    public function rankHistories() { return $this->hasMany(RankHistory::class); }
    public function kycDocuments() { return $this->hasMany(KycDocument::class); }

    // Helpers
    public function getFullNameAttribute(): string { return trim("{$this->first_name} {$this->last_name}"); }
    public function isActive(): bool { return $this->active_status === 'active'; }
    public function isSubscriptionValid(): bool { return $this->product_validity && $this->product_validity->isFuture(); }
}
