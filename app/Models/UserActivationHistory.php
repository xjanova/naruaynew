<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_status',
        'new_status',
        'reason',
        'changed_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
