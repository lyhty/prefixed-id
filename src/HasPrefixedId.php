<?php

namespace SirMathays\PrefixedId;

use Illuminate\Support\Str;
use LogicException;
use SirMathays\PrefixedId\Facades\PrefixedId;

/**
 * @method static static findWithPrefixedId($pid, $columns = ['*'])
 * @method static static findWithPid($pid, $columns = ['*'])
 * @method static static pidFind($pid, $columns = ['*'])
 * @method static static findManyWithPrefixedId($pids, $columns = ['*'])
 * @method static static findManyWithPid($pids, $columns = ['*'])
 * @method static static pidFindMany($pids, $columns = ['*'])
 * @method static static findOrFailWithPrefixedId($pid, $columns = ['*'])
 * @method static static findOrFailWithPid($pid, $columns = ['*'])
 * @method static static pidFindOrFail($pid, $columns = ['*'])
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder wherePid($prefixedId, string $attribute = null, bool $or = false)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWherePid($prefixedId, string $attribute = null)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder wherePidIn($prefixedIds, string $attribute = null, bool $or = false)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWherePidIn($prefixedIds, string $attribute = null)
 * @property-read string $prefixedId
 * Id prefixed with class prefix variable.
 * @property-read string $pid
 * Id prefixed with class prefix variable.
 */
trait HasPrefixedId
{
    protected string $idPrefix;

    /**
     * Boot the prefixed id trait for a model.
     *
     * @return void
     */
    public static function bootHasPrefixedId(): void
    {
        static::addGlobalScope(new PrefixedIdScope);
    }

    /**
     * Initialize the prefixed id trait for an instance.
     *
     * @return void
     */
    public function initializeHasPrefixedId(): void
    {
        $this->idPrefix = static::getIdPrefix();

        if (
            PrefixedId::shouldAutoCast() && 
            !isset($this->casts[$key = $this->getKeyName()])
        ) {
            $this->casts[$key] = PrefixedIdCast::class;
        }
    }

    /**
     * Get id with prefix.
     *
     * @return string
     */
    protected function getPrefixedIdAttribute(): ?string
    {
        return ($id = $this->getKey())
            ? $this->idPrefix . $id
            : null;
    }

    /**
     * Shorthand for getting id with prefix.
     *
     * @return string
     */
    protected function getPidAttribute(): ?string
    {
        return $this->getPrefixedIdAttribute();
    }

    /**
     * Parse prefix id.
     *
     * @param string $prefixedId
     * @return mixed
     */
    public static function parsePrefixedId(?string $prefixedId)
    {
        if (is_null($prefixedId)) {
            return null;
        }

        if (!Str::startsWith($prefixedId, static::getIdPrefix())) {
            return null;
        }

        $val = Str::after($prefixedId, static::getIdPrefix());
        settype($val, (new static)->getKeyType());

        return $val;
    }

    /**
     * Get id prefix for class from config.
     *
     * @return string
     * @throws \LogicException
     */
    public static function getIdPrefix(): string
    {
        throw_if(
            is_null($prefix = PrefixedId::findPrefix(static::class)),
            new LogicException(sprintf("%s::class doesn't have prefix value set up in config.", static::class))
        );

        return $prefix;
    }

    /**
     * Format prefixed id for given id.
     *
     * @param mixed $id
     * @return string|null
     */
    public static function formatPrefixedId($id): ?string
    {
        return !is_null($id)
            ? static::getIdPrefix() . $id
            : null;
    }
}
