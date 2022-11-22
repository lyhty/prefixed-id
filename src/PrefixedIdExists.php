<?php

namespace Lyhty\PrefixedId;

use Illuminate\Contracts\Validation\Rule;
use Lyhty\PrefixedId\Facades\PrefixedId;

class PrefixedIdExists implements Rule
{
    protected ?string $modelClass;

    /**
     * The PidExists constructor.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass = null)
    {
        $this->modelClass = !is_null($modelClass)
            ? resolve_model_class($modelClass)
            : $modelClass;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $modelClass = $this->modelClass ?: PrefixedId::getModelClass($value);

        return !is_null($modelClass)
            && (new $modelClass)->newQuery()->wherePrefixedId($value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Given :attribute does not exist.';
    }
}
