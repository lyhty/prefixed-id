<?php

namespace SirMathays\PrefixedId\Facades;

use Illuminate\Support\Facades\Facade;
use SirMathays\PrefixedId\PrefixedId as BasePrefixedId;

/**
 * @method static array modelClasses()
 * @method static array foreignKeys()
 * @method static string|null findPrefix($className)
 * @method static string|null getModelClass($prefixedId, string $wishedClass = null)
 * @method static \Illuminate\Database\Eloquent\Model|null findModel($prefixedId, string $wishedClass = null)
 * @method static \Illuminate\Database\Eloquent\Model findOrFailModel($prefixedId, string $wishedClass = null)
 * @method static string|null matchForeignKey($keyName)
 * @method static bool isPrefixedId($prefixedId)
 * @method static bool shouldAutoCast()
 * 
 * @see \SirMathays\PrefixedId\PrefixedId
 */
class PrefixedId extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BasePrefixedId::class;
    }
}
