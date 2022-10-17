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
interface TypeMapperInterface
{
    /**
     * Resolve type name from class
     *
     * @param class-string $class
     */
    public function getTypeByClass(string $class): string;

    /**
     * Resolve class from type name
     *
     * @return class-string
     */
    public function getClassByType(string $type): string;
}
