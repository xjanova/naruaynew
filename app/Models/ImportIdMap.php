<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportIdMap extends Model
{
    protected $table = 'import_id_maps';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'table_name',
        'legacy_id',
        'new_id',
    ];

    /**
     * Resolve a legacy ID to the new ID for a given table.
     */
    public static function resolve(string $table, int $legacyId): ?int
    {
        return static::where('table_name', $table)
            ->where('legacy_id', $legacyId)
            ->value('new_id');
    }
}
