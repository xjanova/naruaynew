<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function documents()
    {
        return $this->hasMany(KycDocument::class);
    }
}
