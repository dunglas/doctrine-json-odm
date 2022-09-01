<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

/**
 * Allows using string constants in place of class names.
 */
class TypeMapper
{
    /**
     * @var array<class-string, string>
     */
    public static $map;

    /**
     * Falls back to class name itself.
     *
     * @param class-string $class
     */
    public static function getTypeByClass(string $class): string
    {
        $type = array_search($class, self::$map);

        return $type ?: $class;
    }

    /**
     * Falls back to type name itself – it might as well be a class.
     *
     * @return class-string
     */
    public static function getClassByType(string $type): string
    {
        return self::$map[$type] ?? $type;
    }
}
