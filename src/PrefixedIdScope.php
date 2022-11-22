<?php

namespace Lyhty\PrefixedId;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Lyhty\PrefixedId\Facades\PrefixedId;

class PrefixedIdScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = [
        'WherePrefixedId', 'OrWherePrefixedId',
        'WherePrefixedIdIn', 'OrWherePrefixedIdIn',
        'FindWithPrefixedId', 'FindManyWithPrefixedId',
        'FindOrFailWithPrefixedId',
    ];

    /**
     * {@inheritDoc}
     */
    public function apply(Builder $builder, Model $model)
    {
        // 
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the where-prefixed-id extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWherePrefixedId(Builder $builder)
    {
        $builder->macro(
            'wherePrefixedId',
            $closure = function (Builder $builder, $prefixedId, string $attribute = null, bool $or = false) {
                $model = $builder->getModel();

                if (is_null($attribute) || $attribute == $model->getKeyName()) {
                    $attribute = $model->getKeyName();
                    $value = $model->parsePrefixedId($prefixedId);
                } else {
                    $modelClass = PrefixedId::matchForeignKey($attribute);
                    $value = (new $modelClass)->parsePrefixedId($prefixedId);
                }

                return $builder->{!$or ? 'where' : 'orWhere'}($attribute, $value);
            }
        );

        $builder->macro('wherePid', $closure);
    }

    /**
     * Add the or-where-prefixed-id extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOrWherePrefixedId(Builder $builder)
    {
        $builder->macro('orWherePrefixedId', $closure = function (Builder $builder, $prefixedId, string $attribute = null) {
            return $builder->wherePrefixedId($prefixedId, $attribute, true);
        });

        $builder->macro('orWherePid', $closure);
    }

    /**
     * Add the where-prefixed-id-in extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWherePrefixedIdIn(Builder $builder)
    {
        $builder->macro(
            'wherePrefixedIdIn',
            $closure = function (Builder $builder, $prefixedIds, string $attribute = null, bool $or = false) {
                $model = $builder->getModel();

                $prefixedIds = collect($prefixedIds);

                if (is_null($attribute) || $attribute == $model->getKeyName()) {
                    $attribute = $model->getKeyName();
                    $value = $prefixedIds->map(fn ($id) => $model->parsePrefixedId($id));
                } else {
                    $modelClass = PrefixedId::matchForeignKey($attribute);
                    $value = $prefixedIds->map(fn ($id) => (new $modelClass)->parsePrefixedId($id));
                }

                return $builder->{!$or ? 'whereIn' : 'orWhereIn'}($attribute, $value);
            }
        );

        $builder->macro('wherePidIn', $closure);
    }

    /**
     * Add the or-where-prefixed-id-in extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOrWherePrefixedIdIn(Builder $builder)
    {
        $builder->macro('orWherePrefixedIdIn', $closure = function (Builder $builder, $prefixedIds, string $attribute = null) {
            return $builder->wherePrefixedIdIn($prefixedIds, $attribute);
        });

        $builder->macro('orWherePidIn', $closure);
    }

    /**
     * Add the find-with-prefixed-id extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFindWithPrefixedId(Builder $builder)
    {
        $builder->macro('findWithPrefixedId', $closure = function (Builder $builder, $pid, $columns = ['*']) {
            if (is_array($pid) || $pid instanceof Arrayable) {
                return $builder->findManyWithPrefixedId($pid, $columns);
            }

            return $builder->find($this->parsePrefixedId($builder, $pid), $columns);
        });

        $builder->macro('findWithPid', $closure);
        $builder->macro('pidFind', $closure);
    }

    /**
     * Add the find-many-with-prefixed-id extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFindManyWithPrefixedId(Builder $builder)
    {
        $builder->macro('findManyWithPrefixedId', $closure = function (Builder $builder, $pids, $columns = ['*']) {
            $ids = collect($pids)->map(fn ($pid) => $this->parsePrefixedId($builder, $pid));

            return $builder->findMany($ids, $columns);
        });

        $builder->macro('findManyWithPid', $closure);
        $builder->macro('pidFindMany', $closure);
    }

    /**
     * Add the find-or-fail-with-prefixed-id extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFindOrFailWithPrefixedId(Builder $builder)
    {
        $builder->macro('findOrFailWithPrefixedId', $closure = function (Builder $builder, $pid, $columns = ['*']) {
            $id = ($ids = collect($pid)->map(fn ($pid) => $this->parsePrefixedId($builder, $pid)))
                ->when($ids->count() <= 1, fn ($id) => $id->first());

            return $builder->findOrFail($id, $columns);
        });

        $builder->macro('findOrFailWithPid', $closure);
        $builder->macro('pidFindOrFail', $closure);
    }

    /**
     * Helper to access models parse prefixed id function.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param mixed $pid
     * @return void
     */
    public function parsePrefixedId($builder, $pid)
    {
        return $builder->getModel()->parsePrefixedId($pid);
    }
}