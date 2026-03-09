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
        'address', 'address2', 'city', 'state', 'zip', 'country',
        // Banking fields
        'bank_name', 'branch_name', 'account_holder', 'account_number', 'ifsc_code', 'pan_number',
        // Social / additional fields
        'facebook', 'twitter', 'instagram', 'linkedin', 'telegram', 'whatsapp',
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
