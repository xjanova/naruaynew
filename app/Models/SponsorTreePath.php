<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorTreePath extends Model
{
    protected $table = 'sponsor_tree_paths';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = [
        'ancestor',
        'descendant',
        'depth',
    ];

    protected function casts(): array
    {
        return [
            'ancestor' => 'integer',
            'descendant' => 'integer',
            'depth' => 'integer',
        ];
    }

    public function ancestorUser()
    {
        return $this->belongsTo(User::class, 'ancestor');
    }

    public function descendantUser()
    {
        return $this->belongsTo(User::class, 'descendant');
    }

    /**
     * Override getKeyName to avoid issues with null primary key.
     */
    public function getKeyName()
    {
        return null;
    }

    /**
     * Set the keys for a save/update query (composite key handling).
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('ancestor', '=', $this->getAttribute('ancestor'))
              ->where('descendant', '=', $this->getAttribute('descendant'));

        return $query;
    }
}
