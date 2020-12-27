<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kalnoy\Nestedset\NodeTrait;

class Group extends Model
{
    use HasFactory, NodeTrait;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function rights(): BelongsToMany
    {
        return $this->belongsToMany(Right::class);
    }
}
