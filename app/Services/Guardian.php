<?php

namespace App\Services;

use Auth;
use Cache;
use Artisan;
use App\Models\User;
use App\Models\Right;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Guardian
{
    /**
     * @param User $user
     * @param string $action
     * @param string|Model $model
     * @param array|null $attributes
     * @return bool
     * @throws \Throwable
     */
    public function may(User $user, string $action, $model, ?array $attributes = null): bool
    {
        $info = $this->checkRights($user, $action, $model, $attributes);

        if (!$info->allowed || !($model instanceof Model)) {
            return $info->allowed;
        }

        $this->prepareDB();

        $model = clone $model;
        $model->setConnection('sqlite');
        $model->getConnection()->statement('PRAGMA foreign_keys = OFF');
        $model->getConnection()->beginTransaction();

            $model->saveQuietly();
            $id = $model->getKeyName();
            $query = $model->newQueryWithoutRelationships();
            $query->where($id, $model->$id);
            $query->where(fn (Builder $builder) =>
                $this->addConstraints($info->conditions, $query)
            );
            $count = $query->count();

        $model->getConnection()->rollBack();

        return $count === 1;
    }

    public function addConstraintsToQuery(
        Builder $builder, ?array $attributes = null,
        ?User $user = null, string $action = 'read'
    ): Builder {
        $user ??= Auth::user();
        $model = $builder->getModel();
        $info = $this->checkRights($user, $action, $model, $attributes);

        if (!$info->allowed) {
            $modelClass = class_basename($model);
            throw new AuthorizationException(
                "User is not allowed to $action this $modelClass."
            );
        }

        return $builder->where(fn (Builder $builder) =>
            $this->addConstraints($info->conditions, $builder)
        );
    }

    protected function checkRights(User $user, string $action, $model, ?array $attributes)
    {
        $modelClass = is_string($model) ? $model : get_class($model);
        $rights = $user->getAllRights()->where('model', $modelClass);
        $permittedAttributes = $rights->flatMap->$action->unique()->sort();

        switch ($action) {
            case 'read':
                $attributes ??= $permittedAttributes->all();
                break;
            case 'create':
            case 'update':
                if ($model instanceof Model)
                    $attributes ??= array_keys($model->getDirty());
                break;
            case 'delete':
                $rights = $rights->where('delete', true);
                $permittedAttributes = collect(['*']);
        }

        $allowed = $permittedAttributes->isNotEmpty() && (
            $permittedAttributes->contains('*') ||
            $permittedAttributes->intersect($attributes)->count() === count($attributes)
        );

        $conditions = $allowed ? $this->getConditions($action, $rights) : collect();
        if ($attributes) {
            $attributes[] = '*';
            $conditions = $conditions->only($attributes);
        }

        return new class ($allowed, $conditions) {
            public bool $allowed;
            public Collection $conditions;

            public function __construct(bool $allowed, Collection $conditions)
            {
                $this->allowed = $allowed;
                $this->conditions = $conditions;
            }
        };
    }

    protected function getConditions(string $action, EloquentCollection $rights): Collection
    {
        return Cache::tags(['rights', 'conditions'])->rememberForever(
            'rights:' . implode(',', $rights->modelKeys()),
            fn () => $rights
                ->loadMissing('conditions')
                ->sortBy(fn (Right $right) => $right->conditions->count())
                ->reduce(function(Collection $result, Right $right) use ($action) {
                    $fields = $action === 'delete'
                        ? collect(['*'])
                        : $right->$action;

                    if ($fields === null) return $result;

                    $groups = $fields->mapToGroups(
                        function(string $field) use ($right) {
                            return [$field => $right->conditions];
                        }
                    );

                    $result = $result->mergeRecursive($groups->map->all());

                    return $result;
                }, new Collection)
                ->mapInto(Collection::class)
            );
    }

    protected function prepareDB()
    {
        touch(database_path('database.sqlite'));
        Artisan::call('migrate --database=sqlite');
    }

    protected function addConstraints(Collection $allConditions, Builder $builder)
    {
        $wildcard = $allConditions->pull('*', collect());

        if ($wildcard->first->isEmpty()) return;

        foreach ($allConditions as $conditions) {
            if ($conditions->first->isEmpty()) continue;
            $this->addConstraint($builder, $conditions);
        }

        if ($wildcard->isNotEmpty()) {
            $builder->orWhere(fn(Builder $builder) =>
                $this->addConstraint($builder, $wildcard)
            );
        }
    }

    protected function addConstraint(Builder $builder, Collection $conditions)
    {
        $builder->where(fn (Builder $builder) =>
            $this->addConstraintPerField($builder, $conditions)
        );
    }

    protected function addConstraintPerField(Builder $builder, Collection $fieldConditions)
    {
        foreach ($fieldConditions as $conditions) {
            $builder->orWhere(fn (Builder $builder) =>
                $builder->where($conditions->toArray())
            );
        }
    }
}
