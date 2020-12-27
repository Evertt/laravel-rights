<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Right extends Model
{
    use HasFactory;

    protected $casts = [
        'create' => 'collection',
        'read'   => 'collection',
        'update' => 'collection',
        'delete' => 'boolean',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(RightCondition::class);
    }
}
