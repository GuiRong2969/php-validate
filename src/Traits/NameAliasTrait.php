<?php declare(strict_types=1);
/**
 * Created by VsCode.
 * User: Guirong
 * Date: 2023-04-03
 * Time: 09:30
 */

namespace Guirong\Validate\Traits;

/**
 * Trait NameAliasTrait
 *
 * @package Guirong\Validate\Traits
 */
trait NameAliasTrait
{
    /** @var array Name aliases map. please define on main-class */
    // protected static $aliases = [];

    /**
     * get real validator name by alias name
     *
     * @param string $name
     *
     * @return string
     */
    public static function realName(string $name): string
    {
        return static::$aliases[$name] ?? $name;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasAlias(string $name): bool
    {
        return isset(static::$aliases[$name]);
    }

    /**
     * @return array
     */
    public static function getAliases(): array
    {
        return static::$aliases;
    }

    /**
     * @param array $aliases
     */
    public static function setAliases(array $aliases): void
    {
        foreach ($aliases as $name => $alias) {
            static::$aliases[$name] = $alias;
        }
    }
}
