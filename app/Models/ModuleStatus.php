<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_name',
        'is_enabled',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
        ];
    }
}
