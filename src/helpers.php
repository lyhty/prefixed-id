<?php

namespace SirMathays\PrefixedId;

use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;

/**
 * Resolve whether given value is a model class.
 *
 * @param string $model_name
 * Either the class name or the morph name 
 * (e.g. 'App\Models\User' or 'user').
 * @return string
 * @throws \LogicException
 */
function resolve_model_class(string $model_name): string
{
    return class_exists($model_name)
        ? $model_name
        : throw_unless(
            Relation::getMorphedModel($model_name),
            new LogicException("Invalid class name given")
        );
}