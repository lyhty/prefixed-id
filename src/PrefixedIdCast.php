<?php

namespace SirMathays\PrefixedId;

use SirMathays\PrefixedId\Facades\PrefixedId;
use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;

class PrefixedIdCast implements CastsInboundAttributes, SerializesCastableAttributes
{
    protected ?string $class;

    /**
     * The PrefixedIdCast constructor.
     *
     * @param string|null $class
     */
    public function __construct(string $class = null)
    {
        // If class is set, check whether it is an actual 
        // class name, or a morph name. If neither is true, 
        // throw an exception.
        if (!is_null($class)) {
            $class = class_exists($class)
                ? $class
                : (Relation::getMorphedModel($class) ?? false);

            if ($class === false) {
                throw new LogicException("Invalid class name given");
            }
        }

        $this->class = $class;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return $value;
        }

        $class = $this->resolveModelClass($model, $key);

        if (!is_numeric($value)) {
            $value = $class::parsePrefixedId($value);

            if (is_null($value)) {
                throw new Exception("Invalid value given to '$key'");
            }
        }

        return (int) $value;
    }

    /**
     * {@inheritDoc}
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function serialize($model, string $key, $value, array $attributes)
    {
        $class = $this->resolveModelClass($model, $key);

        return $class::formatPrefixedId($value);
    }

    /**
     * Resolve model class.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @return string
     */
    protected function resolveModelClass($model, string $key)
    {
        // If class is given in cast setup.
        if ($this->class) {
            return $this->class;
        }

        // If key matches the model key, return the model class.
        if ($key == $model->getKeyName()) {
            return get_class($model);
        }

        // At this point we will assume that the key is a foreign 
        // key, and we will look for an equivalent class from the 
        // prefix config. If this fails, the resolving will fail, 
        // and an exception will be thrown.
        return throw_unless(
            PrefixedId::matchForeignKey($key),
            new LogicException("Model class could not be resolved for '$key'")
        );
    }
}