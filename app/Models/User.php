<?php

namespace App\Models;

use App\Services\Guardian;
use Cache;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property Collection|Group[] groups
 * @property Collection|Group[] rights
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function rights(): BelongsToMany
    {
        return $this->belongsToMany(Right::class);
    }

    public function getAllRights(): Collection
    {
        return Cache::tags(['users', 'rights'])
            ->rememberForever("user_{$this->id}_rights", function() {
                $groups = $this->groups->load(['rights', 'descendants.rights']);
                $allDescendants = $groups->map->descendants->flatten();
                $groups = $groups->merge($allDescendants);
                $groupRights = $groups->map->rights->flatten();

                return $this->rights->merge($groupRights);
            });
    }

    public function mayCreate($model, ?array $attributes = null): bool
    {
        return app(Guardian::class)->may(
            $this, 'create', $model, $attributes
        );
    }

    public function mayRead($model, ?array $attributes = null): bool
    {
        return app(Guardian::class)->may(
            $this, 'read', $model, $attributes
        );
    }

    public function mayUpdate($model, ?array $attributes = null): bool
    {
        return app(Guardian::class)->may(
            $this, 'update', $model, $attributes
        );
    }

    public function mayDelete($model): bool
    {
        return app(Guardian::class)->may(
            $this, 'delete', $model
        );
    }
}
