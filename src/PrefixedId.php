<?php

namespace SirMathays\PrefixedId;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PrefixedId
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * The PrefixedId constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get model classes array that support prefixed ids.
     *
     * @return array
     */
    public function modelClasses(): array
    {
        return $this->config['models'] ?? [];
    }

    /**
     * Get foreign keys and classes connected to them
     *
     * @return array
     */
    public function foreignKeys(): array
    {
        return $this->config['foreign_keys'] ?? [];
    }

    /**
     * Find prefix for given class.
     *
     * @param mixed $className
     * @return void
     */
    public function findPrefix($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        $prefixMatch = Arr::where($this->modelClasses(), function ($class) use ($className) {
            return in_array($className, Arr::wrap($class));
        });

        return count($prefixMatch) 
            ? Str::finish(array_keys($prefixMatch)[0], '-')
            : null;
    }

    /**
     * Parse given prefixed id and get the model attached to it.
     *
     * @param string $prefixedId
     * @return string|null
     */
    public function getModelClass($prefixedId, string $wishedClass = null): ?string
    {
        $prefix = Str::before($prefixedId, '-');
        $model = Arr::get($this->modelClasses(), $prefix);

        if (!is_array($model)) return $model;

        if ($wishedClass) {
            $index = array_search($wishedClass, $model);

            return is_int($index) ? $model[$index] : null;
        }

        return $model[0];
    }

    /**
     * Parse given prefixed id and find model instance.
     *
     * @param string $prefixedId
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findModel($prefixedId, string $wishedClass = null)
    {
        $class = $this->getModelClass($prefixedId, $wishedClass);

        return !is_null($class) 
            ? $class::pidFind($prefixedId)
            : null;
    }

    /**
     * Parse given prefixed id and find or fail model instance.
     *
     * @param string $prefixedId
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFailModel($prefixedId, string $wishedClass = null)
    {
        $class = $this->getModelClass($prefixedId, $wishedClass);

        throw_if(is_null($class), new RecordsNotFoundException);

        return $class::pidFindOrFail($prefixedId);
    }

    /**
     * Return class for foreign key.
     *
     * @param string $prefixedId
     * @return string|null
     */
    public function matchForeignKey($keyName): ?string
    {
        return Arr::get($this->foreignKeys(), $keyName);
    }

    /**
     * Return boolean value whether given value is a prefixed id.
     *
     * @param string $prefixedId
     * @return bool
     */
    public function isPrefixedId($prefixedId): bool
    {
        return !is_null($this->getModelClass($prefixedId));
    }
}