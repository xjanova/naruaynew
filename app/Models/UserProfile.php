<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // Address fields
        'address', 'address2', 'city', 'state', 'country', 'postal_code',
        // Banking fields
        'bank_name', 'account_number', 'account_holder_name', 'ifsc_code', 'pan_number',
        // Payout preference
        'payout_type',
        // Social / additional fields
        'facebook', 'line_token', 'line_userid',
    ];

    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'pan_number' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
